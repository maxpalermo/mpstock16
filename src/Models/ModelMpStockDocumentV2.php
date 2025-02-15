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

use MpSoft\MpStock\Helpers\ParseXml;

class ModelMpStockDocumentV2 extends ObjectModel
{
    public $id_shop;
    public $number_document;
    public $date_document;
    public $id_mpstock_mvt_reason;
    public $id_supplier;
    public $tot_qty;
    public $tot_document_te;
    public $tot_document_taxes;
    public $tot_document_ti;
    public $id_employee;
    public $date_add;
    public $date_upd;

    public static $definition = [
        'table' => 'mpstock_document_v2',
        'primary' => 'id_mpstock_document',
        'fields' => [
            'id_shop' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => false],
            'number_document' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'size' => 255],
            'date_document' => ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => false],
            'id_mpstock_mvt_reason' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'id_supplier' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => false],
            'tot_qty' => ['type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => false],
            'tot_document_te' => ['type' => self::TYPE_FLOAT, 'validate' => 'isFloat', 'required' => false],
            'tot_document_taxes' => ['type' => self::TYPE_FLOAT, 'validate' => 'isFloat', 'required' => false],
            'tot_document_ti' => ['type' => self::TYPE_FLOAT, 'validate' => 'isFloat', 'required' => false],
            'id_employee' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate', 'required' => true],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDate', 'required' => false],
        ],
    ];

    public function countAllResults()
    {
        $db = Db::getInstance();
        $sql = 'SELECT COUNT(*) as total FROM ' . _DB_PREFIX_ . 'mpstock_document_v2';
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
            ->select('SQL_CALC_FOUND_ROWS a.*,m.name as mvt_reason,CONCAT(e.firstname, " ", e.lastname) as employee, s.name as supplier, 0 as checkbox')
            ->from($table, 'a')
            ->leftJoin('mpstock_mvt_reason_lang', 'm', 'a.id_mpstock_mvt_reason = m.id_mpstock_mvt_reason and m.id_lang = ' . (int) $id_lang)
            ->leftJoin('employee', 'e', 'a.id_employee = e.id_employee')
            ->leftJoin('supplier', 's', 'a.id_supplier = s.id_supplier');

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
                ->orderBy('id_mpstock_movement DESC');
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

    /**
     * Summary of createImportDocument
     *
     * @param ParseXml $parser
     * @param int $mvtReasonId
     *
     * @return bool|string true on success, error message on failure
     */
    public static function createImportDocument($parser, $mvtReasonId)
    {
        $db = Db::getInstance();
        $id_lang = (int) Context::getContext()->language->id;

        if (!(int) $mvtReasonId) {
            return 'Scegli un tipo di movimento';
        }

        $mvtReason = new ModelMpStockMvtReasonV2($mvtReasonId, $id_lang);
        if (!Validate::isLoadedObject($mvtReason)) {
            return 'Tipo di movimento non valido';
        }

        $employeeId = (int) Context::getContext()->employee->id;

        $rows = $parser->getDocumentContent();
        if (!$rows) {
            return 'Non sono presenti righe da importare';
        }

        $number_document = $parser->getDocumentNumber();
        $date_document = $parser->getDocumentDate();

        $sql = new DbQuery();
        $sql->select('id_mpstock_document')
            ->from('mpstock_document_v2')
            ->where('number_document = "' . pSQL($number_document) . '"')
            ->where('date_document = "' . pSQL($date_document) . '"');
        $id_document = (int) $db->getValue($sql);

        if ($id_document) {
            return sprintf(
                'Esiste già un documento con questo numero e data: %s - %s',
                $id_document,
                $date_document
            );
        }

        $doc = new ModelMpStockDocumentV2(null, $id_lang);
        $data = [
            'id_shop' => 0,
            'number_document' => $number_document,
            'date_document' => $date_document,
            'id_mpstock_mvt_reason' => (int) $mvtReasonId,
            'id_supplier' => 0,
            'tot_qty' => 0,
            'tot_document_te' => 0,
            'tot_document_taxes' => 0,
            'tot_document_ti' => 0,
            'id_employee' => $employeeId,
            'date_add' => date('Y-m-d H:i:s'),
            'date_upd' => null,
        ];
        $doc->hydrate($data);

        try {
            $res = $doc->add();
        } catch (\Throwable $th) {
            return $th->getMessage() . "\n" . $th->getLine() . "\n" . $th->getFile();
        }

        $processed = 0;
        $skipped = 0;
        $errors = [];

        foreach ($rows as $line => $row) {
            $ean13 = trim($row['ean13']);
            if ($ean13 === '') {
                $skipped++;
                $errors[] = "Riga {$line} non importata. EAN13 mancante";

                continue;
            }

            $sql = new DbQuery();
            $sql->select('a.id_product, a.id_product_attribute, b.reference')
                ->from('product_attribute', 'a')
                ->innerJoin('product', 'b', 'b.id_product=a.id_product')
                ->where("a.ean13 = '" . pSQL($ean13) . "'");
            $prod = $db->getRow($sql);

            if (!$prod) {
                $skipped++;
                $processed++;
                $errors[] = "Riga {$line} non importata. Prodotto con EAN13 {$ean13} non trovato";

                continue;
            }

            $sql = new DbQuery();
            $sql->select('id_supplier')
                ->from('product_supplier')
                ->where("id_product = '" . (int) $prod['id_product'] . "'")
                ->where("id_product_attribute = '" . (int) $prod['id_product_attribute'] . "'");
            $id_supplier = (int) $db->getValue($sql);

            $sign = $mvtReason->sign == 0 ? 1 : -1;
            $stockQtyBefore = (int) StockAvailable::getQuantityAvailableByProduct($prod['id_product'], $prod['id_product_attribute']);
            $stockMvt = (int) $row['qty'] * $sign;
            $stockQtyAfter = $stockQtyBefore + $stockMvt;

            $data_row = [
                'id_document' => $doc->id,
                'ref_movement' => $mvtReason->name,
                'id_warehouse' => 0,
                'id_supplier' => $id_supplier,
                'document_number' => $doc->number_document,
                'document_date' => $doc->date_document,
                'id_mpstock_mvt_reason' => (int) $mvtReasonId,
                'id_product' => (int) $prod['id_product'],
                'id_product_attribute' => (int) $prod['id_product_attribute'],
                'reference' => (int) $prod['reference'],
                'ean13' => $ean13,
                'upc' => '',
                'price_te' => (float) $row['price'],
                'wholesale_price_te' => (float) $row['wholesale_price'],
                'id_employee' => $employeeId,
                'date_add' => date('Y-m-d H:i:s'),
                'date_upd' => null,
                'id_order' => 0,
                'id_order_detail' => 0,
                'mvt_reason' => $mvtReason->name,
                'stock_quantity_before' => $stockQtyBefore,
                'stock_movement' => $stockMvt,
                'stock_quantity_after' => $stockQtyAfter,
            ];

            $mov = new ModelMpStockMovementV2();
            $mov->hydrate($data_row);

            try {
                $res = $mov->add();
            } catch (\Throwable $th) {
                $errors[] = $th->getMessage() . "\n" . $th->getLine() . "\n" . $th->getFile();
            }

            $processed++;
        }

        if (count($errors) > 0) {
            $msg = "Riga importata con errori:\n";
            foreach ($errors as $error) {
                $msg .= $error . "\n";
            }

            return $msg;
        }

        return true;
    }

    public static function truncate()
    {
        $table = self::$definition['table'];
        $pfx = _DB_PREFIX_;
        $sql = "TRUNCATE TABLE {$pfx}{$table}";

        return Db::getInstance()->execute($sql);
    }

    public static function updateTable()
    {
        self::truncate();
        $fields = [
            'id_mpstock_document',
            'id_shop',
            'number_document',
            'date_document',
            'id_mpstock_mvt_reason',
            'id_supplier',
            'tot_qty',
            'tot_document_te',
            'tot_document_taxes',
            'tot_document_ti',
            'id_employee',
            'date_add',
            'date_upd',
        ];
        $fields_list = implode(',', $fields);
        $pfx = _DB_PREFIX_;
        $sql = "INSERT INTO {$pfx}mpstock_document_v2 "
            . '(' . $fields_list . ') '
            . "SELECT {$fields_list} FROM {$pfx}mpstock_document";
        $errors = [];

        try {
            $result = Db::getInstance()->execute($sql);
        } catch (\Throwable $th) {
            $result = false;
            $errors[] = $th->getMessage() . "\n" . $th->getLine() . "\n" . $th->getFile();
        }

        return [
            'errors' => $errors,
            'rows_affected' => \Db::getInstance()->Affected_Rows(),
        ];
    }

    public static function getByFilename($filename)
    {
        $pfx = _DB_PREFIX_;
        $table = self::$definition['table'];
        $primary = self::$definition['primary'];
        $filename = pSQL($filename);

        $sql = "SELECT {$primary} FROM {$pfx}{$table} WHERE number_document = '{$filename}'";
        $result = (int) Db::getInstance()->getValue($sql);

        if ($result) {
            return new self($result);
        }

        return false;
    }
}
