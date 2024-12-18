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

namespace MpSoft\MpStock\Helpers;

class ImportOrdersDetails
{
    protected $module;
    protected $context;
    protected $id_lang;
    protected $controller;

    public const MOVEMENT_WEB_SELL = 113;

    public function __construct()
    {
        /** @var \Context */
        $this->context = \Context::getContext();
        /** @var \Module */
        $this->module = \Module::getInstanceByName('mpstock');
        /** @var int */
        $this->id_lang = $this->context->language->id;
        /** @var \ModuleAdminController */
        $this->controller = $this->context->controller;
    }

    public function getOrdersDetails()
    {
        $db = \Db::getInstance();

        $sub = new \DbQuery();
        $sub->select('id_order_detail')
            ->from('mpstock_product')
            ->where('id_order_detail>0');
        $sub = '(' . $sub->build() . ')';

        $sql_count = new \DbQuery();
        $sql_count->select('COUNT(*)')
            ->from('order_detail', 'od')
            ->innerJoin('orders', 'o', 'o.id_order = od.id_order')
            ->where('od.id_order_detail NOT IN ' . $sub);
        $total = (int) $db->getValue($sql_count);

        $sql = new \DbQuery();
        $sql->select('od.*, o.date_add, o.date_upd')
            ->from('order_detail', 'od')
            ->innerJoin('orders', 'o', 'o.id_order = od.id_order')
            ->where('od.id_order_detail NOT IN ' . $sub)
            ->limit(1000);

        $sql = $sql->build();

        $ordersDetails = $db->executeS($sql);

        return [
            'total' => $total,
            'ordersDetails' => $ordersDetails,
        ];
    }

    public function importOrdersDetails($data)
    {
        $result = [];
        foreach ($data as $orderDetail) {
            $result = array_merge($result, $this->importMovements($orderDetail));
        }

        return $result;
    }

    public function importMovements($orderDetail)
    {
        $success = [];
        $errors = [];

        $record = $this->hydrate($orderDetail, self::MOVEMENT_WEB_SELL);
        $model = new \ModelMpStockMovement();
        $model->hydrate($record);

        try {
            $model->add(false, true);
            $success[] = $model->id_order_detail;
        } catch (\Exception $e) {
            $errors[] = $e->getMessage();
        }

        return [
            'success' => $success,
            'errors' => $errors,
        ];
    }

    public function hydrate($orderDetail, $id_mvt)
    {
        $record = [
            'id_warehouse' => (int) $orderDetail['id_warehouse'],
            'id_document' => null,
            'id_order' => (int) $orderDetail['id_order'],
            'id_order_detail' => $orderDetail['id_order_detail'],
            'id_mpstock_mvt_reason' => $id_mvt,
            'id_product' => (int) $orderDetail['product_id'],
            'id_product_attribute' => (int) $orderDetail['product_attribute_id'],
            'reference' => $orderDetail['product_reference'],
            'ean13' => $orderDetail['product_ean13'],
            'upc' => $orderDetail['product_upc'],
            'physical_quantity' => (int) $orderDetail['product_quantity_in_stock'],
            'usable_quantity' => (int) $orderDetail['product_quantity'],
            'price_te' => (float) $orderDetail['unit_price_tax_excl'],
            'wholesale_price_te' => null,
            'id_employee' => (int) $this->context->employee->id,
            'date_add' => $orderDetail['date_add'],
            'date_upd' => $orderDetail['date_upd'],
        ];

        return $record;
    }
}