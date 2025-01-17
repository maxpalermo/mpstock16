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

use MpSoft\MpStock\Helpers\GetProductAttributeCombination;

class AdminMpStockMovementsController extends ModuleAdminController
{
    protected $mvtSigns;
    protected $mvtReasons;
    protected $suppliers;
    protected $employees;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'mpstock_product';
        $this->identifier = 'id_mpstock_product';
        $this->className = 'ModelMpStockMovementV2';
        $this->lang = false;
        $this->deleted = false;
        $this->explicitSelect = true;
        $this->allow_export = true;
        $this->context = Context::getContext();
        $this->_orderBy = 'date_add';
        $this->_orderWay = 'DESC';

        $this->mvtSigns = $this->getMvtSigns();
        $this->mvtReasons = $this->getMvtReasons();
        $this->suppliers = $this->getSuppliers();
        $this->employees = $this->getEmployees();

        $this->_select = 'd.number_document, d.date_document,pl.name, \'\' as remain';
        $this->_join = 'LEFT JOIN ' . _DB_PREFIX_ . 'mpstock_document_v2 d ON (a.id_document = d.id_mpstock_document)';
        $this->_join .= ' LEFT JOIN ' . _DB_PREFIX_ . 'product_lang pl ON (a.id_product = pl.id_product AND pl.id_lang = ' . (int) $this->context->language->id . ')';

        parent::__construct();
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

    public function initPageHeaderToolbar()
    {
        $this->page_header_toolbar_btn['new_document'] = [
            'href' => static::$currentIndex . '&add' . 'mpstock_document_v2' . '&token=' . $this->token,
            'desc' => $this->l('Nuovo documento'),
            'icon' => 'process-icon-new',
        ];

        $this->page_header_toolbar_btn['new_movement'] = [
            'href' => 'javascript:showPanelNewMovements();',
            'desc' => $this->l('Nuovo movimento'),
            'icon' => 'process-icon-list',
        ];

        $this->page_header_toolbar_btn['import_orders'] = [
            'href' => 'javascript:showImportPanel();',
            'desc' => $this->l('Importa ordini'),
            'icon' => 'process-icon-download',
            'confirm' => $this->l('Sei sicuro di voler importare gli ordini?'),
        ];

        parent::initPageHeaderToolbar();
    }

    public function initContent()
    {
        $tpl = $this->context->smarty->createTemplate(
            $this->getTemplatePath() . 'dataTables/movements.tpl',
            $this->context->smarty
        );

        $params = [
            'admin_controller_url' => $this->context->link->getAdminLink('AdminMpStockMovements'),
            'mvtReasons' => $this->mvtReasons,
            'suppliers' => $this->suppliers,
            'employees' => $this->employees,
        ];

        $tpl->assign($params);

        $page = $tpl->fetch();

        $this->content = $page;

        return parent::initContent();
    }

    public function postProcess()
    {
        if (Tools::isSubmit('fetch')) {
            $data = file_get_contents('php://input');
            if ($data) {
                $data = json_decode($data, true);
                if (isset($data['action'])) {
                    try {
                        $this->response($this->{'ajaxProcess' . Tools::ucfirst($data['action'])}());
                    } catch (\Throwable $th) {
                        $this->response([
                            'error' => $th->getMessage(),
                        ]);
                    }
                }
            }
        }

        return parent::postProcess();
    }

    public function processimportOrdersDetails()
    {
        $class = new importOrdersDetails();
        $result = $class->importOrdersDetails([]);
        if ($result['errors']) {
            foreach ($result['errors'] as $error) {
                $this->errors[] = $error;
            }
        }

        if ($result['success']) {
            $this->confirmations[] = $this->l('Importazione completata');
        }
    }

    public function renderForm()
    {
        return parent::renderForm();
    }

    protected function getMvtSigns()
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('*')
            ->from('mpstock_mvt_reason_v2');
        $rows = $db->executeS($sql);

        $out = [];
        foreach ($rows as $row) {
            $out[$row['id_mpstock_mvt_reason']] = $row['sign'] ? -1 : 1;
        }

        return $out;
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
        $out = [];
        foreach ($result as $row) {
            $out[$row['id_supplier']] = $row;
        }

        return $out;
    }

    protected function getEmployees()
    {
        $id_lang = (int) $this->context->language->id;
        $sql = new DbQuery();
        $sql->select('*')
            ->from('employee')
            ->orderBy('lastname ASC');

        $result = Db::getInstance()->executeS($sql);
        $out = [];
        foreach ($result as $row) {
            $row['name'] = $row['lastname'] . ' ' . $row['firstname'];
            $out[$row['id_employee']] = $row;
        }

        return $out;
    }

    public function getMvtReasonName($value)
    {
        if ((int) $value == 0) {
            return '--';
        }

        if (!isset($this->mvtReasons[$value])) {
            return '--';
        }

        return $this->mvtReasons[$value];
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

    public function getStockQuantity($value)
    {
        if ($value < 0) {
            return "<span class='text-danger font-bold font-20'>$value</span>";
        }

        if ($value > 0) {
            return "<span class='text-success font-bold font-20'>$value</span>";
        }

        return "<span class='text-warning font-bold font-20'>$value</span>";
    }

    public function getSignQuantity($value, $row, $returnValue = false)
    {
        $mvt = $row['id_mpstock_mvt_reason'];
        if ($mvt != 0) {
            $sign = $this->mvtSigns[$mvt];
            $value = (int) abs($value) * $sign;
        }

        if ($returnValue) {
            return $value;
        }

        return $this->getStockQuantity($value);
    }

    public function getRemainQuantity($value, $row)
    {
        $stock = (int) $row['physical_quantity'];
        $mvt = (int) $this->getSignQuantity($row['usable_quantity'], $row, true);
        $remain = $stock + $mvt;

        return $this->getStockQuantity($remain);
    }

    public function getCombination($value)
    {
        if ($value == 0) {
            return '--';
        }

        $combination = new Combination($value);

        $value = $combination->getAttributesName($this->context->language->id);
        $comb = '';
        foreach ($value as $v) {
            $comb .= $v['name'] . ' ';
        }

        return Tools::strtoupper(trim($comb));
    }

    protected function response($params)
    {
        header('Content-Type: application/json; charset=utf-8');
        ob_clean();
        exit(json_encode($params));
    }

    public function ajaxProcessGetOrdersDetails()
    {
        $class = new ImportOrdersDetails();
        $result = $class->getOrdersDetails();

        $this->response($result);
    }

    public function ajaxProcessImportOrdersDetails()
    {
        $data = file_get_contents('php://input');
        $data = json_decode($data, true);
        $class = new ImportOrdersDetails();
        $result = $class->importOrdersDetails($data['ordersDetails']);

        $this->response([
            'success' => $result['success'],
            'errors' => $result['errors'],
        ]);
    }

    public function ajaxProcessGetMovements()
    {
        $start = (int) Tools::getValue('start');
        $length = (int) Tools::getValue('length');
        $search = Tools::getValue('search')['value'];
        $draw = (int) Tools::getValue('draw');
        $columns = Tools::getValue('columns');
        $order = Tools::getValue('order');

        $model = new ModelMpStockMovementV2();
        $movements = $model->dataTable($start, $length, $columns, $order);

        foreach ($movements['data'] as &$row) {
            $row['product_name'] = ModelMpStockMovementV2::getProductNameById($row['id_product']);
            $row['product_combination'] = ModelMpStockMovementV2::getProductCombinationById($row['id_product'], $row['id_product_attribute']);
            $row['actions'] = '';
        }

        $this->response(
            [
                'draw' => $draw,
                'recordsTotal' => $movements['totalRecords'],
                'recordsFiltered' => $movements['totalFiltered'],
                'data' => $movements['data'],
            ]
        );
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

        $this->response($result);
    }

    public function ajaxProcessGetProductAttributes()
    {
        $id_product = (int) Tools::getValue('productId');
        $combinations = GetProductAttributeCombination::getProductCombinations($id_product);

        $this->response($combinations);
    }

    public function ajaxProcessGetCurrentStock()
    {
        $id_product = (int) Tools::getValue('productId');
        $id_product_attribute = (int) Tools::getValue('productAttributeId');

        $stock = StockAvailable::getQuantityAvailableByProduct($id_product, $id_product_attribute);

        $this->response([
            'currentStock' => (int) $stock,
        ]);
    }

    public function ajaxProcessSaveMovement()
    {
        $id_lang = (int) $this->context->language->id;
        $productId = (int) Tools::getValue('productId');
        $productAttributeId = (int) Tools::getValue('productAttributeId');
        $quantity = (int) Tools::getValue('movementQuantity');
        $quantityAfter = (int) Tools::getValue('movementQuantityAfter');
        $reason = (int) Tools::getValue('movementReason');
        $message = null;

        $product = new Product($productId, false, $id_lang);
        $combination = new Combination($productAttributeId);

        $model = new ModelMpStockMovementV2();
        $model->id_document = null;
        $model->ref_movement = null;
        $model->id_warehouse = null;
        $model->id_supplier = null;
        $model->document_number = null;
        $model->document_date = null;
        $model->id_mpstock_mvt_reason = $reason;
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
        $model->mvt_reason = $this->getMvtReasonName($reason);
        $model->stock_quantity_before = StockAvailable::getQuantityAvailableByProduct($productId, $productAttributeId);
        $model->stock_movement = $quantity;
        $model->stock_quantity_after = $quantityAfter;

        try {
            $res = $model->add();
            $message = $this->l('Movimento salvato correttamente');
        } catch (\Throwable $th) {
            $res = false;
            $message = $th->getMessage();
        }

        if (!$res) {
            $this->response([
                'success' => $res,
                'message' => $message,
            ]);
        }
    }
}
