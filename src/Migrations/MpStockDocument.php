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

class MpStockDocument
{
    public static function up()
    {
        $pfx = _DB_PREFIX_;
        $sql = "CREATE TABLE IF NOT EXISTS `{$pfx}mpstock_document_v2` (
            `id_mpstock_document` INT AUTO_INCREMENT PRIMARY KEY,
            `id_shop` INT UNSIGNED,
            `number_document` VARCHAR(255),
            `date_document` DATE,
            `id_mpstock_mvt_reason` INT UNSIGNED NOT NULL,
            `id_supplier` INT UNSIGNED,
            `tot_qty` INT,
            `tot_document_te` DECIMAL(20,6),
            `tot_document_taxes` DECIMAL(20,6),
            `tot_document_ti` DECIMAL(20,6),
            `id_employee` INT UNSIGNED NOT NULL,
            `date_add` DATETIME NOT NULL,
            `date_upd` DATETIME
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

        return \Db::getInstance()->execute($sql);
    }

    public static function down()
    {
        $pfx = _DB_PREFIX_;
        $sql = "DROP TABLE IF EXISTS `{$pfx}mpstock_document_v2`;";

        return \Db::getInstance()->execute($sql);
    }
}
