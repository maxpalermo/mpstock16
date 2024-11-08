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
class AdminMpStockDocumentsController extends ModuleAdminController
{
    protected $mvtReasons;
    protected $suppliers;
    protected $employees;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'mpstock_document';
        $this->identifier = 'id_mpstock_document';
        $this->className = 'ModelMpStockDocument';
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

        $this->fields_list = [
            'id_mpstock_document' => [
                'title' => $this->l('ID'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ],
            'number_document' => [
                'title' => $this->l('Numero'),
                'filter_key' => 'a!number_document',
            ],
            'date_document' => [
                'title' => $this->l('Data'),
                'filter_key' => 'a!date_document',
            ],
            'id_mpstock_mvt_reason' => [
                'title' => $this->l('Movimento'),
                'type' => 'select',
                'list' => $this->mvtReasons,
                'filter_key' => 'a!id_mpstock_mvt_reason',
                'callback' => 'getMvtReasonName',
            ],
            'id_supplier' => [
                'title' => $this->l('Fornitore'),
                'type' => 'select',
                'list' => $this->getSuppliers(),
                'filter_key' => 'a!id_supplier',
                'callback' => 'getSupplierName',
            ],
            'tot_document_ti' => [
                'title' => $this->l('Totale'),
                'type' => 'price',
                'align' => 'text-right',
                'class' => 'fixed-width-md',
            ],
            'id_employee' => [
                'title' => $this->l('Operatore'),
                'type' => 'select',
                'list' => $this->getEmployees(),
                'filter_key' => 'a!id_employee',
                'callback' => 'getEmployeeName',
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
}
