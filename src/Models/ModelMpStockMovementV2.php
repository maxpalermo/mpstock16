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
class ModelMpStockMovementV2 extends ObjectModel
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
    public $id_order;
    public $id_order_detail;
    public $mvt_reason;
    public $stock_quantity_before;
    public $stock_movement;
    public $stock_quantity_after;
    public $date_add;
    public $date_upd;

    public static $definition = [
        'table' => 'mpstock_movement_v2',
        'primary' => 'id_mpstock_movement',
        'fields' => [
            'id_document' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => false],
            'id_warehouse' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => false],
            'id_supplier' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => false],
            'document_number' => ['type' => self::TYPE_STRING, 'validate' => 'isAnything', 'required' => false, 'size' => 255],
            'document_date' => ['type' => self::TYPE_DATE, 'validate' => 'isDate', 'required' => false],
            'id_mpstock_mvt_reason' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'mvt_reason' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'size' => 255],
            'id_product' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'id_product_attribute' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => false],
            'reference' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'size' => 255],
            'ean13' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'size' => 255],
            'upc' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'size' => 255],
            'price_te' => ['type' => self::TYPE_FLOAT, 'validate' => 'isFloat', 'required' => false],
            'wholesale_price_te' => ['type' => self::TYPE_FLOAT, 'validate' => 'isFloat', 'required' => false],
            'id_employee' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => false],
            'id_order' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => false],
            'id_order_detail' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => false],
            'stock_quantity_before' => ['type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => true],
            'stock_movement' => ['type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => true],
            'stock_quantity_after' => ['type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => true],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate', 'required' => true],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDate', 'required' => false],
        ],
    ];

    public static function getMovementsByIdDocument($id_document)
    {
        $db = Db::getInstance();
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'mpstock_movement_v2 WHERE id_document = ' . (int) $id_document;
        $result = $db->executeS($sql);

        if ($result) {
            foreach ($result as &$row) {
                $row['mvt_reason'] = ModelMpStockMovementV2::getMvtReasonById($row['id_mpstock_mvt_reason']);
                $row['product_name'] = ModelMpStockMovementV2::getProductNameById($row['id_product']);
                $row['product_combination'] = ModelMpStockMovementV2::getProductCombinationById($row['id_product'], $row['id_product_attribute']);
                $row['employee'] = ModelMpStockMovementV2::getEmployeeById($row['id_employee']);
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
        $sql = 'SELECT COUNT(*) as total FROM ' . _DB_PREFIX_ . 'mpstock_movement_v2';
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
            ->select('o.reference as order_reference')
            ->select('pl.name as product_name')
            ->select('CONCAT(e.firstname, " ", e.lastname) as employee')
            ->select('s.name as supplier, 0 as checkbox')
            ->from($table, 'a')
            ->leftJoin('orders', 'o', 'a.id_order = o.id_order')
            ->leftJoin('product_lang', 'pl', 'a.id_product = pl.id_product and pl.id_lang = ' . (int) $id_lang)
            ->leftJoin('employee', 'e', 'a.id_employee = e.id_employee')
            ->leftJoin('supplier', 's', 'a.id_supplier = s.id_supplier');

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

    public static function getCommons($id_product_attribute)
    {
        $output = [
            'ean13' => '',
            'upc' => '',
            'reference' => '',
            'id_supplier' => 0,
            'price_te' => 0,
            'wholesale_price_te' => 0,
            'tax_rate' => 0,
            'stock_quantity_before' => 0,
        ];
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('pa.ean13 as pa_ean13')
            ->select('pa.upc as pa_upc')
            ->select('pa.reference as pa_reference')
            ->select('pa.id_product as id_other_product')
            ->select('p.ean13 as p_ean13')
            ->select('p.upc as p_upc')
            ->select('p.reference as p_reference')
            ->select('p.id_supplier')
            ->select('p.price')
            ->select('p.wholesale_price')
            ->select('sa.quantity as stock_quantity_before')
            ->from('product_attribute', 'pa')
            ->innerJoin('product', 'p', 'pa.id_product=p.id_product')
            ->innerJoin('stock_available', 'sa', 'sa.id_product_attribute=' . (int) $id_product_attribute)
            ->where('pa.id_product_attribute=' . (int) $id_product_attribute);
        $result = $db->getRow($sql);

        if ($result) {
            $output['ean13'] = !empty($result['pa_ean13']) ? $result['pa_ean13'] : $result['p_ean13'];
            $output['upc'] = !empty($result['pa_upc']) ? $result['pa_upc'] : $result['p_upc'];
            $output['reference'] = !empty($result['pa_reference']) ? $result['pa_reference'] : $result['p_reference'];
            $output['id_supplier'] = !empty($result['id_supplier']) ? $result['id_supplier'] : 0;
            $output['price_te'] = $result['price'];
            $output['wholesale_price_te'] = $result['wholesale_price'];
            $output['stock_quantity_before'] = (int) $result['stock_quantity_before'];
        }

        return $output;
    }

    public static function truncate()
    {
        $pfx = _DB_PREFIX_;
        $sql = "TRUNCATE TABLE {$pfx}mpstock_movement_v2";

        return Db::getInstance()->execute($sql);
    }

    public static function updateTable()
    {
        self::truncate();
        $affected_rows = 0;
        $errors = [];
        $pfx = _DB_PREFIX_;
        $id_lang = (int) Context::getContext()->language->id;

        $fields = [
            'id_document',
            'id_warehouse',
            'id_supplier',
            'document_number',
            'document_date',
            'id_mpstock_mvt_reason',
            'mvt_reason',
            'id_product',
            'id_product_attribute',
            'reference',
            'ean13',
            'upc',
            'price_te',
            'wholesale_price_te',
            'id_employee',
            'id_order',
            'id_order_detail',
            'stock_quantity_before',
            'stock_movement',
            'stock_quantity_after',
            'date_add',
            'date_upd',
        ];
        $fields_list = implode(',', $fields);

        $import_fields_from_movements = [
            'a.id_document',
            'a.id_warehouse',
            'd.id_supplier as id_supplier',
            'd.number_document as document_number',
            'd.date_document as document_date',
            'a.id_mpstock_mvt_reason',
            'mvr.name as mvt_reason',
            'a.id_product',
            'a.id_product_attribute',
            'COALESCE(pa.reference, p.reference) as reference', // for combination products
            'a.ean13',
            'a.upc',
            'a.price_te',
            'a.wholesale_price_te',
            'a.id_employee',
            'NULL as id_order',
            'NULL as id_order_detail',
            'a.physical_quantity as stock_quantity_before',
            'a.usable_quantity as stock_movement',
            'a.usable_quantity + a.physical_quantity as stock_quantity_after',
            'a.date_add',
            'a.date_upd',
        ];
        $import_fields_from_movements_list = implode(',', $import_fields_from_movements);

        $sql = "
            INSERT INTO `{$pfx}mpstock_movement_v2` ($fields_list)
            SELECT 
                {$import_fields_from_movements_list}
            FROM 
                `{$pfx}mpstock_product` a
            LEFT JOIN 
                `{$pfx}mpstock_document` d ON a.id_document = d.id_mpstock_document
            LEFT JOIN 
                `{$pfx}product_attribute` pa ON a.id_product_attribute = pa.id_product_attribute
            LEFT JOIN 
                `{$pfx}product` p ON a.id_product = p.id_product
            LEFT JOIN 
                `{$pfx}mpstock_mvt_reason_lang` mvr ON a.id_mpstock_mvt_reason = mvr.id_mpstock_mvt_reason AND mvr.id_lang = {$id_lang}
        ";
        $errors = [];

        $query_result = self::execSql($sql);

        if ($query_result['rows_affected'] > 0) {
            $affected_rows += $query_result['rows_affected'];
            $errors = array_merge($errors, $query_result['errors']);
        }

        $id_mvt = (int) Configuration::get(MpStockV2::MPSTOCK_DEFAULT_UNLOAD_MVT_ID);
        $name_mvt = '';
        $mvt = new ModelMpStockMvtReasonV2($id_mvt, $id_lang);
        if (Validate::isLoadedObject($mvt)) {
            $name_mvt = $mvt->name;
        } else {
            $name_mvt = 'Nessun movimento';
        }

        $import_fields_from_details = [
            'NULL as id_document', // No document for details, only for movments.
            'NULL as id_warehouse', // No warehouse for details, only for movments.
            'NULL as id_supplier',
            'CONCAT("ADD-", o.id_order) as document_number', // No document number for details, only for movments.
            'o.date_add as document_date',
            "$id_mvt as id_mpstock_mvt_reason",
            "'$name_mvt' as mvt_reason",
            'a.product_id',
            'a.product_attribute_id',
            'a.product_reference as reference',
            'a.product_ean13',
            'a.product_upc',
            'a.product_price as price_te',
            'NULL as wholesale_price_te',
            'NULL as id_employee',
            'o.id_order as id_order',
            'a.id_order_detail as id_order_detail',
            'CAST(a.product_quantity_in_stock as signed) as stock_quantity_before',
            'CAST(a.product_quantity as signed) * -1 as stock_movement',
            'CAST(a.product_quantity_in_stock as signed) - CAST(a.product_quantity as signed) as stock_quantity_after',
            'o.date_add',
            'o.date_upd',
        ];
        $import_fields_from_details_list = implode(',', $import_fields_from_details);

        $id_lang = (int) Context::getContext()->language->id;
        $sql = "
            INSERT INTO `{$pfx}mpstock_movement_v2` ($fields_list)
            SELECT 
                {$import_fields_from_details_list}
            FROM 
                `{$pfx}order_detail` a
            INNER JOIN 
                `{$pfx}orders` o ON a.id_order = o.id_order
            WHERE o.id_order IS NOT NULL
        ";
        $query_result = self::execSql($sql);

        if ($query_result['rows_affected'] > 0) {
            $affected_rows += $query_result['rows_affected'];
            $errors = array_merge($errors, $query_result['errors']);
        }

        Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'mpstock_movement_v2_tmp`');

        $sql = "
            CREATE TABLE `{$pfx}mpstock_movement_v2_tmp` AS
            SELECT * FROM `{$pfx}mpstock_movement_v2` ORDER BY date_add ASC
        ";

        $query_result = self::execSql($sql);

        $errors = array_merge($errors, $query_result['errors']);

        $sql = "
            INSERT INTO `{$pfx}mpstock_movement_v2`
            SELECT * FROM `{$pfx}mpstock_movement_v2_tmp`
            ORDER BY date_add ASC
        ";

        $query_result = self::execSql($sql);
        $errors = array_merge($errors, $query_result['errors']);

        Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'mpstock_movement_v2_tmp`');

        return [
            'errors' => $errors,
            'rows_affected' => $affected_rows,
        ];
    }

    protected static function execSql($sql)
    {
        $errors = [];

        try {
            $result = Db::getInstance()->execute($sql);
        } catch (\Throwable $th) {
            $result = false;
            $errors[] = $th->getMessage() . "\n" . $th->getLine() . "\n" . $th->getFile();
        }

        if ($result) {
            $affected_rows = \Db::getinstance()->Affected_Rows();
        }

        return [
            'errors' => $errors,
            'rows_affected' => $result ? $affected_rows : 0,
        ];
    }

    public static function hydrateMovement($orderDetailId)
    {
        $orderDetail = new OrderDetail($orderDetailId);
        if (!Validate::isLoadedObject($orderDetail)) {
            return false;
        }

        $movement = self::getMovementByIdOrderDetail($orderDetailId);
        if (!Validate::isLoadedObject($movement)) {
            return false;
        }
    }

    public static function getMovementByIdOrderDetail($orderDetailId)
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select(self::$definition['primary'])
            ->from('mpstock_movement_v2')
            ->where('id_order_detail = ' . (int) $orderDetailId);
        $result = (int) $db->getValue($sql);

        if ($result) {
            return new ModelMpStockMovementV2($result);
        }

        return false;
    }

    public function hydrateFromOrderDetail($orderDetail, $refund = false)
    {
        $mvtId = (int) Configuration::get(\MpStockV2::MPSTOCK_DEFAULT_UNLOAD_MVT_ID);
        $mvt = new ModelMpStockMvtReasonV2($mvtId, (int) Context::getContext()->language->id);
        if (Validate::isLoadedObject($mvt)) {
            $this->id_mpstock_mvt_reason = $mvt->id;
            $this->mvt_reason = $mvt->name;
        } else {
            return false;
        }

        $order = new Order($orderDetail->id_order);
        if (!Validate::isLoadedObject($order)) {
            return false;
        }

        if ($refund) {
            $sign = 1;
        } else {
            $sign = -1;
        }

        $stock_mvt = (int) $orderDetail->product_quantity * $sign;
        $stock_before = (int) $orderDetail->product_quantity_in_stock;
        $stock_after = $stock_before + $stock_mvt;

        $this->id_document = null;
        $this->id_warehouse = null;
        $this->id_supplier = null;
        $this->document_number = $order->id;
        $this->document_date = $order->date_add;
        $this->id_product = (int) $orderDetail->product_id;
        $this->id_product_attribute = (int) $orderDetail->product_attribute_id;
        $this->reference = $orderDetail->product_reference;
        $this->ean13 = $orderDetail->product_ean13;
        $this->upc = $orderDetail->product_upc;
        $this->price_te = (float) $orderDetail->unit_price_tax_excl;
        $this->wholesale_price_te = null;
        $this->id_employee = null;
        $this->id_order = (int) $orderDetail->id_order;
        $this->id_order_detail = $orderDetail->id_order_detail;
        $this->stock_quantity_before = (int) $stock_before;
        $this->stock_movement = (int) $stock_mvt;
        $this->stock_quantity_after = (int) $stock_after;
        $this->date_add = date('Y-m-d H:i:s');
        $this->date_upd = date('Y-m-d H:i:s');

        return $this;
    }
}
