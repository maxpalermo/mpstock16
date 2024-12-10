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
        $this->className = 'ModelMpStockMovement';
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
        $this->_join = 'LEFT JOIN ' . _DB_PREFIX_ . 'mpstock_document d ON (a.id_document = d.id_mpstock_document)';
        $this->_join .= ' LEFT JOIN ' . _DB_PREFIX_ . 'product_lang pl ON (a.id_product = pl.id_product AND pl.id_lang = ' . (int) $this->context->language->id . ')';

        $this->fields_list = [
            'id_mpstock_product' => [
                'title' => $this->l('ID'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ],
            'number_document' => [
                'title' => $this->l('Numero'),
                'filter_key' => 'd!number_document',
            ],
            'date_document' => [
                'title' => $this->l('Data'),
                'filter_key' => 'd!date_document',
            ],
            'id_mpstock_mvt_reason' => [
                'title' => $this->l('Movimento'),
                'type' => 'select',
                'list' => $this->mvtReasons,
                'filter_key' => 'a!id_mpstock_mvt_reason',
                'callback' => 'getMvtReasonName',
            ],
            'id_order' => [
                'title' => $this->l('Ordine'),
                'filter_key' => 'a!id_order',
            ],
            'id_order_detail' => [
                'title' => $this->l('Riga ordine'),
                'filter_key' => 'a!id_order_detail',
            ],
            'reference' => [
                'title' => $this->l('Riferimento'),
                'filter_key' => 'a!reference',
                'remove_onclick' => true,
            ],
            'product_name' => [
                'title' => $this->l('Prodotto'),
                'filter_key' => 'pl!name',
            ],
            'id_product_attribute' => [
                'title' => $this->l('Combinazione'),
                'search' => false,
                'callback' => 'getCombination',
            ],
            'ean13' => [
                'title' => $this->l('EAN13'),
                'remove_onclick' => true,
            ],
            'physical_quantity' => [
                'title' => $this->l('Magazzino'),
                'float' => true,
                'align' => 'text-right',
                'callback' => 'getStockQuantity',
            ],
            'usable_quantity' => [
                'title' => $this->l('Quantità'),
                'float' => true,
                'align' => 'text-right',
                'callback' => 'getSignQuantity',
            ],
            'remain' => [
                'title' => $this->l('Giacenza'),
                'float' => true,
                'search' => false,
                'align' => 'text-right',
                'callback' => 'getRemainQuantity',
            ],
            'price_te' => [
                'title' => $this->l('Prezzo (i.e.)'),
                'type' => 'price',
                'align' => 'text-right',
            ],
            'date_add' => [
                'title' => $this->l('Data inserimento'),
                'align' => 'text-center',
                'filter_key' => 'a!date_add',
                'order_by' => true,
                'order_way' => 'DESC',
            ],
        ];

        $this->bulk_actions = [
            'delete' => [
                'text' => $this->l('Delete selected'),
                'confirm' => $this->l('Delete selected items?'),
            ],
        ];

        parent::__construct();
    }

    public function setMedia()
    {
        parent::setMedia();

        $this->addCSS($this->module->getLocalPath() . 'views/css/style.css', 'all', 1000);
    }

    public function initPageHeaderToolbar()
    {
        $this->page_header_toolbar_btn['new_document'] = [
            'href' => static::$currentIndex . '&add' . 'mpstock_document' . '&token=' . $this->token,
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
        $tpl_path = $this->module->getLocalPath() . 'views/templates/admin/AdminMpStockMovements.tpl';
        $tpl = $this->context->smarty->createTemplate($tpl_path, $this->context->smarty);
        $tpl->assign([
            'tpl_dir' => $this->module->getLocalPath() . 'views/templates/',
            'module' => $this->module->name,
            'ajax_controller' => $this->context->link->getAdminLink('AdminMpStockMovements'),
        ]);
        $this->content = $tpl->fetch();

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

    public function renderList()
    {
        $this->addRowAction('edit');
        $this->addRowAction('delete');

        return parent::renderList();
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
            ->from('mpstock_mvt_reason');
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
        $sql->select('*')
            ->from('mpstock_mvt_reason_lang')
            ->where('id_lang = ' . $id_lang)
            ->orderBy('name ASC');

        $result = Db::getInstance()->executeS($sql);
        $out = [];
        foreach ($result as $row) {
            $out[$row['id_mpstock_mvt_reason']] = $row['name'];
        }

        return $out;
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
            $out[$row['id_supplier']] = $row['name'];
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
            $out[$row['id_employee']] = $row['lastname'] . ' ' . $row['firstname'];
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
}
