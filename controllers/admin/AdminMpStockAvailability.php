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
        $this->addJS(_MODULE_DIR_ . 'mpstockv2/views/js/plugins/swal2/swal2.js');
        $this->addJS(_MODULE_DIR_ . 'mpstockv2/views/js/plugins/htmx/htmx.js');
        $this->addCSS(_MODULE_DIR_ . 'mpstockv2/views/css/style.css');
        $this->addJqueryPlugin('autocomplete');
        $this->addJqueryUI('ui.autocomplete');
    }

    public function initPageHeaderToolbar()
    {
        $this->page_header_toolbar = true;

        $this->page_header_toolbar_btn['default_qty'] = [
            'href' => $this->context->link->getAdminLink('AdminMpStockAvailability') . '&action=update_default_qty',
            'desc' => $this->module->l('Aggiorna quantità di default', $this->controller_name),
            'icon' => 'process-icon-refresh',
        ];

        $this->page_header_toolbar_btn['align_quantities'] = [
            'href' => $this->context->link->getAdminLink('AdminMpStockAvailability') . '&action=align_quantities',
            'desc' => $this->module->l('Allinea quantità di magazzino', $this->controller_name),
            'icon' => 'process-icon-align',
        ];

        parent::initPageHeaderToolbar();
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

    public function initProcess()
    {
        parent::initProcess();
        if (Tools::getValue('update_default_on')) {
            $this->action = 'update_default_on';
        }
        if (Tools::getValue('check_default_on')) {
            $this->action = 'check_default_on';
        }
        if (Tools::getValue('action') === 'checkWrongDefault') {
            $this->action = 'checkWrongDefault';
        }
        if (Tools::getValue('action') === 'checkShouldBeDefault') {
            $this->action = 'checkShouldBeDefault';
        }
        if (Tools::getValue('action') === 'checkComparison') {
            $this->action = 'checkComparison';
        }
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
            ->select('pa.default_on')
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

    public function processUpdateDefaultQty()
    {
        $table = _DB_PREFIX_ . 'product_attribute';
        $db = Db::getInstance();
        // Azzero tutti i default_on
        $db->execute(
            "UPDATE $table SET default_on = NULL"
        );
        // Cerco la quantità maggiore per ogni prodotto
        $sql = new DbQuery();
        $sql->select('id_product, MAX(quantity) as max_quantity')
            ->from('product_attribute')
            ->groupBy('id_product');

        $sql = $sql->build();
        $results = $db->executeS($sql);

        $rows_updated = 0;

        foreach ($results as $result) {
            $sql = "UPDATE $table SET default_on=1 WHERE id_product={$result['id_product']} AND quantity = {$result['max_quantity']} LIMIT 1";
            $db->execute($sql);

            $sql_pa = "SELECT id_product_attribute FROM $table WHERE id_product={$result['id_product']} AND default_on = 1";
            $row_pa = (int) $db->getValue($sql_pa);

            $sql = "UPDATE {$table}_shop SET default_on=NULL WHERE id_product = {$result['id_product']}";
            DB::getInstance()->execute($sql);

            $sql = "UPDATE {$table}_shop SET default_on=1 WHERE id_product_attribute = {$row_pa}";
            DB::getInstance()->execute($sql);

            $affected_rows = $db->Affected_Rows();
            $rows_updated += $affected_rows;
        }

        if ($rows_updated) {
            $this->warnings[] = sprintf(
                $this->l('Aggiornate %d combinazioni di default', $this->controller_name),
                $rows_updated
            );
        } else {
            $this->confirmations[] = $this->l('Tutte le combinazioni sono corrette');
        }
    }

    public function processAlignQuantities()
    {
        $sql = 'UPDATE ps_product_attribute pa
        INNER JOIN ps_stock_available sa ON pa.id_product_attribute = sa.id_product_attribute
        SET pa.quantity = sa.quantity
        WHERE pa.quantity != sa.quantity';

        try {
            if (Db::getInstance()->execute($sql)) {
                $sql = str_replace('ps_product_attribute', 'ps_attribute_shop', $sql);
                Db::getInstance()->execute($sql);

                $affected = Db::getInstance()->Affected_Rows();
                if ($affected > 0) {
                    $this->confirmations[] = sprintf(
                        $this->module->l('Quantità allineate correttamente per %d combinazioni', $this->controller_name),
                        $affected
                    );
                } else {
                    $this->confirmations[] = $this->module->l('Tutte le quantità sono già allineate', $this->controller_name);
                }
            } else {
                $this->errors[] = $this->module->l('Si è verificato un errore durante l\'allineamento delle quantità', $this->controller_name);
            }
        } catch (Exception $e) {
            $this->errors[] = $this->module->l('Si è verificato un errore durante l\'allineamento delle quantità', $this->controller_name);
            error_log($e->getMessage());
        }
    }
}
