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

namespace MpSoft\MpStockV2\Migrations;

class MpStockMovement
{
    public static function up()
    {
        $pfx = _DB_PREFIX_;
        $sql = "CREATE TABLE IF NOT EXISTS `{$pfx}mpstock_movement_v2` (
            `id_mpstock_movement` INT AUTO_INCREMENT PRIMARY KEY,
            `id_document` INT UNSIGNED NULL,
            `id_warehouse` INT UNSIGNED NULL,
            `id_supplier` INT UNSIGNED NULL,
            `document_number` VARCHAR(255),
            `document_date` DATE,
            `id_mpstock_mvt_reason` INT UNSIGNED NOT NULL,
            `mvt_reason` VARCHAR(255) NOT NULL,
            `id_product` INT UNSIGNED NOT NULL,
            `id_product_attribute` INT UNSIGNED NULL,
            `reference` VARCHAR(255) NULL,
            `ean13` VARCHAR(255) NULL,
            `upc` VARCHAR(255) NULL,
            `price_te` DECIMAL(20,6),
            `wholesale_price_te` DECIMAL(20,6) NULL,
            `id_employee` INT UNSIGNED NULL,
            `id_order` INT UNSIGNED NULL,
            `id_order_detail` INT UNSIGNED NULL,
            `stock_quantity_before` INT NOT NULL,
            `stock_movement` INT NOT NULL,
            `stock_quantity_after` INT NOT NULL,
            `date_add` DATETIME NOT NULL,
            `date_upd` DATETIME NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

        return \Db::getInstance()->execute($sql);
    }

    public static function down()
    {
        $pfx = _DB_PREFIX_;
        $sql = "DROP TABLE IF EXISTS `{$pfx}mpstock_movement_v2`;";

        return \Db::getInstance()->execute($sql);
    }
}
