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

class AdminMpStockAvailabilityController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'stock_available';
        $this->className = 'StockAvailable';
        $this->lang = false;
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

    public function initContent()
    {
        $tpl = $this->context->smarty->createTemplate(
            $this->module->getLocalPath() . 'views/templates/admin/controllers/AdminMpStockAvailability.tpl',
            $this->context->smarty
        );

        $params = [
            'link' => $this->context->link,
        ];

        $tpl->assign($params);

        $page = $tpl->fetch();
        $this->content = $page;

        return parent::initContent();
    }

    public function ajaxProcessGetTable()
    {
        $start = (int) Tools::getValue('start');
        $length = (int) Tools::getValue('length');
        $search = Tools::getValue('search')['value'];
        $draw = (int) Tools::getValue('draw');
        $columns = Tools::getValue('columns');
        $order = Tools::getValue('order');

        $list = $this->dataTable($start, $length, $columns, $order);

        foreach ($list['data'] as &$row) {
            $row['product_name'] = ModelMpStockMovementV2::getProductNameById($row['id_product']);
            $row['product_combination'] = ModelMpStockMovementV2::getProductCombinationById($row['id_product'], $row['id_product_attribute']);
            $row['actions'] = '';
        }

        Response::json(
            [
                'draw' => $draw,
                'recordsTotal' => $list['totalRecords'],
                'recordsFiltered' => $list['totalFiltered'],
                'data' => $list['data'],
                'query' => $list['query'],
            ]
        );
    }

    public function dataTable($start, $length, $columns, $order)
    {
        $table = 'stock_available';
        $primary = 'id_stock_available';
        $id_lang = (int) Context::getContext()->language->id;
        $id_shop = (int) Context::getContext()->shop->id;

        $count = $this->countAllResults();
        $filtered = false;
        $ordered = false;

        $db = Db::getInstance();
        $builder = new DbQuery();

        $builder
            ->select('SQL_CALC_FOUND_ROWS a.id_product')
            ->select('a.id_product_attribute')
            ->select('pl.name as product_name')
            ->select('COALESCE(pa.reference, p.reference) as product_reference')
            ->select('COALESCE(pa.ean13, p.ean13) as product_ean13')
            ->select('a.quantity')
            ->select("'--' as combination")
            ->select('p.price')
            ->select('p.active')
            ->from($table, 'a')
            ->innerJoin('product', 'p', 'a.id_product = p.id_product')
            ->innerJoin('product_lang', 'pl', 'a.id_product = pl.id_product and pl.id_lang = ' . (int) $id_lang)
            ->leftJoin('product_attribute', 'pa', 'a.id_product_attribute = pa.id_product_attribute')
            ->where('a.id_product_attribute = 0');

        if ($columns) {
            foreach ($columns as $key => $column) {
                switch ($column['name']) {
                    case 'product_name':
                        $column['name'] = 'pl.name';

                        break;
                    case 'product_reference':
                        $column['name'] = 'p.reference';

                        break;
                    case 'product_ean13':
                        $column['name'] = 'p.ean13';

                        break;
                }
                if ($column['search']['value'] != '' && $columns[$key]['searchable'] == 'true') {
                    if ($column['name'] == 'a.quantity' || $column['name'] == 'p.price') {
                        if (strlen($column['search']['value']) < 3) {
                            continue;
                        }
                        $builder->where("{$column['name']} {$column['search']['value']}");
                    } elseif ($column['name'] == 'p.active') {
                        $builder->where($column['name'] . ' = ' . (int) $column['search']['value']);
                    } else {
                        $builder->where($column['name'] . ' LIKE "' . $column['search']['value'] . '"');
                    }
                    $filtered = true;

                    continue;
                }
            }
        }

        if ($order) {
            foreach ($order as $item) {
                $id_column = (int) $item['column'];
                $term = $columns[$id_column]['name'];
                $dir = $item['dir'];

                if ($columns[$id_column]['orderable'] == 'false') {
                    continue;
                }

                $builder->orderBy("{$term} {$dir}");
                $ordered = true;
            }
        }

        if (!$ordered) {
            $builder
                ->orderBy("a.{$primary} DESC");
        }

        $builder->limit($length, $start);

        $query = $builder->build();

        try {
            $result = $db->executeS($query);
            $filtered_rows = (int) $db->getValue('SELECT FOUND_ROWS()');
        } catch (PdoException $e) {
            $result = 0;
            $filtered_rows = 0;
        }

        if ($result) {
            foreach ($result as &$row) {
                $row['actions'] = '';
            }
        }

        return [
            'totalRecords' => $filtered_rows,
            'totalFiltered' => $filtered_rows,
            'data' => $result,
            'query' => str_replace("\n", ' ', (string) $query),
        ];
    }

    public function countAllResults()
    {
        $db = Db::getInstance();
        $sql = 'SELECT COUNT(*) as total FROM ' . _DB_PREFIX_ . 'stock_available';
        $result = (int) $db->getValue($sql);

        return $result;
    }

    public function ajaxProcessGetCombinationsTable()
    {
        $id_product = (int) Tools::getValue('id_product');
        $id_lang = (int) Context::getContext()->language->id;

        $db = Db::getInstance();
        $builder = new DbQuery();

        $builder
            ->select('a.id_product_attribute')
            ->select('COALESCE(pa.reference, p.reference) as reference')
            ->select('COALESCE(pa.ean13, p.ean13) as ean13')
            ->select('a.quantity')
            ->select("GROUP_CONCAT(attr.name SEPARATOR ' - ') as combination")
            ->from('stock_available', 'a')
            ->innerJoin('product', 'p', 'a.id_product = p.id_product')
            ->innerJoin('product_attribute', 'pa', 'a.id_product_attribute = pa.id_product_attribute')
            ->innerJoin('product_attribute_combination', 'pac', 'a.id_product_attribute = pac.id_product_attribute')
            ->innerJoin('attribute_lang', 'attr', 'pac.id_attribute = attr.id_attribute AND attr.id_lang = ' . (int) $id_lang)
            ->where('a.id_product_attribute != 0 AND a.id_product = ' . $id_product)
            ->groupBy('a.id_product_attribute')
            ->orderBy('combination ASC');

        $query = $builder->build();
        $result = $db->executeS($query);

        $tpl = $this->context->smarty->createTemplate(
            $this->module->getLocalPath() . 'views/templates/admin/partials/combinationsTable.tpl',
            $this->context->smarty
        );

        $params = [
            'combinations' => $result,
        ];

        $tpl->assign($params);

        $table = $tpl->fetch();
        Response::json(
            [
                'table' => $table,
            ]
        );
    }
}
