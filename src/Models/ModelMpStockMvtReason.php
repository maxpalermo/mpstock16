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
class ModelMpStockMvtReason extends ObjectModel
{
    public $sign;
    public $date_add;
    public $date_upd;
    public $deleted;
    public $name;

    public static $definition = [
        'table' => 'mpstock_mvt_reason',
        'primary' => 'id_mpstock_mvt_reason',
        'multilang' => true,
        'fields' => [
            'sign' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => true,
            ],
            'date_add' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'datetime' => true,
                'required' => true,
            ],
            'date_upd' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'timestamp' => true,
                'required' => false,
            ],
            'deleted' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => true,
            ],
            // Multilang fields
            'name' => [
                'lang' => true,
                'type' => self::TYPE_STRING,
                'size' => 255,
                'validate' => 'isString',
                'required' => true,
            ],
        ],
    ];

    public function delete()
    {
        $this->deleted = true;

        return $this->update();
    }

    public static function getMvtReasons($id_lang = null)
    {
        $db = Db::getInstance();
        $sql = new DbQuery();

        if ($id_lang === null) {
            $id_lang = (int) Context::getContext()->language->id;
        }

        $sql->select('a.id_mpstock_mvt_reason, a.sign, b.name')
            ->from('mpstock_mvt_reason', 'a')
            ->leftJoin('mpstock_mvt_reason_lang', 'b', 'a.id_mpstock_mvt_reason = b.id_mpstock_mvt_reason and b.id_lang=' . (int) $id_lang)
            ->orderBy('b.name ASC');
        $result = $db->executeS($sql);

        if (!$result) {
            return [];
        }

        return $result;
    }

    public static function getMovementType($id)
    {
        $model = new self($id, Context::getContext()->language->id);
        if ($model->id) {
            return $model->name;
        }

        return '--';
    }

    public static function getName($id)
    {
        return self::getMovementType($id);
    }
}