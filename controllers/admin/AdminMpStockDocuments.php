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

class AdminMpStockDocumentsController extends ModuleAdminController
{
    protected $mvtReasons;
    protected $suppliers;
    protected $employees;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'mpstock_document_v2';
        $this->identifier = 'id_mpstock_document';
        $this->className = 'ModelMpStockDocumentV2';
        $this->lang = false;
        $this->deleted = false;
        $this->explicitSelect = true;
        $this->allow_export = true;
        $this->context = Context::getContext();
        $this->_orderBy = 'date_add';
        $this->_orderWay = 'DESC';

        $this->mvtReasons = $this->getMvtReasons();
        $this->suppliers = $this->getSuppliers();
        $this->employees = $this->getEmployees();

        parent::__construct();
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

    public function renderList()
    {
        return false;
    }

    public function initContent()
    {
        $tpl = $this->context->smarty->createTemplate(
            $this->getTemplatePath() . 'documents/document.tpl',
            $this->context->smarty
        );

        $params = [
            'admin_controller_url' => $this->context->link->getAdminLink('AdminMpStockDocuments'),
            'mvtReasons' => $this->mvtReasons,
            'suppliers' => $this->suppliers,
            'employees' => $this->employees,
        ];

        $tpl->assign($params);

        $page = $tpl->fetch();

        $this->content = $page;

        return parent::initContent();
    }

    public function ajaxProcessSaveDocument()
    {
        $id_document = (int) Tools::getValue('id_document');
        $number_document = Tools::getValue('number_document');
        $date_document = Tools::getValue('date_document');
        $id_supplier = (int) Tools::getValue('id_supplier');
        $id_mpstock_mvt_reason = (int) Tools::getValue('id_mpstock_mvt_reason');
        $id_employee = (int) Tools::getValue('id_employee');

        $model = new ModelMpStockDocumentV2($id_document);
        $model->number_document = $number_document;
        $model->date_document = $date_document;
        $model->id_supplier = $id_supplier;
        $model->id_mpstock_mvt_reason = $id_mpstock_mvt_reason;
        $model->id_employee = $id_employee;

        $result = $model->save();

        Response::json([
            'success' => (bool) $result,
        ]);
    }

    public function ajaxProcessGetDocuments()
    {
        $start = (int) Tools::getValue('start');
        $length = (int) Tools::getValue('length');
        $search = Tools::getValue('search')['value'];
        $draw = (int) Tools::getValue('draw');
        $columns = Tools::getValue('columns');
        $order = Tools::getValue('order');

        $model = new ModelMpStockDocumentV2();
        $documents = $model->dataTable($start, $length, $columns, $order);

        Response::json(
            [
                'draw' => $draw,
                'recordsTotal' => $documents['totalRecords'],
                'recordsFiltered' => $documents['totalFiltered'],
                'data' => $documents['data'],
            ]
        );
    }

    public function ajaxProcessGetInvoiceDetails($id_invoice = null, $ajax = true)
    {
        if ($ajax) {
            $data = file_get_contents('php://input');
            $data = json_decode($data, true);
            $id_invoice = (int) $data['id_invoice'];
        } else {
            if (!$id_invoice) {
                return false;
            }
        }

        $movements = ModelMpStockMovementV2::getMovementsByIdDocument($id_invoice);
        $tpl = $this->context->smarty->createTemplate(
            $this->getTemplatePath() . 'documents/document-details.tpl',
            $this->context->smarty
        );

        $params = [
            'movements' => $movements,
            'mvtReasons' => $this->mvtReasons,
            'suppliers' => $this->suppliers,
            'employees' => $this->employees,
            'id_invoice' => $id_invoice,
            'id_document' => $id_invoice,
            'admin_controller_url' => $this->context->link->getAdminLink('AdminMpStockDocuments'),
        ];

        $tpl->assign($params);

        $table = $tpl->fetch();

        $response =
            [
                'id_invoice' => $id_invoice,
                'id_document' => $id_invoice,
                'movements' => $movements,
                'content' => $table,
            ];

        if ($ajax) {
            Response::json($response);
        }

        return $response;
    }

    public function ajaxProcessSearchTermProduct()
    {
        $term = Tools::getValue('term');
        $sql = new DbQuery();
        $sql->select('p.id_product as value, pl.name as label')
            ->from('product', 'p')
            ->leftJoin('product_lang', 'pl', 'p.id_product = pl.id_product AND pl.id_lang = ' . (int) $this->context->language->id)
            ->where('p.reference LIKE "%' . pSQL($term) . '%" OR pl.name LIKE "%' . pSQL($term) . '%"')
            ->limit(10);

        $result = Db::getInstance()->executeS($sql);

        Response::json($result);
    }

    public function ajaxProcessGetProductAttributes()
    {
        $id_product = (int) Tools::getValue('productId');
        $sql = new DbQuery();
        $sql->select('pa.id_product_attribute as value, GROUP_CONCAT(CONCAT(agl.name, ":", al.name) SEPARATOR ", ") as label')
            ->from('product_attribute', 'pa')
            ->leftJoin('product_attribute_combination', 'pac', 'pa.id_product_attribute = pac.id_product_attribute')
            ->leftJoin('attribute', 'a', 'pac.id_attribute = a.id_attribute')
            ->leftJoin('attribute_lang', 'al', 'a.id_attribute = al.id_attribute AND al.id_lang = ' . (int) $this->context->language->id)
            ->leftJoin('attribute_group', 'ag', 'a.id_attribute_group = ag.id_attribute_group')
            ->leftJoin('attribute_group_lang', 'agl', 'ag.id_attribute_group = agl.id_attribute_group AND agl.id_lang = ' . (int) $this->context->language->id)
            ->where('pa.id_product = ' . $id_product)
            ->groupBy('pa.id_product_attribute')
            ->orderBy('pa.id_product_attribute ASC');

        $result = Db::getInstance()->executeS($sql);

        Response::json($result);
    }

    public function ajaxProcessGetCurrentStock()
    {
        $id_product = (int) Tools::getValue('productId');
        $id_product_attribute = (int) Tools::getValue('productAttributeId');

        $stock = StockAvailable::getQuantityAvailableByProduct($id_product, $id_product_attribute);

        Response::json(
            [
                'currentStock' => (int) $stock,
            ]
        );
    }

    public function ajaxProcessSaveMovement()
    {
        $id_lang = (int) $this->context->language->id;
        $documentId = (int) Tools::getValue('documentId');
        $productId = (int) Tools::getValue('productId');
        $productAttributeId = (int) Tools::getValue('productAttributeId');
        $movementReason = (int) Tools::getValue('movementReason');
        $quantity = (int) Tools::getValue('movementQuantity');
        $quantityAfter = (int) Tools::getValue('movementQuantityAfter');

        $message = null;

        $product = new Product($productId, false, $id_lang);
        $combination = new Combination($productAttributeId);
        $document = new ModelMpStockDocumentV2($documentId);
        if (!Validate::isLoadedObject($document)) {
            Response::json(
                [
                    'success' => false,
                    'title' => $this->module->l('Salva Movimento', get_class($this)),
                    'message' => $this->module->l('Errore: Documento non trovato', get_class($this)),
                ]
            );
        }
        $mvtReason = new ModelMpStockMvtReasonV2($movementReason, $id_lang);
        if (!Validate::isLoadedObject($mvtReason)) {
            Response::json(
                [
                    'success' => false,
                    'title' => $this->module->l('Salva Movimento', get_class($this)),
                    'message' => $this->module->l('Errore: Tipo di movimento non trovato', get_class($this)),
                ]
            );
        }
        $sign = (int) $mvtReason->sign;

        $id_supplier = (int) $document->id_supplier;
        $document_number = $document->number_document;
        $document_date = $document->date_document;

        $model = new ModelMpStockMovementV2();
        $model->id_document = $documentId;
        $model->id_warehouse = 0;
        $model->id_supplier = $id_supplier;
        $model->document_number = $document_number;
        $model->document_date = $document_date;
        $model->id_mpstock_mvt_reason = $movementReason;
        $model->id_product = $productId;
        $model->id_product_attribute = $productAttributeId;
        $model->reference = $product->reference;
        $model->ean13 = $combination->ean13;
        $model->upc = $combination->upc;
        $model->price_te = $product->price;
        $model->wholesale_price_te = $product->wholesale_price;
        $model->id_employee = (int) $this->context->employee->id;
        $model->date_add = date('Y-m-d H:i:s');
        $model->date_upd = null;
        $model->id_order = null;
        $model->id_order_detail = null;
        $model->mvt_reason = $this->getMvtReasonName($movementReason);
        $model->stock_quantity_before = StockAvailable::getQuantityAvailableByProduct($productId, $productAttributeId);
        $model->stock_movement = $quantity;
        $model->stock_quantity_after = $quantityAfter;

        try {
            $res = $model->add();

            StockAvailable::updateQuantity($productId, $productAttributeId, $quantity * $sign);

            $message = $this->l('Movimento salvato correttamente');
        } catch (\Throwable $th) {
            $res = false;
            $message = $th->getMessage();
        }

        Response::json(
            [
                'success' => $res,
                'title' => $this->module->l('Salva Movimento', get_class($this)),
                'message' => $message,
            ]
        );
    }

    protected function refreshTableMovements($id_invoice)
    {
        $db = Db::getInstance();
        $query = new DbQuery();
    }

    public function renderForm()
    {
        return parent::renderForm();
    }

    protected function getMvtReasons()
    {
        $id_lang = (int) $this->context->language->id;
        $sql = new DbQuery();
        $sql->select('a.id_mpstock_mvt_reason, a.sign, b.name')
            ->from('mpstock_mvt_reason_v2', 'a')
            ->leftJoin('mpstock_mvt_reason_lang', 'b', 'a.id_mpstock_mvt_reason = b.id_mpstock_mvt_reason and b.id_lang=' . (int) $this->context->language->id)
            ->orderBy('b.name ASC');

        $result = Db::getInstance()->executeS($sql);
        if (!$result) {
            return [];
        }

        return $result;
    }

    protected function getSuppliers()
    {
        $id_lang = (int) $this->context->language->id;
        $sql = new DbQuery();
        $sql->select('*')
            ->from('supplier')
            ->orderBy('name ASC');

        $result = Db::getInstance()->executeS($sql);
        if (!$result) {
            return [];
        }

        return $result;
    }

    protected function getEmployees()
    {
        $id_lang = (int) $this->context->language->id;
        $sql = new DbQuery();
        $sql->select('*')
            ->from('employee')
            ->orderBy('lastname ASC');

        $result = Db::getInstance()->executeS($sql);
        if (!$result) {
            return [];
        }

        return $result;
    }

    public function getMvtReasonName($value, $returnsName = true)
    {
        if ((int) $value == 0) {
            return '--';
        }

        if (!isset($this->mvtReasons[$value])) {
            return '--';
        }

        $reason = $this->mvtReasons[$value];
        if ($returnsName) {
            return $reason['name'];
        }

        return $reason;
    }

    public function getSupplierName($value)
    {
        if ((int) $value == 0) {
            return '--';
        }

        if (!isset($this->suppliers[$value])) {
            return '--';
        }

        return $this->suppliers[$value];
    }

    public function getEmployeeName($value)
    {
        if ((int) $value == 0) {
            return '--';
        }

        if (!isset($this->employees[$value])) {
            return '--';
        }

        return $this->employees[$value];
    }
}
