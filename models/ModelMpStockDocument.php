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
class ModelMpStockDocument extends ObjectModel
{
    public $id_mpstock_document;
    public $number_document;
    public $date_document;
    public $id_mpstock_mvt_reason;
    public $id_employee;
    public $id_supplier;
    public $id_lang;
    public $id_shop;
    public $id_shop_group;
    public $date_add;
    public $date_upd;

    public static $definition = [
        'table' => 'mpstock_document',
        'primary' => 'id_mpstock_document',
        'fields' => [
            'number_document' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'size' => 255],
            'date_document' => ['type' => self::TYPE_DATE, 'validate' => 'isDate', 'required' => true],
            'id_mpstock_mvt_reason' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'id_employee' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'id_supplier' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'id_lang' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'id_shop' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'id_shop_group' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate', 'required' => true],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDate', 'required' => true],
        ],
    ];

    public function countAllResults()
    {
        $db = Db::getInstance();
        $sql = 'SELECT COUNT(*) as total FROM ' . _DB_PREFIX_ . 'mpstock_document';
        $result = (int) $db->getValue($sql);

        return $result;
    }

    public function dataTable($offset = 0, $limit = 0, $columns = null, $order = null)
    {
        $table = self::$definition['table'];
        $primary = self::$definition['primary'];
        $id_lang = (int) Context::getContext()->language->id;
        $id_shop = (int) Context::getContext()->shop->id;

        $count = $this->countAllResults();
        $filtered = false;
        $ordered = false;

        $db = Db::getInstance();
        $builder = new DbQuery();

        $builder
            ->select('SQL_CALC_FOUND_ROWS a.*,m.name as mvt_reason,CONCAT(e.firstname, " ", e.lastname) as employee, s.name as supplier, 0 as checkbox')
            ->from($table, 'a')
            ->leftJoin('mpstock_mvt_reason_lang', 'm', 'a.id_mpstock_mvt_reason = m.id_mpstock_mvt_reason and m.id_lang = ' . (int) $id_lang)
            ->leftJoin('employee', 'e', 'a.id_employee = e.id_employee')
            ->leftJoin('supplier', 's', 'a.id_supplier = s.id_supplier');

        if ($columns) {
            foreach ($columns as $key => $column) {
                if ($column['search']['value'] != '' && $columns[$key]['searchable'] == 'true') {
                    $builder->where($column['name'] . ' LIKE "%' . $column['search']['value'] . '%"');
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
                ->orderBy('id_mpstock_movement DESC');
        }

        $builder->limit($limit, $offset);

        $query = $builder->build();
        $result = $db->executeS($query);
        $filtered_rows = (int) $db->getValue('SELECT FOUND_ROWS()');

        if ($result) {
            foreach ($result as &$row) {
                $row['actions'] = '';
            }
        }

        return [
            'totalRecords' => $count,
            'totalFiltered' => $filtered ? $filtered_rows : $count,
            'data' => $result,
        ];
    }
}
