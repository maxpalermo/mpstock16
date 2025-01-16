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

use MpSoft\MpStockV2\Helpers\Response;
use MpSoft\MpStockV2\Migrations\MpStockDocument;
use MpSoft\MpStockV2\Migrations\MpStockMovement;
use MpSoft\MpStockV2\Migrations\MpStockMvtReason;

class MpStockV2AjaxModuleFrontController extends ModuleFrontController
{
    public function getTemplateVarProduct()
    {
        $id_product = (int) Tools::getValue('id_product');
        $product = new Product($id_product);
        $product->loadStockData();
        $result = [
            'id_product' => $id_product,
            'ean13' => $product->ean13,
            'upc' => $product->upc,
            'quantity' => $product->quantity,
            'available_date' => $product->available_date,
        ];
        die(json_encode($result));
    }

    public function init()
    {
        if (Tools::issubmit('action')) {
            $this->ajax = true;
            $action = 'ajaxProcess' . Tools::ucfirst(Tools::getValue('action'));
            Response::json($this->$action());
        }
        parent::init();
    }

    public function ajaxProcessCreateMvtReasonTable()
    {
        $result = MpStockMvtReason::up();
        Response::json(
            [
                'success' => $result,
                'message' => 'Tabella motivi movimenti creata con successo',
            ]
        );
    }

    public function ajaxProcessCreateDocumentTable()
    {
        $result = MpStockDocument::up();
        Response::json(
            [
                'success' => $result,
                'message' => 'Tabella documenti creata con successo',
            ]
        );
    }

    public function ajaxProcessCreateMovementTable()
    {
        $result = MpStockMovement::up();
        Response::json(
            [
                'success' => $result,
                'message' => 'Tabella movimenti creata con successo',
            ]
        );
    }

    public function ajaxProcessImportMvtReasonTable()
    {
        $errors = ModelMpStockMvtReasonV2::updateTable();
        Response::json(
            [
                'success' => $errors === [] || is_int($errors),
                'message' => 'Tabella motivi movimenti aggiornata con successo',
                'errors' => $errors,
            ]
        );
    }

    public function ajaxProcessImportDocumentTable()
    {
        $result = ModelMpStockDocumentV2::updateTable();
        Response::json(
            [
                'success' => !$result['errors'],
                'message' => 'Tabella documenti aggiornata con ' . ($result['errors'] ? 'errori' : 'successo'),
                'errors' => $result['errors'],
                'rows_affected' => (int) $result['rows_affected'],
            ]
        );
    }

    public function ajaxProcessImportMovementTable()
    {
        $result = ModelMpStockMovementV2::updateTable();
        if ($result['errors']) {
            Response::json(
                [
                    'success' => false,
                    'message' => 'Tabella movimenti aggiornata con errori',
                    'errors' => $result['errors'],
                    'rows_affected' => $result['rows_affected'],
                ]
            );
        }
        Response::json(
            [
                'success' => $result['errors'] === [] || is_int($result['errors']),
                'message' => 'Tabella movimenti aggiornata con successo',
                'errors' => $result['errors'],
                'rows_affected' => $result['rows_affected'],
            ]
        );
    }

    public function ajaxProcessSaveMvtReason()
    {
        $id_mvt_reason = (int) Tools::getValue('mvtReasonId');
        Configuration::updateValue(MpStockV2::MPSTOCKV2_MVT_REASON_ID, $id_mvt_reason);
        Response::json(
            [
                'success' => true,
                'message' => 'Salvataggio avvenuto con successo',
                'rows_affected' => 1,
                'errors' => [],
            ]
        );
    }
}
