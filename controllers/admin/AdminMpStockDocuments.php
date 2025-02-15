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
use MpSoft\MpStockV2\Helpers\ProductUtils;
use MpSoft\MpStockV2\Helpers\Response;

class AdminMpStockDocumentsController extends ModuleAdminController
{
    protected $mvtReasons;
    protected $suppliers;
    protected $employees;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'mpstock_document_v2';
        $this->identifier = 'id_mpstock_document';
        $this->className = 'ModelMpStockDocumentV2';
        $this->lang = false;
        $this->deleted = false;
        $this->explicitSelect = true;
        $this->allow_export = true;
        $this->context = Context::getContext();
        $this->_orderBy = 'date_add';
        $this->_orderWay = 'DESC';

        $this->mvtReasons = $this->getMvtReasons();
        $this->suppliers = $this->getSuppliers();
        $this->employees = $this->getEmployees();

        parent::__construct();
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
        $this->addCSS('https://fonts.googleapis.com/icon?family=Material+Icons');
        $this->addCSS(_MODULE_DIR_ . 'mpstockv2/views/css/buttons.css', 'all', 1001);
        $this->addJqueryPlugin('autocomplete');
        $this->addJqueryUI('ui.autocomplete');
    }

    public function renderList()
    {
        return false;
    }

    public function initPageHeaderToolbar()
    {
        $this->page_header_toolbar = true;

        $this->page_header_toolbar_btn['new_document'] = [
            'href' => 'javascript:void(0);',
            'desc' => $this->module->l('Nuovo documento', $this->controller_name),
            'icon' => 'process-icon-new',
        ];

        parent::initPageHeaderToolbar();
    }

    public function initContent()
    {
        $tpl = $this->context->smarty->createTemplate(
            $this->getTemplatePath() . 'documents/document.tpl',
            $this->context->smarty
        );

        $params = [
            'admin_controller_url' => $this->context->link->getAdminLink('AdminMpStockDocuments'),
            'admin_url' => $this->context->link->getAdminLink($this->controller_name),
            'base_url' => $this->module->getPathUri(),
            'base_url_views' => $this->module->getPathUri() . 'views/',
            'base_url_js' => $this->module->getPathUri() . 'views/js/',
            'script_src' => $this->module->getPathUri() . 'views/js/movements/index.js',
            'mvtReasons' => $this->mvtReasons,
            'suppliers' => $this->suppliers,
            'employees' => $this->employees,
        ];

        $tpl->assign($params);

        $page = $tpl->fetch();

        $this->content = $page;

        return parent::initContent();
    }

    public function ajaxProcessSaveDocument()
    {
        $id_document = (int) Tools::getValue('id_document');
        $number_document = Tools::getValue('number_document');
        $date_document = Tools::getValue('date_document');
        $id_supplier = (int) Tools::getValue('id_supplier');
        $id_mpstock_mvt_reason = (int) Tools::getValue('id_mpstock_mvt_reason');
        $id_employee = (int) Tools::getValue('id_employee');

        $model = new ModelMpStockDocumentV2($id_document);
        $model->number_document = $number_document;
        $model->date_document = $date_document;
        $model->id_supplier = $id_supplier;
        $model->id_mpstock_mvt_reason = $id_mpstock_mvt_reason;
        $model->id_employee = $id_employee;

        $result = $model->save();

        Response::json([
            'success' => (bool) $result,
        ]);
    }

    public function ajaxProcessGetDocuments()
    {
        $start = (int) Tools::getValue('start');
        $length = (int) Tools::getValue('length');
        $search = Tools::getValue('search')['value'];
        $draw = (int) Tools::getValue('draw');
        $columns = Tools::getValue('columns');
        $order = Tools::getValue('order');

        $model = new ModelMpStockDocumentV2();
        $documents = $model->dataTable($start, $length, $columns, $order);

        Response::json(
            [
                'draw' => $draw,
                'recordsTotal' => $documents['totalRecords'],
                'recordsFiltered' => $documents['totalFiltered'],
                'data' => $documents['data'],
            ]
        );
    }

    public function ajaxProcessGetDocumentDetails($params)
    {
        $id_document = (int) $params['id'];
        $ajax = (int) $params['ajax'];

        $document = new ModelMpStockDocumentV2($id_document);
        if (!Validate::isLoadedObject($document)) {
            return false;
        }

        $movements = ModelMpStockMovementV2::getMovementsByIdDocument($id_document);

        $response =
            [
                'success' => true,
                'id_document' => $id_document,
                'id_mvt_reason' => $document->id_mpstock_mvt_reason,
                'document' => $document->getFields(),
                'movements' => $movements,
            ];

        if ($ajax) {
            Response::json($response);
        }

        return $response;
    }

    public function ajaxProcessGetProductAutocomplete()
    {
        $term = Tools::getValue('term');
        $type = Tools::getValue('type', 'product');
        $sql = new DbQuery();
        $sql->select('p.id_product as value, p.reference, CONCAT(" (", p.reference, ") ", pl.name) as label')
                ->from('product', 'p')
                ->leftJoin('product_lang', 'pl', 'p.id_product = pl.id_product AND pl.id_lang = ' . (int) $this->context->language->id);
        if ($type == 'product') {
            $sql->where('pl.name LIKE "%' . pSQL($term) . '%"')
                ->limit(10);
        } elseif ($type == 'reference') {
            $sql->where('p.reference LIKE "%' . pSQL($term) . '%"')
                ->limit(10);
        } elseif ($type == 'ean13') {
            $sql->where('p.ean13 = "' . pSQL($term) . '"')
                ->limit(1);
        }

        $result = Db::getInstance()->executeS($sql);

        if ($result) {
            foreach ($result as &$item) {
                $product = new Product($item['value'], false, $this->context->language->id);
                $options = GetProductAttributeCombination::createOptions($item['value']);

                if (Validate::isLoadedObject($product)) {
                    $cover = Product::getCover($item['value']);
                    if ($cover) {
                        $image_src = ProductUtils::getImageSrc($cover['id_image'], 'cover');
                    } else {
                        $image_src = null;
                    }

                    $item['product_name'] = $product->name;
                    $item['reference'] = $product->reference;
                    $item['ean13'] = $product->ean13;
                    $item['options'] = implode('', $options);
                    $item['cover'] = $image_src;
                }
            }
        }

        if ($type == 'ean13' && $result) {
            Response::json($result[0]);
        }

        Response::json($result);
    }

    public function ajaxProcessGetProductAttributeDetails($params)
    {
        $shop = \Context::getContext()->shop;
        $id_shop = \Context::getContext()->shop->id;
        $id_lang = \Context::getContext()->language->id;

        $id_product_attribute = (int) $params['id_product_attribute'];
        if (!$id_product_attribute) {
            return [
                'success' => false,
                'message' => $this->module->l('Errore: attributo non valido', get_class($this)),
            ];
        }

        $combination = new Combination($id_product_attribute, $id_lang);
        if (!Validate::isLoadedObject($combination)) {
            return [
                'success' => false,
                'message' => $this->module->l('Errore: combinazione non valida', get_class($this)),
            ];
        }

        $quantity = StockAvailable::getQuantityAvailableByProduct($combination->id_product, $id_product_attribute);
        if ($quantity === false) {
            return [
                'success' => false,
                'message' => $this->module->l('Errore: quantità non valida', get_class($this)),
            ];
        }

        $quantity = (int) $quantity;

        $id_images = GetProductAttributeCombination::getProductAttributeImages($combination->id);
        if ($id_images) {
            $image_src = ProductUtils::getImageSrc($id_images[0]['id_image'], 'medium');
        } else {
            $image_src = null;
        }

        return [
            'success' => true,
            'data' => [
                'id_product' => $combination->id_product,
                'id_product_attribute' => $combination->id,
                'reference' => $combination->reference,
                'ean13' => $combination->ean13,
                'quantity' => $quantity,
                'image_src' => $image_src,
            ],
        ];
    }

    public function ajaxProcessGetEan13()
    {
        $ean13 = Tools::getValue('ean13');
        $id_mvt = (int) Tools::getValue('id_mvt_reason');

        $sign = ModelMpStockMvtReasonV2::getSign($id_mvt);

        // Controllo se l'EAN13 è nella tabella product_attribute
        $sql = new DbQuery();
        $sql
            ->select('pa.id_product')
            ->select('pa.id_product_attribute')
            ->from('product_attribute', 'pa')
            ->where('pa.ean13 = "' . pSQL($ean13) . '"');

        $result = Db::getInstance()->getRow($sql);

        // altrimenti controllo che l'ean13 sia nella tabella product
        if (!$result) {
            $sql = new DbQuery();
            $sql
                ->select('p.id_product as value')
                ->select('p.reference as label')
                ->select ('0 as id_product_attribute')
                ->from('product', 'p')
                ->where('p.ean13 = "' . pSQL($ean13) . '"');
            $result = Db::getInstance()->getRow($sql);
        }

        // inserisco in $result il nome del prodotto
        if ($result) {
            $sql = new DbQuery();
            $sql->select('pl.name as label')
                ->from('product', 'p')
                ->leftJoin('product_lang', 'pl', 'p.id_product = pl.id_product AND pl.id_lang = ' . (int) $this->context->language->id)
                ->where('p.id_product = ' . (int) $result['id_product']);
            $result['name'] = Db::getInstance()->getValue($sql);
        }

        // inserisco in un array tutte le combinazioni del prodotto leggendo la tabella product_attribute
        $attributes = [];
        $sql = new DbQuery();
        $sql->select('pa.id_product_attribute as value, GROUP_CONCAT(al.name SEPARATOR ", ") as label')
            ->from('product_attribute', 'pa')
            ->leftJoin('product_attribute_combination', 'pac', 'pa.id_product_attribute = pac.id_product_attribute')
            ->leftJoin('attribute', 'a', 'pac.id_attribute = a.id_attribute')
            ->leftJoin('attribute_lang', 'al', 'a.id_attribute = al.id_attribute AND al.id_lang = ' . (int) $this->context->language->id)
            ->leftJoin('attribute_group', 'ag', 'a.id_attribute_group = ag.id_attribute_group')
            ->leftJoin('attribute_group_lang', 'agl', 'ag.id_attribute_group = agl.id_attribute_group AND agl.id_lang = ' . (int) $this->context->language->id)
            ->where('pa.id_product=' . (int) $result['id_product'])
            ->groupBy('pa.id_product_attribute')
            ->orderBy('al.name ASC');

        $sql = $sql->build();

        $attributes = Db::getInstance()->executeS($sql);
        // trasformo $attributes in un elenco di <options></options>
        $options = '';
        foreach ($attributes as $attribute) {
            $options .= '<option value="' . $attribute['value'] . '">' . $attribute['label'] . '</option>';
        }
        $result['attributes'] = $options;

        // inserisco la quantità attuale di magazzino
        $result['stock_quantity'] = StockAvailable::getQuantityAvailableByProduct($result['id_product'], $result['id_product_attribute']);

        // inserisco l'id_supplier del prodotto
        $sql = new DbQuery();
        $sql->select('ps.id_supplier')
            ->from('product_supplier', 'ps')
            ->where('ps.id_product = ' . (int) $result['id_product']);

        $result['id_supplier'] = (int) Db::getInstance()->getValue($sql);

        // inserisco il segno del movimento
        $result['sign'] = $sign;

        // inserisco il nome dell'operatore corrente
        $result['employee'] = $this->context->employee->firstname . ' ' . $this->context->employee->lastname;

        Response::json($result);
    }

    public function ajaxProcessGetProductAttributes()
    {
        $id_product = (int) Tools::getValue('productId');
        $sql = new DbQuery();
        $sql->select('pa.id_product_attribute as value, GROUP_CONCAT(CONCAT(agl.name, ":", al.name) SEPARATOR ", ") as label')
            ->from('product_attribute', 'pa')
            ->leftJoin('product_attribute_combination', 'pac', 'pa.id_product_attribute = pac.id_product_attribute')
            ->leftJoin('attribute', 'a', 'pac.id_attribute = a.id_attribute')
            ->leftJoin('attribute_lang', 'al', 'a.id_attribute = al.id_attribute AND al.id_lang = ' . (int) $this->context->language->id)
            ->leftJoin('attribute_group', 'ag', 'a.id_attribute_group = ag.id_attribute_group')
            ->leftJoin('attribute_group_lang', 'agl', 'ag.id_attribute_group = agl.id_attribute_group AND agl.id_lang = ' . (int) $this->context->language->id)
            ->where('pa.id_product = ' . $id_product)
            ->groupBy('pa.id_product_attribute')
            ->orderBy('pa.id_product_attribute ASC');

        $result = Db::getInstance()->executeS($sql);

        Response::json($result);
    }

    public function ajaxProcessGetProductQuantity()
    {
        $id_product = (int) Tools::getValue('id_product');
        $id_product_attribute = (int) Tools::getValue('id_product_attribute');

        $stock = StockAvailable::getQuantityAvailableByProduct($id_product, $id_product_attribute);

        Response::json(
            [
                'success' => true,
                'quantity' => (int) $stock,
            ]
        );
    }

    public function ajaxProcessGetProductCombinations($ajax = true, $id_product = 0)
    {
        if (!$id_product) {
            $id_product = (int) Tools::getValue('id');
        }

        $sql = new DbQuery();
        $sql->select('pa.id_product_attribute as value, GROUP_CONCAT(CONCAT(al.name) SEPARATOR ", ") as label')
            ->from('product_attribute', 'pa')
            ->leftJoin('product_attribute_combination', 'pac', 'pa.id_product_attribute = pac.id_product_attribute')
            ->leftJoin('attribute', 'a', 'pac.id_attribute = a.id_attribute')
            ->leftJoin('attribute_lang', 'al', 'a.id_attribute = al.id_attribute AND al.id_lang = ' . (int) $this->context->language->id)
            ->leftJoin('attribute_group', 'ag', 'a.id_attribute_group = ag.id_attribute_group')
            ->leftJoin('attribute_group_lang', 'agl', 'ag.id_attribute_group = agl.id_attribute_group AND agl.id_lang = ' . (int) $this->context->language->id)
            ->where('pa.id_product = ' . $id_product)
            ->groupBy('pa.id_product_attribute')
            ->orderBy('pa.id_product_attribute ASC');

        $result = Db::getInstance()->executeS($sql);

        if ($ajax) {
            Response::json($result);
        }

        return $result;
    }

    public function ajaxProcessGetCurrentStock()
    {
        $id_product = (int) Tools::getValue('productId');
        $id_product_attribute = (int) Tools::getValue('productAttributeId');

        $stock = StockAvailable::getQuantityAvailableByProduct($id_product, $id_product_attribute);

        Response::json(
            [
                'currentStock' => (int) $stock,
            ]
        );
    }

    public function ajaxProcessGetMovementDetails($params)
    {
        $id_mpstock_movement = (int) $params['id_mpstock_movement'];
        $movement = new ModelMpStockMovementV2($id_mpstock_movement);
        if ($movement) {
            $product = new Product($movement->id_product, false, $this->context->language->id);
            if (Validate::isLoadedObject($product)) {
                $movement->product_name = $product->name;
                $combinations = $this->ajaxProcessGetProductCombinations(false, $movement->id_product);
                $movement->combinations = $combinations;
                $movement->options = implode('', array_map(function ($combination) {
                    return "<option value='" . $combination['value'] . "'>" . $combination['label'] . '</option>';
                }, $combinations));
            }
            $mvt = new ModelMpStockMvtReasonV2($movement->id_mpstock_mvt_reason, $this->context->language->id);
            if (Validate::isLoadedObject($mvt)) {
                $movement->reason_name = $mvt->name;
                $movement->sign = $mvt->sign;
            }
            $employee = new Employee($movement->id_employee, false, $this->context->language->id);
            if (Validate::isLoadedObject($employee)) {
                $movement->employee_name = $employee->firstname . ' ' . $employee->lastname;
            }

            // imposto l'immagine di default
            $movement->image_src = 'https://placehold.co/160x240';
            $id_image = \Product::getCover($movement->id_product);
            if ($id_image) {
                $movement->image_src = ProductUtils::getImageSrc($id_image['id_image'], 'medium');
            }

            // Cerco l'immagine dell'attributo
            if ($movement->id_product_attribute) {
                $id_images = GetProductAttributeCombination::getProductAttributeImages($movement->id_product_attribute);
                if ($id_images) {
                    $movement->image_src = ProductUtils::getImageSrc($id_images[0]['id_image'], 'medium');
                }
            }

            $movement->success = true;
        }

        Response::json($movement);
    }

    public function ajaxProcessSaveMovement()
    {
        $id_lang = (int) $this->context->language->id;
        $documentId = (int) Tools::getValue('documentId');
        $productId = (int) Tools::getValue('productId');
        $productAttributeId = (int) Tools::getValue('productAttributeId');
        $movementReason = (int) Tools::getValue('movementReason');
        $quantity = (int) Tools::getValue('movementQuantity');
        $quantityAfter = (int) Tools::getValue('movementQuantityAfter');

        $message = null;

        $product = new Product($productId, false, $id_lang);
        $combination = new Combination($productAttributeId);
        $document = new ModelMpStockDocumentV2($documentId);
        if (!Validate::isLoadedObject($document)) {
            Response::json(
                [
                    'success' => false,
                    'title' => $this->module->l('Salva Movimento', get_class($this)),
                    'message' => $this->module->l('Errore: Documento non trovato', get_class($this)),
                ]
            );
        }
        $mvtReason = new ModelMpStockMvtReasonV2($movementReason, $id_lang);
        if (!Validate::isLoadedObject($mvtReason)) {
            Response::json(
                [
                    'success' => false,
                    'title' => $this->module->l('Salva Movimento', get_class($this)),
                    'message' => $this->module->l('Errore: Tipo di movimento non trovato', get_class($this)),
                ]
            );
        }
        $sign = (int) $mvtReason->sign;

        $id_supplier = (int) $document->id_supplier;
        $document_number = $document->number_document;
        $document_date = $document->date_document;

        $model = new ModelMpStockMovementV2();
        $model->id_document = $documentId;
        $model->id_warehouse = 0;
        $model->id_supplier = $id_supplier;
        $model->document_number = $document_number;
        $model->document_date = $document_date;
        $model->id_mpstock_mvt_reason = $movementReason;
        $model->id_product = $productId;
        $model->id_product_attribute = $productAttributeId;
        $model->reference = $product->reference;
        $model->ean13 = $combination->ean13;
        $model->upc = $combination->upc;
        $model->price_te = $product->price;
        $model->wholesale_price_te = $product->wholesale_price;
        $model->id_employee = (int) $this->context->employee->id;
        $model->date_add = date('Y-m-d H:i:s');
        $model->date_upd = null;
        $model->id_order = null;
        $model->id_order_detail = null;
        $model->mvt_reason = $this->getMvtReasonName($movementReason);
        $model->stock_quantity_before = StockAvailable::getQuantityAvailableByProduct($productId, $productAttributeId);
        $model->stock_movement = $quantity;
        $model->stock_quantity_after = $quantityAfter;

        try {
            $res = $model->add();

            StockAvailable::updateQuantity($productId, $productAttributeId, $quantity * $sign);

            $message = $this->l('Movimento salvato correttamente');
        } catch (\Throwable $th) {
            $res = false;
            $message = $th->getMessage();
        }

        Response::json(
            [
                'success' => $res,
                'title' => $this->module->l('Salva Movimento', get_class($this)),
                'message' => $message,
            ]
        );
    }

    public function ajaxProcessEditMovement()
    {
        $id = (int) Tools::getValue('id');
        $model = new ModelMpStockMovementV2($id);
        $id_product = (int) Tools::getValue('id_product');
        $id_product_attribute = (int) Tools::getValue('id_product_attribute');
        if (!Validate::isLoadedObject($model)) {
            Response::json(
                [
                    'success' => false,
                    'title' => $this->module->l('Modifica Movimento', get_class($this)),
                    'message' => $this->module->l('Errore: Movimento non trovato', get_class($this)),
                ]
            );
        }
        $sign = (int) Tools::getValue('sign');
        $model->stock_quantity_before = StockAvailable::getQuantityAvailableByProduct($id_product, $id_product_attribute);
        $model->stock_movement = (int) Tools::getValue('stock_movement') * (int) $sign;
        $model->stock_quantity_after = $model->stock_quantity_before + $model->stock_movement;
        $model->mvt_reason = $this->getMvtReasonName($model->id_mpstock_mvt_reason);
        $model->save();

        Response::json(
            [
                'success' => true,
                'title' => $this->module->l('Modifica Movimento', get_class($this)),
                'message' => $this->module->l('Movimento modificato correttamente', get_class($this)),
            ]
        );
    }

    public function ajaxProcessAddMovement()
    {
        $id = (int) Tools::getValue('id');
        $model = new ModelMpStockMovementV2($id);
        $id_product = (int) Tools::getValue('id_product');
        $id_product_attribute = (int) Tools::getValue('id_product_attribute');
        if (Validate::isLoadedObject($model)) {
            Response::json(
                [
                    'success' => false,
                    'title' => $this->module->l('Salva Movimento', get_class($this)),
                    'message' => $this->module->l('Errore: Movimento già esistente.', get_class($this)),
                ]
            );
        }
        $sign = (int) Tools::getValue('sign');

        $document = new ModelMpStockDocumentV2((int) Tools::getValue('id_mpstock_document'));
        if (!Validate::isLoadedObject($document)) {
            Response::json(
                [
                    'success' => false,
                    'title' => $this->module->l('Salva Movimento', get_class($this)),
                    'message' => $this->module->l('Errore: Documento non trovato', get_class($this)),
                ]
            );
        }

        $movement = new ModelMpStockMvtReasonV2((int) Tools::getValue('id_mpstock_mvt_reason'), $this->context->language->id);
        if (!Validate::isLoadedObject($movement)) {
            Response::json(
                [
                    'success' => false,
                    'title' => $this->module->l('Salva Movimento', get_class($this)),
                    'message' => $this->module->l('Errore: Tipo di movimento non trovato', get_class($this)),
                ]
            );
        }

        $model = new ModelMpStockMovementV2();

        $model->id_document = $document->id;
        $model->document_number = $document->number_document;
        $model->date_document = $document->date_document;
        $model->id_supplier = (int) Tools::getValue('id_supplier');
        $model->id_mpstock_mvt_reason = (int) Tools::getValue('id_mpstock_mvt_reason');
        $model->id_mpstock_movement = $movement->name;
        $model->mvt_reason = $this->getMvtReasonName($model->id_mpstock_mvt_reason);
        $model->id_product = (int) Tools::getValue('id_product');
        $model->id_product_attribute = (int) Tools::getValue('id_product_attribute');
        $model->ean13 = Tools::getValue('ean13');
        $model->id_employee = (int) $this->context->employee->id;
        $model->stock_quantity_before = StockAvailable::getQuantityAvailableByProduct($id_product, $id_product_attribute);
        $model->stock_movement = (int) Tools::getValue('stock_movement') * (int) $sign;
        $model->stock_quantity_after = $model->stock_quantity_before + $model->stock_movement;
        $model->unit_price = (float) Tools::getValue('unit_price');
        $model->date_add = date('Y-m-d H:i:s');
        $model->date_upd = null;

        try {
            $model->add(false, true);
        } catch (\Throwable $th) {
            Response::json([
                'success' => false,
                'title' => $this->module->l('Salva Movimento', get_class($this)),
                'message' => sprintf(
                    $this->module->l('Errore: %s', get_class($this)),
                    $th->getMessage()
                ),
            ]);
        }

        Response::json(
            [
                'success' => true,
                'title' => $this->module->l('Nuovo Movimento', get_class($this)),
                'message' => $this->module->l('Movimento aggiunto correttamente', get_class($this)),
            ]
        );
    }

    protected function refreshTableMovements($id_invoice)
    {
        $db = Db::getInstance();
        $query = new DbQuery();
    }

    public function renderForm()
    {
        return parent::renderForm();
    }

    protected function getMvtReasons()
    {
        $id_lang = (int) $this->context->language->id;
        $sql = new DbQuery();
        $sql->select('a.id_mpstock_mvt_reason, a.sign, b.name')
            ->from('mpstock_mvt_reason_v2', 'a')
            ->leftJoin('mpstock_mvt_reason_lang', 'b', 'a.id_mpstock_mvt_reason = b.id_mpstock_mvt_reason and b.id_lang=' . (int) $this->context->language->id)
            ->orderBy('b.name ASC');

        $result = Db::getInstance()->executeS($sql);
        if (!$result) {
            return [];
        }

        return $result;
    }

    protected function getSuppliers()
    {
        $id_lang = (int) $this->context->language->id;
        $sql = new DbQuery();
        $sql->select('*')
            ->from('supplier')
            ->orderBy('name ASC');

        $result = Db::getInstance()->executeS($sql);
        if (!$result) {
            return [];
        }

        return $result;
    }

    protected function getEmployees()
    {
        $id_lang = (int) $this->context->language->id;
        $sql = new DbQuery();
        $sql->select('*')
            ->from('employee')
            ->orderBy('lastname ASC');

        $result = Db::getInstance()->executeS($sql);
        if (!$result) {
            return [];
        }

        return $result;
    }

    public function getMvtReasonName($value, $returnsName = true)
    {
        if ((int) $value == 0) {
            return '--';
        }

        if (!isset($this->mvtReasons[$value])) {
            return '--';
        }

        $reason = $this->mvtReasons[$value];
        if ($returnsName) {
            return $reason['name'];
        }

        return $reason;
    }

    public function getSupplierName($value)
    {
        if ((int) $value == 0) {
            return '--';
        }

        if (!isset($this->suppliers[$value])) {
            return '--';
        }

        return $this->suppliers[$value];
    }

    public function getEmployeeName($value)
    {
        if ((int) $value == 0) {
            return '--';
        }

        if (!isset($this->employees[$value])) {
            return '--';
        }

        return $this->employees[$value];
    }

    public function postProcess()
    {
        $fetch_data = file_get_contents('php://input');
        if ($fetch_data) {
            $fetch_data = json_decode($fetch_data, true);
            if (isset($fetch_data['action'])) {
                try {
                    Response::json($this->{'ajaxProcess' . Tools::ucfirst($fetch_data['action'])}($fetch_data));
                } catch (\Throwable $th) {
                    Response::json(['error' => $th->getMessage()]);
                }
            }
        }

        return parent::postProcess();
    }
}
