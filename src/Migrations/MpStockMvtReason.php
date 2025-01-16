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

class MpStockMvtReason
{
    public static function up()
    {
        $pfx = _DB_PREFIX_ ;
        $sql = "CREATE TABLE IF NOT EXISTS `{$pfx}mpstock_mvt_reason_v2` (
            `id_mpstock_mvt_reason` INT AUTO_INCREMENT PRIMARY KEY,
            `sign` TINYINT(1) NOT NULL,
            `deleted` TINYINT(1) NOT NULL,
            `date_add` DATETIME NOT NULL,
            `date_upd` TIMESTAMP NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

        $sql_lang = "CREATE TABLE IF NOT EXISTS `{$pfx}mpstock_mvt_reason_v2_lang` (
            `id_mpstock_mvt_reason` INT,
            `id_lang` INT,
            `name` VARCHAR(255) NOT NULL,
            PRIMARY KEY (`id_mpstock_mvt_reason`, `id_lang`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

        return \Db::getInstance()->execute($sql) && \Db::getInstance()->execute($sql_lang);
    }

    public static function down()
    {
        $sql = 'DROP TABLE `mpstock_mvt_reason_v2`;';
        $sql_lang = 'DROP TABLE `mpstock_mvt_reason_v2_lang`;';

        return \Db::getInstance()->execute($sql) && \Db::getInstance()->execute($sql_lang);
    }
}
