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

use MpSoft\MpStock\Helpers\ParseXml;
use MpSoft\MpStockV2\Helpers\Response;

class AdminMpStockConfigController extends ModuleAdminController
{
    protected $mvtReasons = [];

    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'mpstock_document_v2';
        $this->className = 'ModelMpStockMvtReasonV2';
        $this->lang = false;
        $this->context = Context::getContext();
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        parent::__construct();

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
        $this->addJS(_MODULE_DIR_ . 'mpstockv2/views/js/plugins/swal2/swal2.js');
        $this->addJS(_MODULE_DIR_ . 'mpstockv2/views/js/plugins/htmx/htmx.js');
        $this->addCSS(_MODULE_DIR_ . 'mpstockv2/views/css/style.css');
        $this->addJqueryPlugin('autocomplete');
        $this->addJqueryUI('ui.autocomplete');
        $this->addJS(_MODULE_DIR_ . 'mpstockv2/views/js/showGrowlMessages.js');
    }

    public function initContent()
    {
        $tpl_path = $this->getTemplatePath() . 'config/config.tpl';
        $tpl = $this->context->smarty->createTemplate($tpl_path);
        $tpl->assign([
            'admin_controller_url' => $this->context->link->getAdminLink($this->controller_name),
            'mvtReasons' => $this->mvtReasons,
        ]);

        $content = $tpl->fetch();
        $this->content = $content;

        return parent::initContent();
    }

    public function ajaxProcessGetMvtReasonsList()
    {
        $start = (int) Tools::getValue('start');
        $length = (int) Tools::getValue('length');
        $search = Tools::getValue('search')['value'];
        $draw = (int) Tools::getValue('draw');
        $columns = Tools::getValue('columns');
        $order = Tools::getValue('order');

        $model = new ModelMpStockMvtReasonV2();
        $mvtReasons = $model->dataTable($start, $length, $columns, $order);

        Response::json(
            [
                'draw' => $draw,
                'recordsTotal' => $mvtReasons['totalRecords'],
                'recordsFiltered' => $mvtReasons['totalFiltered'],
                'data' => $mvtReasons['data'],
            ]
        );
    }

    public function ajaxProcessGet()
    {
        $id = (int) Tools::getValue('id_mpstock_mvt_reason');
        $id_lang = Context::getContext()->language->id;
        $mvtReason = new ModelMpStockMvtReasonV2($id, $id_lang);
        $fields = $mvtReason->getFields();
        $fields['name'] = $mvtReason->name;

        Response::json($fields);
    }

    public function ajaxProcessSave()
    {
        $id_lang = Context::getContext()->language->id;
        $id = (int) Tools::getValue('reason_code');
        $name = Tools::getValue('reason_name');
        $sign = (int) Tools::getValue('reason_sign');
        $error = null;

        $model = new ModelMpStockMvtReasonV2($id, $id_lang);
        $model->name = $name;
        $model->sign = $sign;
        $model->active = true;

        try {
            if (Validate::isLoadedObject($model)) {
                $result = $model->update();
            } else {
                $model->force_id = true;
                $model->id = $id;
                $result = $model->add();
            }
        } catch (\Throwable $th) {
            $error = sprintf('Errore %s durante il salvataggio', $th->getMessage());
        }

        if (!$result) {
            $message = sprintf($this->module->l('Errore %s durante il salvataggio', get_class($this)), $error);
        } else {
            $message = sprintf($this->module->l('Salvataggio avvenuto con successo', get_class($this)), $error);
        }

        Response::json(
            [
                'success' => $result,
                'message' => $message,
                'error' => $error,
            ]
        );
    }

    public function ajaxProcessLoadFile()
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
                'content' => $parser->getDocumentContent(),
            ];

            $this->response( $response);
        } else {
            $this->response([
                'success' => false,
                'message' => $parser->getError(),
            ]);
        }
    }

    public function ajaxProcessImportXml()
    {
        $file = Tools::fileAttachment('document_xml', false);
        $mvtReasonId = (int) Tools::getValue('mvtReason');
        $parser = new ParseXml($file['tmp_name'], $file['name']);
        $parsed = $parser->parse();
        if ($parsed) {
            $result = ModelMpStockDocumentV2::createImportDocument($parser, $mvtReasonId);
        } else {
            $error = $parser->getError();
            $result = sprintf($this->module->l('Errore %s durante la lettura del file.', get_class($this)), $error);
        }

        if ($result === true) {
            $this->response([
                'success' => true,
                'message' => $this->module->l('Importazione avvenuta con successo.', get_class($this)),
            ]);
        }

        $this->response([
            'success' => false,
            'message' => $result,
        ]);
    }
}
