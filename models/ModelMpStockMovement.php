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
    public $ref_movement;
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
        'table' => 'mpstock_product',
        'primary' => 'id_mpstock_product',
        'fields' => [
            'id_document' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'ref_movement' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'size' => 255],
            'id_warehouse' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'id_supplier' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'document_number' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'size' => 255],
            'document_date' => ['type' => self::TYPE_DATE, 'validate' => 'isDate', 'required' => true],
            'id_mpstock_mvt_reason' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'id_product' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'id_product_attribute' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'reference' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'size' => 255],
            'ean13' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'size' => 255],
            'upc' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'size' => 255],
            'price_te' => ['type' => self::TYPE_FLOAT, 'validate' => 'isFloat', 'required' => true],
            'wholesale_price_te' => ['type' => self::TYPE_FLOAT, 'validate' => 'isFloat', 'required' => true],
            'id_employee' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate', 'required' => true],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDate', 'required' => true],
            'id_order' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'id_order_detail' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'mvt_reason' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'size' => 255],
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
}
