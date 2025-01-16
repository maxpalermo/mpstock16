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

        $this->documentDate = $xml->movement_date;
        $this->movementType = (string) $xml->movement_type;

        foreach ($xml->rows->row as $row) {
            $content[] = [
                'ean13' => (string) $row->ean13,
                'reference' => (string) $row->reference,
                'qty' => (int) $row->qty,
                'price' => (float) $row->price,
                'wholesale_price' => (float) $row->wholesale_price,
            ];
        }

        $this->content = $content;

        return true;
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
