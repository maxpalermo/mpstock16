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

class ParseXml
{
    private $filePath;
    private $fileName;
    private $documentType;
    private $documentNumber;
    private $documentDate;
    private $movementType;
    private $content = [];
    private $parseError = false;
    private $error;

    public function __construct($filePath, $fileName)
    {
        $this->filePath = $filePath;
        $this->fileName = $fileName;
        $this->parseFileName();
    }

    private function parseFileName()
    {
        $fileName = basename($this->fileName);
        if (preg_match('/^([CS])*\((.*)-(.*)\)(.*)\.xml$/i', $fileName, $matches)) {
            $this->documentType = $matches[1] === 'C' ? 'Carico' : 'Scarico';
            $this->documentNumber = $matches[2];
            $this->documentDate = $matches[3];
        } else {
            $this->parseError = true;
        }
    }

    public function getDocumentType()
    {
        return $this->documentType;
    }

    public function getDocumentNumber()
    {
        return (string) $this->documentNumber;
    }

    public function getDocumentDate()
    {
        return (string) $this->documentDate;
    }

    public function parse()
    {
        if ($this->parseError) {
            $this->error = 'File non valido.';

            return false;
        }

        if (!file_exists($this->filePath)) {
            return false;
        }

        $xml = simplexml_load_file($this->filePath);
        $content = [];

        if (!isset($xml->rows->row)) {
            $this->content = [];

            return false;
        }

        $this->documentDate = $xml->movement_date;
        $this->movementType = (string) $xml->movement_type;

        foreach ($xml->rows->row as $row) {
            $content[] = [
                'ean13' => (string) $row->ean13,
                'reference' => (string) $row->reference,
                'qty' => (int) $row->qty,
                'price' => (float) $row->price,
                'wholesale_price' => (float) $row->wholesale_price,
                'exists' => false,
            ];
        }

        $this->hydrateProducts($content);

        $this->content = $content;

        return true;
    }

    protected function toSql($value)
    {
        if (!trim($value)) {
            return '';
        }

        return "'" . pSQL($value) . "'";
    }

    protected function hydrateProducts(&$content)
    {
        $id_lang = (int) \Context::getContext()->language->id;

        $ean13s = array_map(
            [$this, 'toSql'],
            array_unique(array_column($content, 'ean13'))
        );
        $references = array_map(
            [$this, 'toSql'],
            array_unique(array_column($content, 'reference'))
        );

        $ean13s = array_filter($ean13s);
        $references = array_filter($references);

        $ean13s = implode(',', $ean13s);
        $references = implode(',', $references);

        $db = \Db::getinstance();
        $sql = new \DbQuery();
        $sql->select('COALESCE(pa.ean13, p.ean13) as ean13')
            ->select('COALESCE(pa.reference, p.reference) as reference')
            ->select('pl.name as product_name')
            ->select('GROUP_CONCAT(al.name SEPARATOR " ") as combination')
            ->from('product_attribute', 'pa')
            ->innerJoin('product', 'p', 'p.id_product = pa.id_product')
            ->innerJoin('product_lang', 'pl', 'pl.id_product = pa.id_product AND pl.id_lang = ' . $id_lang)
            ->leftJoin('product_attribute_combination', 'pac', 'pac.id_product_attribute = pa.id_product_attribute')
            ->leftJoin('attribute_lang', 'al', 'al.id_attribute = pac.id_attribute AND al.id_lang = ' . $id_lang)
            ->where("pa.ean13 IN ({$ean13s}) OR pa.reference IN ({$references})")
            ->groupBy('pa.id_product_attribute');

        $query = $sql->build();

        $products = $db->executeS($query);

        if ($products) {
            foreach ($content as &$row) {
                foreach ($products as $product) {
                    if ($row['ean13'] === $product['ean13'] || $row['reference'] === $product['reference']) {
                        $row['checkbox'] = true;
                        $row['product_name'] = $product['product_name'];
                        $row['combination'] = $product['combination'];
                        $row['exists'] = true;

                        break;
                    }
                }
            }
        }

        return $content;
    }

    public function getDocumentContent()
    {
        return $this->content;
    }

    public function getMovementType()
    {
        return (string) $this->movementType;
    }

    public function getError()
    {
        return $this->error;
    }
}
