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
class ModelMpStockMvtReasonV2 extends ObjectModel
{
    public $sign;
    public $date_add;
    public $date_upd;
    public $active;
    public $name;

    public static $definition = [
        'table' => 'mpstock_mvt_reason_v2',
        'primary' => 'id_mpstock_mvt_reason',
        'multilang' => true,
        'fields' => [
            'sign' => [
                'type' => self::TYPE_INT,
                'validate' => 'isInt',
                'required' => true,
            ],
            'active' => [
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
        $this->active = false;

        return $this->update();
    }

    public function fullDelete()
    {
        return parent::delete();
    }

    public static function getMvtReasons($id_lang = null)
    {
        $db = Db::getInstance();
        $sql = new DbQuery();

        if ($id_lang === null) {
            $id_lang = (int) Context::getContext()->language->id;
        }

        $sql->select('a.id_mpstock_mvt_reason, b.name, a.sign, a.active,a.date_add, a.date_upd')
            ->from('mpstock_mvt_reason_v2', 'a')
            ->leftJoin('mpstock_mvt_reason_v2_lang', 'b', 'a.id_mpstock_mvt_reason = b.id_mpstock_mvt_reason and b.id_lang=' . (int) $id_lang)
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

    public function isActive()
    {
        return $this->active;
    }

    public static function getActives()
    {
        $id_lang = Context::getContext()->language->id;

        return Db::getInstance()->executeS(
            'SELECT a.*, b.name FROM ' . _DB_PREFIX_ . 'mpstock_mvt_reason_v2 a '
            . 'INNER JOIN ' . _DB_PREFIX_ . 'mpstock_mvt_reason_lang b '
            . 'ON a.id_mpstock_mvt_reason = b.id_mpstock_mvt_reason AND b.id_lang = ' . (int) $id_lang . ' '
            . 'WHERE active = 1 '
            . 'ORDER BY b.name ASC'
        );
    }

    public static function getList()
    {
        $id_lang = Context::getContext()->language->id;

        return Db::getInstance()->executeS(
            'SELECT a.*, b.name FROM ' . _DB_PREFIX_ . 'mpstock_mvt_reason_v2 a '
            . 'INNER JOIN ' . _DB_PREFIX_ . 'mpstock_mvt_reason_lang b '
            . 'ON a.id_mpstock_mvt_reason = b.id_mpstock_mvt_reason AND b.id_lang = ' . (int) $id_lang . ' '
            . 'ORDER BY b.name ASC'
        );
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
            ->select('SQL_CALC_FOUND_ROWS a.id_mpstock_mvt_reason, b.name, a.sign, a.active, a.date_add, a.date_upd')
            ->from($table, 'a')
            ->innerJoin('mpstock_mvt_reason_v2_lang', 'b', 'a.id_mpstock_mvt_reason = b.id_mpstock_mvt_reason and b.id_lang = ' . (int) $id_lang);

        if ($columns) {
            foreach ($columns as $key => $column) {
                if ($column['search']['value'] != '' && $columns[$key]['searchable'] == 'true') {
                    $builder->where($column['name'] . ' LIKE "%' . $column['search']['value'] . '%"');
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
                ->orderBy('a.id_mpstock_mvt_reason ASC');
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

    public function countAllResults()
    {
        $db = Db::getInstance();
        $sql = 'SELECT COUNT(*) as total FROM ' . _DB_PREFIX_ . self::$definition['table'];
        $result = (int) $db->getValue($sql);

        return $result;
    }

    public static function updateTable()
    {
        $id_lang = Context::getContext()->language->id;
        $pfx = _DB_PREFIX_;
        $sql = "SELECT a.*, b.name FROM {$pfx}mpstock_mvt_reason a "
            . "INNER JOIN {$pfx}mpstock_mvt_reason_lang b ON a.id_mpstock_mvt_reason = b.id_mpstock_mvt_reason AND b.id_lang = " . (int) $id_lang . ' '
            . 'ORDER BY a.id_mpstock_mvt_reason ASC';
        $list = Db::getInstance()->executeS($sql);
        $errors = [];

        foreach ($list as $mvt) {
            $model = new self($mvt['id_mpstock_mvt_reason'], Context::getContext()->language->id);
            $model->sign = (int) $mvt['sign'] == 0 ? 1 : $mvt['sign'];
            $model->active = true;
            $model->date_add = $mvt['date_add'];
            $model->date_upd = $mvt['date_upd'];
            $model->name = $mvt['name'];

            try {
                if (!Validate::isLoadedObject($model)) {
                    $model->force_id = true;
                    $model->id = $mvt['id_mpstock_mvt_reason'];
                    $model->add();
                } else {
                    $model->update();
                }
            } catch (\Throwable $th) {
                $errors[] = sprintf(
                    'Errore: %s per il movimento %s',
                    $th->getMessage(),
                    $mvt['name']
                );
            }
        }

        return $errors;
    }

    public static function getSign($id_mvt)
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('sign')
            ->from('mpstock_mvt_reason')
            ->where('id_mpstock_mvt_reason=' . (int) $id_mvt);

        return (int) $db->getValue($sql);
    }
}
