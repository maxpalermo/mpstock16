<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    Massimiliano Palermo <maxx.palermo@gmail.com>
 * @copyright Since 2016 Massimiliano Palermo
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

use MpSoft\MpStockV2\Helpers\GetProductAttributeCombination;
use MpSoft\MpStockV2\Helpers\ParseXml;
use MpSoft\MpStockV2\Helpers\Response;

class AdminMpStockImportController extends ModuleAdminController
{
    protected $mvtReasons = [];

    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'mpstock_document_v2';
        $this->className = 'ModelMpStockDocumentV2';
        $this->lang = false;
        $this->context = Context::getContext();
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        parent::__construct();

        $fetchData = file_get_contents('php://input');
        if ($fetchData) {
            $data = json_decode($fetchData, true);
            if (isset($data['action']) && $data['action'] && isset($data['ajax']) && $data['ajax']) {
                $action = 'fetchProcess' . Tools::ucFirst($data['action']);
                if (method_exists($this, $action)) {
                    Response::json($this->$action($data));
                }
            }
        }

        $this->mvtReasons = ModelMpStockMvtReasonV2::getMvtReasons($this->context->language->id);
    }

    protected function response($params)
    {
        header('Content-Type: application/json; charset=utf-8');
        ob_clean();
        exit(json_encode($params));
    }

    public function setMedia()
    {
        parent::setMedia();

        $this->addJqueryUI('ui.datepicker');
        $this->addCSS(_MODULE_DIR_ . 'mpstockv2/views/js/plugins/datatables/datatables.min.css');
        $this->addJS(_MODULE_DIR_ . 'mpstockv2/views/js/plugins/datatables/datatables.min.js');
        $this->addCSS(_MODULE_DIR_ . 'mpstockv2/views/js/plugins/toastify/toastify.css');
        $this->addJS(_MODULE_DIR_ . 'mpstockv2/views/js/plugins/toastify/toastify.js');
        $this->addJS(_MODULE_DIR_ . 'mpstockv2/views/js/plugins/toastify/showToastify.js');
        $this->addCSS(_MODULE_DIR_ . 'mpstockv2/views/css/style.css');
        $this->addJqueryPlugin('autocomplete');
        $this->addJqueryUI('ui.autocomplete');
    }

    public function initContent()
    {
        $tpl_path = $this->getTemplatePath() . 'import/ImportDocument.tpl';
        $tpl = $this->context->smarty->createTemplate($tpl_path);
        $tpl->assign([
            'admin_controller_url' => $this->context->link->getAdminLink($this->controller_name),
            'fetchURL' => $this->context->link->getAdminLink($this->controller_name),
            'mvtReasons' => $this->mvtReasons,
        ]);

        $content = $tpl->fetch();
        $this->content = $content;

        return parent::initContent();
    }

    public function ajaxProcessParseFile()
    {
        $file = Tools::fileAttachment('document_xml', false);
        $parser = new ParseXml($file['tmp_name'], $file['name']);
        $parsed = $parser->parse();
        if ($parsed) {
            $movementId = (int) $parser->getMovementType();
            $response = [
                'success' => true,
                'document' => $parser->getDocumentNumber(),
                'type' => $parser->getDocumentType(),
                'date' => $parser->getDocumentDate(),
                'movementId' => $movementId,
                'movement' => ModelMpStockMvtReasonV2::getMovementType($movementId),
                'rows' => $parser->getDocumentContent(),
            ];

            Response::json($response);
        } else {
            Response::json(
                [
                    'success' => false,
                    'message' => $parser->getError(),
                ]
            );
        }
    }

    public function ajaxProcessImportXml()
    {
        $file = Tools::fileAttachment('document_xml', false);
        $mvtReasonId = (int) Tools::getValue('mvtReason');
        $parser = new ParseXml($file['tmp_name'], $file['name']);
        $parsed = $parser->parse();

        Response::json([
            'success' => $parsed,
            'rows' => $parser->getDocumentContent(),
        ]);

        if ($parsed) {
            $result = ModelMpStockDocumentV2::createImportDocument($parser, $mvtReasonId);
        } else {
            $error = $parser->getError();
            $result = sprintf($this->module->l('Errore %s durante la lettura del file.', get_class($this)), $error);
        }

        if ($result === true) {
            Response::json(
                [
                    'success' => true,
                    'message' => $this->module->l('Importazione avvenuta con successo.', get_class($this)),
                ]
            );
        }

        Response::json(
            [
                'success' => false,
                'message' => $result,
            ]
        );
    }

    public function fetchProcessImportData($data)
    {
        $mvtReasonId = (int) $data['mvtReasonId'];
        $filename = $data['filename'];
        $rows = $data['rows'];
        $sign = 0;
        $id_lang = (int) Context::getContext()->language->id;

        $mvtReason = new ModelMpStockMvtReasonV2($mvtReasonId, $id_lang);
        if (!Validate::isLoadedObject($mvtReason)) {
            return [
                'success' => false,
                'message' => sprintf(Tools::displayError('Movimento non valido: %s'), $mvtReasonId),
            ];
        }

        $sign = (int) $mvtReason->sign;

        $document = ModelMpStockDocumentV2::getByFilename($filename);
        if ($document) {
            return [
                'success' => false,
                'message' => sprintf(Tools::displayError('Esiste già un documento con questo nome: %s'), $filename),
            ];
        }

        if (preg_match('/([CS])*\((.*)-(.*)\)(.*)\.xml$/i', $filename, $matches)) {
            $documentType = $matches[1] === 'C' ? 'Carico' : 'Scarico';
            $documentNumber = $matches[2];
            $documentDate = $matches[3];
        }

        if (preg_match('/^C:\\\\fakepath\\\\(.*)$/i', $filename, $matches)) {
            $filename = $matches[1];
        }

        $document = new ModelMpStockDocumentV2();
        $document->number_document = $filename;
        $document->date_document = date('Y-m-d');
        $document->id_mpstock_mvt_reason = $mvtReasonId;
        $document->id_employee = (int) Context::getContext()->employee->id;
        $document->id_supplier = 0;

        try {
            $result = $document->add();
        } catch (\Throwable $th) {
            return [
                'success' => false,
                'message' => $th->getMessage(),
            ];
        }

        $id_document = (int) $document->id;
        if (!$id_document) {
            return [
                'success' => false,
                'message' => Tools::displayError('Errore durante l\'aggiornamento del documento'),
            ];
        }

        foreach ($rows as $row) {
            $id_pa = GetProductAttributeCombination::getIdByEan13($row['ean13']);
            if (!$id_pa) {
                $id_pa = GetProductAttributeCombination::getIdByReference($row['reference']);
            }
            if (!$id_pa) {
                continue;
            }

            $pa = new \Combination($id_pa, $id_lang);
            $qty = $row['qty'] * $sign;

            $movement = new ModelMpStockMovementV2();
            $movement->id_document = $id_document;
            $movement->id_warehouse = 0;
            $movement->id_supplier = 0;
            $movement->document_number = $document->number_document;
            $movement->document_date = $document->date_document;
            $movement->id_mpstock_mvt_reason = $document->id_mpstock_mvt_reason;
            $movement->mvt_reason = $mvtReason->name;
            $movement->id_product = $pa->id_product;
            $movement->id_product_attribute = $pa->id;
            $movement->reference = $pa->reference;
            $movement->ean13 = $pa->ean13;
            $movement->upc = $pa->upc;
            $movement->price_te = 0;
            $movement->wholesale_price_te = 0;
            $movement->id_employee = (int) Context::getContext()->employee->id;
            $movement->id_order = 0;
            $movement->id_order_detail = 0;
            $movement->stock_quantity_before = \StockAvailable::getQuantityAvailableByProduct($pa->id_product, $pa->id);
            $movement->stock_movement = $qty;
            $movement->stock_quantity_after = \StockAvailable::getQuantityAvailableByProduct($pa->id_product, $pa->id) + $qty;
            $movement->date_add = date('Y-m-d H:i:s');

            try {
                $result = $movement->add();
                StockAvailable::updateQuantity($pa->id_product, $pa->id, $qty);
            } catch (\Throwable $th) {
                $this->errors[] = $th->getMessage();
            }
        }

        return [
            'success' => !$this->errors,
            'rows' => $rows,
            'filename' => $filename,
            'sign' => $sign,
            'message' => sprintf(
                $this->module->l('Importazione avvenuta con %s.', get_class($this)),
                $this->errors ? 'errori' : 'successo'
            ),
        ];
    }
}
