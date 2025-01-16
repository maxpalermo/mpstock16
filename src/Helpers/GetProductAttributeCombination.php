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

namespace MpSoft\MpStockV2\Helpers;

class GetProductAttributeCombination
{
    public static function getProductName($id_product, $id_lang = null)
    {
        if (!$id_lang) {
            $id_lang = (int) \Context::getContext()->language->id;
        }

        $product = new \Product($id_product, false, $id_lang);
        if (!\Validate::isLoadedObject($product)) {
            return '--';
        }

        return $product->name;
    }

    public static function getCombination($id_product_attribute, $id_lang)
    {
        $combination = new \Combination($id_product_attribute, $id_lang);

        return $combination;
    }

    public static function getProductCombinations($id_product, $id_lang = null)
    {
        if (!$id_lang) {
            $id_lang = (int) \Context::getContext()->language->id;
        }

        $sql = new \DbQuery();
        $sql->select('pa.id_product_attribute as value, GROUP_CONCAT(CONCAT(agl.name, ":", al.name) SEPARATOR ", ") as label')
            ->from('product_attribute', 'pa')
            ->leftJoin('product_attribute_combination', 'pac', 'pa.id_product_attribute = pac.id_product_attribute')
            ->leftJoin('attribute', 'a', 'pac.id_attribute = a.id_attribute')
            ->leftJoin('attribute_lang', 'al', 'a.id_attribute = al.id_attribute AND al.id_lang = ' . (int) $id_lang)
            ->leftJoin('attribute_group', 'ag', 'a.id_attribute_group = ag.id_attribute_group')
            ->leftJoin('attribute_group_lang', 'agl', 'ag.id_attribute_group = agl.id_attribute_group AND agl.id_lang = ' . (int) $id_lang)
            ->where('pa.id_product = ' . $id_product)
            ->groupBy('pa.id_product_attribute')
            ->orderBy('pa.id_product_attribute ASC');

        $result = \Db::getInstance()->executeS($sql);

        return $result;
    }

    public static function getCombinationName($id_product_attribute, $with_title = false, $id_lang = null)
    {
        if (!$id_lang) {
            $id_lang = (int) \Context::getContext()->language->id;
        }

        $sql = new \DbQuery();
        if ($with_title) {
            $sql->select('pa.id_product_attribute as value, GROUP_CONCAT(CONCAT(agl.name, ":", al.name) SEPARATOR ", ") as label');
        } else {
            $sql->select('pa.id_product_attribute as value, GROUP_CONCAT(CONCAT(al.name) SEPARATOR ", ") as label');
        }
        $sql
            ->from('product_attribute', 'pa')
            ->leftJoin('product_attribute_combination', 'pac', 'pa.id_product_attribute = pac.id_product_attribute')
            ->leftJoin('attribute', 'a', 'pac.id_attribute = a.id_attribute')
            ->leftJoin('attribute_lang', 'al', 'a.id_attribute = al.id_attribute AND al.id_lang = ' . (int) $id_lang)
            ->leftJoin('attribute_group', 'ag', 'a.id_attribute_group = ag.id_attribute_group')
            ->leftJoin('attribute_group_lang', 'agl', 'ag.id_attribute_group = agl.id_attribute_group AND agl.id_lang = ' . (int) $id_lang)
            ->where('pa.id_product_attribute = ' . $id_product_attribute)
            ->groupBy('pa.id_product_attribute')
            ->orderBy('pa.id_product_attribute ASC');

        $result = \Db::getInstance()->getRow($sql);

        if ($result && !$result['label']) {
            $result['label'] = '--';
        }

        return $result;
    }
}
