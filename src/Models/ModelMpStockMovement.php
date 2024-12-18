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
class ModelMpStockMovement extends ObjectModel
{
    public $id_mpstock_movement;
    public $id_document;
    public $id_warehouse;
    public $id_supplier;
    public $document_number;
    public $document_date;
    public $id_mpstock_mvt_reason;
    public $id_product;
    public $id_product_attribute;
    public $reference;
    public $ean13;
    public $upc;
    public $price_te;
    public $wholesale_price_te;
    public $id_employee;
    public $date_add;
    public $date_upd;
    public $id_order;
    public $id_order_detail;
    public $mvt_reason;
    public $stock_quantity_before;
    public $stock_movement;
    public $stock_quantity_after;

    public static $definition = [
        'table' => 'mpstock_movement',
        'primary' => 'id_mpstock_movement',
        'fields' => [
            'id_document' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'id_warehouse' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => false],
            'id_supplier' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'document_number' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'size' => 255],
            'document_date' => ['type' => self::TYPE_DATE, 'validate' => 'isDate', 'required' => false],
            'id_mpstock_mvt_reason' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'mvt_reason' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'size' => 255],
            'id_product' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'id_product_attribute' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'reference' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'size' => 255],
            'ean13' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'size' => 255],
            'upc' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'size' => 255],
            'price_te' => ['type' => self::TYPE_FLOAT, 'validate' => 'isFloat', 'required' => true],
            'wholesale_price_te' => ['type' => self::TYPE_FLOAT, 'validate' => 'isFloat', 'required' => false],
            'id_employee' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate', 'required' => true],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDate', 'required' => true],
            'id_order' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => false],
            'id_order_detail' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => false],
            'stock_quantity_before' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'stock_movement' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'stock_quantity_after' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
        ],
    ];

    public static function getMovementsByIdDocument($id_document)
    {
        $db = Db::getInstance();
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'mpstock_movement WHERE id_document = ' . (int) $id_document;
        $result = $db->executeS($sql);

        if ($result) {
            foreach ($result as &$row) {
                $row['mvt_reason'] = ModelMpStockMovement::getMvtReasonById($row['id_mpstock_mvt_reason']);
                $row['product_name'] = ModelMpStockMovement::getProductNameById($row['id_product']);
                $row['product_combination'] = ModelMpStockMovement::getProductCombinationById($row['id_product'], $row['id_product_attribute']);
                $row['employee'] = ModelMpStockMovement::getEmployeeById($row['id_employee']);
            }
        }

        return $result;
    }

    public static function getMvtReasonById($id_mpstock_mvt_reason)
    {
        $id_lang = (int) Context::getContext()->language->id;
        $sql = new DbQuery();
        $sql->select('name')
            ->from('mpstock_mvt_reason_lang')
            ->where('id_mpstock_mvt_reason = ' . (int) $id_mpstock_mvt_reason . ' AND id_lang = ' . (int) $id_lang);

        $result = Db::getInstance()->getValue($sql);

        return $result;
    }

    public static function getProductNameById($id_product)
    {
        $id_lang = (int) Context::getContext()->language->id;
        $sql = new DbQuery();
        $sql->select('name')
            ->from('product_lang')
            ->where('id_product = ' . (int) $id_product . ' AND id_lang = ' . (int) $id_lang);

        $result = Db::getInstance()->getValue($sql);

        return $result;
    }

    public static function getProductCombinationById($id_product, $id_product_attribute)
    {
        $id_lang = (int) Context::getContext()->language->id;
        $sql = new DbQuery();
        $sql->select('group_concat(DISTINCT al.name SEPARATOR " - ") as combination')
            ->from('product_attribute_combination', 'pac')
            ->leftJoin('attribute_lang', 'al', 'pac.id_attribute = al.id_attribute AND al.id_lang = ' . (int) $id_lang)
            ->where('pac.id_product_attribute = ' . (int) $id_product_attribute);

        $result = Db::getInstance()->getValue($sql);

        return $result;
    }

    public static function getEmployeeById($id_employee)
    {
        $id_lang = (int) Context::getContext()->language->id;
        $sql = new DbQuery();
        $sql->select('CONCAT(e.firstname, " ", e.lastname) as employee')
            ->from('employee', 'e')
            ->where('e.id_employee = ' . (int) $id_employee);

        $result = Db::getInstance()->getValue($sql);

        return $result;
    }

    public function countAllResults()
    {
        $db = Db::getInstance();
        $sql = 'SELECT COUNT(*) as total FROM ' . _DB_PREFIX_ . 'mpstock_movement';
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
            ->select('SQL_CALC_FOUND_ROWS a.*')
            ->select('d.number_document,d.date_document,d.id_supplier')
            ->select('o.reference as order_reference')
            ->select('pl.name as product_name')
            ->select('m.name as mvt_reason')
            ->select('CONCAT(e.firstname, " ", e.lastname) as employee')
            ->select('s.name as supplier, 0 as checkbox')
            ->from($table, 'a')
            ->leftJoin('orders', 'o', 'a.id_order = o.id_order')
            ->leftJoin('product_lang', 'pl', 'a.id_product = pl.id_product and pl.id_lang = ' . (int) $id_lang)
            ->leftJoin('mpstock_document', 'd', 'a.id_document = d.id_mpstock_document')
            ->leftJoin('mpstock_mvt_reason_lang', 'm', 'a.id_mpstock_mvt_reason = m.id_mpstock_mvt_reason and m.id_lang = ' . (int) $id_lang)
            ->leftJoin('employee', 'e', 'a.id_employee = e.id_employee')
            ->leftJoin('supplier', 's', 'd.id_supplier = s.id_supplier');

        if ($columns) {
            foreach ($columns as $key => $column) {
                if ($column['search']['value'] != '' && $columns[$key]['searchable'] == 'true') {
                    $builder->where($column['name'] . ' LIKE "' . $column['search']['value'] . '"');
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
                ->orderBy('a.id_mpstock_movement DESC');
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