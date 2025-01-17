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
    }

    public function initContent()
    {
        $tpl_path = $this->getTemplatePath() . 'import/ImportDocument.tpl';
        $tpl = $this->context->smarty->createTemplate($tpl_path);
        $tpl->assign([
            'admin_controller_url' => $this->context->link->getAdminLink($this->controller_name),
            'mvtReasons' => $this->mvtReasons,
        ]);

        $content = $tpl->fetch();
        $this->content = $content;

        return parent::initContent();
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
}
