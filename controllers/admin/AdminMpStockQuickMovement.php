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

use MpSoft\MpStockV2\Helpers\GetProductAttributeCombination;
use MpSoft\MpStockV2\Helpers\GetProductImage;
use MpSoft\MpStockV2\Helpers\Response;

class AdminMpStockQuickMovementController extends ModuleAdminController
{
    protected $mvtReasons = [];

    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'mpstock_document_v2';
        $this->className = 'ModelMpStockMovementV2';
        $this->lang = false;
        $this->context = Context::getContext();
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        parent::__construct();

        $this->mvtReasons = ModelMpStockMvtReasonV2::getMvtReasons($this->context->language->id);
    }

    protected function response($params)
    {
        header('Content-Type: application/json; charset=utf-8');
        ob_clean();
        exit(json_encode($params));
    }

    public function setMedia()
    {
        parent::setMedia();

        $this->addJqueryUI('ui.datepicker');
        $this->addCSS(_MODULE_DIR_ . 'mpstockv2/views/js/plugins/datatables/datatables.min.css');
        $this->addJS(_MODULE_DIR_ . 'mpstockv2/views/js/plugins/datatables/datatables.min.js');
        $this->addCSS(_MODULE_DIR_ . 'mpstockv2/views/js/plugins/toastify/toastify.css');
        $this->addJS(_MODULE_DIR_ . 'mpstockv2/views/js/plugins/toastify/toastify.js');
        $this->addJS(_MODULE_DIR_ . 'mpstockv2/views/js/plugins/toastify/showToastify.js');
        $this->addJS(_MODULE_DIR_ . 'mpstockv2/views/js/plugins/swal2/swal2.js');
        $this->addJS(_MODULE_DIR_ . 'mpstockv2/views/js/plugins/htmx/htmx.js');
        $this->addCSS(_MODULE_DIR_ . 'mpstockv2/views/css/style.css');
        $this->addJqueryPlugin('autocomplete');
        $this->addJqueryUI('ui.autocomplete');
    }

    public function initContent()
    {
        $tpl_path = $this->getTemplatePath() . 'quickMvt/quickMvt.tpl';
        $tpl = $this->context->smarty->createTemplate($tpl_path);
        $tpl->assign([
            'admin_controller_url' => $this->context->link->getAdminLink($this->controller_name),
            'mvtReasons' => $this->mvtReasons,
            'baseUrl' => $this->context->shop->getBaseURL(true),
        ]);

        $content = $tpl->fetch();
        $this->content = $content;

        return parent::initContent();
    }

    public function ajaxProcessGetProductByEan13()
    {
        $ean13 = Tools::getValue('ean13', '');

        if (!$ean13) {
            Response::json([
                'id_product' => 0,
                'id_product_attribute' => 0,
                'name' => '',
                'quantity' => 0,
                'image_url' => GetProductImage::getProductImageCover(0),
            ]);
        }

        $db = Db::getInstance();
        $sql = 'select * from ' . _DB_PREFIX_ . "product_attribute where ean13='" . pSQL($ean13) . "'";
        $row = $db->getRow($sql);
        if ($row) {
            $productName = GetProductAttributeCombination::getProductName($row['id_product']);
            $productCombination = GetProductAttributeCombination::getCombinationName($row['id_product_attribute']);

            if ($productCombination) {
                $productCombination = $productCombination['label'];
            }

            Response::json([
                'id_product' => $row['id_product'],
                'id_product_attribute' => $row['id_product_attribute'],
                'name' => "{$productName} - {$productCombination}",
                'quantity' => 1,
                'image_url' => GetProductImage::getProductImageCover($row['id_product']),
            ]);
        }

        Response::json([
            'id_product' => 0,
            'id_product_attribute' => 0,
            'name' => '',
            'quantity' => 0,
            'image_url' => GetProductImage::getProductImageCover(0),
        ]);
    }

    public function ajaxProcessAddQuickMovement()
    {
        $id_lang = (int) Context::getContext()->language->id;
        $movement = Tools::getValue('movement');
        $commons = ModelMpStockMovementV2::getCommons((int) $movement['id_product_attribute']);
        $sign = (int) $movement['sign'];

        if ($sign == 0) {
            Response::json([
                'success' => false,
                'message' => Tools::displayError('Tipo di movimento non valido.'),
            ]);
        }

        if ($sign == 1) {
            $mvtReasonId = MpStockV2::getLoadMvtId();
        } else {
            $mvtReasonId = MpStockV2::getUnloadMvtId();
        }

        $mvtReason = new ModelMpStockMvtReasonV2($mvtReasonId, $id_lang);

        if (!Validate::isLoadedObject($mvtReason)) {
            Response::json([
                'success' => false,
                'message' => sprintf(Tools::displayError('Movimento non valido: %s'), $mvtReasonId),
            ]);
        }

        $model = new ModelMpStockMovementV2();
        $productId = (int) $movement['id_product'];
        $productAttributeId = (int) $movement['id_product_attribute'];
        $stockBefore = StockAvailable::getQuantityAvailableByProduct($productId, $productAttributeId);
        $stockMvt = (int) $movement['quantity'] * $sign;
        $stockAfter = $stockBefore + $stockMvt;

        $data = [
            'id_document' => 0,
            'ref_movement' => $mvtReason->name,
            'id_warehouse' => 0,
            'id_supplier' => $commons['id_supplier'],
            'document_number' => 'MOVIMENTO-VELOCE',
            'document_date' => date('Y-m-d H:i:s'),
            'id_mpstock_mvt_reason' => $mvtReasonId,
            'id_product' => $movement['id_product'],
            'id_product_attribute' => $movement['id_product_attribute'],
            'reference' => $commons['reference'],
            'ean13' => $commons['ean13'],
            'upc' => $commons['upc'],
            'price_te' => $commons['price_te'],
            'wholesale_price_te' => $commons['wholesale_price_te'],
            'id_employee' => (int) Context::getContext()->employee->id,
            'id_order' => 0,
            'id_order_detail' => 0,
            'mvt_reason' => $mvtReason->name,
            'stock_quantity_before' => $stockBefore,
            'stock_movement' => $stockMvt,
            'stock_quantity_after' => $stockAfter,
            'date_add' => date('Y-m-d H:i:s'),
        ];

        $model->hydrate($data);

        try {
            $result = $model->add(false, true);
        } catch (\Throwable $th) {
            Response::json([
                'success' => false,
                'message' => $th->getMessage(),
            ]);
        }

        StockAvailable::setQuantity($productId, $productAttributeId, $stockAfter);
        $currentStock = (int) StockAvailable::getQuantityAvailableByProduct($productId, $productAttributeId);

        Response::json([
            'success' => (int) $result,
            'message' => sprintf(
                'Movimento salvato.<br>Quantità attuale: <strong>%s</strong>',
                $currentStock
            ),
        ]);
    }

    public function getProductByEan13($ean13, $reference)
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('pa.id_product')
            ->select('pa.id_product_attribute')
            ->select('pa.ean13')
            ->select('p.reference')
            ->select('p.price')
            ->select('t.rate as tax_rate')
            ->from('product_attribute', 'pa')
            ->innerJoin('product', 'p', 'p.id_product=pa.id_product')
            ->innerJoin('tax_rule', 'tr', 'tr.id_tax_rules_group=p.id_tax_rules_group')
            ->innerJoin('tax', 't', 't.id_tax=tr.id_tax')
            ->where('pa.reference=\'' . pSQL($reference) . '\'')
            ->where('pa.ean13=\'' . pSQL($ean13) . '\'');

        $product = $db->getRow($sql);
        if (!$product) {
            return [];
        }

        $product['error'] = 0;
        $product['confirmation'] = $this->module->displayConfirmation(
            sprintf(
                'Product %s %s has been processed.',
                isset($product['reference']) ? $product['reference'] : '',
                isset($product['ean13']) ? $product['ean13'] : ''
            )
        );

        return $product;
    }
}
