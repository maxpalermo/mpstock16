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
    public $id_mpstock_product;
    public $id_warehouse;
    public $id_document;
    public $id_order;
    public $id_order_detail;
    public $id_mpstock_mvt_reason;
    public $id_product;
    public $id_product_attribute;
    public $reference;
    public $ean13;
    public $upc;
    public $physical_quantity;
    public $usable_quantity;
    public $price_te;
    public $wholesale_price_te;
    public $id_employee;
    public $date_add;
    public $date_upd;

    public static $definition = [
        'table' => 'mpstock_product',
        'primary' => 'id_mpstock_product',
        'fields' => [
            'id_warehouse' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'id_document' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => false],
            'id_order' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => false],
            'id_order_detail' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => false],
            'id_mpstock_mvt_reason' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'id_product' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'id_product_attribute' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'reference' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'size' => 255],
            'ean13' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'size' => 13],
            'upc' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'size' => 12],
            'physical_quantity' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'usable_quantity' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'price_te' => ['type' => self::TYPE_FLOAT, 'validate' => 'isPrice', 'required' => true],
            'wholesale_price_te' => ['type' => self::TYPE_FLOAT, 'validate' => 'isPrice', 'required' => false],
            'id_employee' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate', 'required' => false],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDate', 'required' => false],
        ],
    ];
}
