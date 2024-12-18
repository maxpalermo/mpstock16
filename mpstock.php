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
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'mpstock/src/Models/autoload.php';
require_once _PS_MODULE_DIR_ . 'mpstock/vendor/autoload.php';

use MpSoft\MpStock\Helpers\InstallTab;

class MpStock extends Module
{
    protected $config_form = false;
    protected $adminClassName = 'AdminMpStock';
    protected $id_lang;
    protected $id_shop;
    protected $mpMovement;
    public $link;
    public $smarty;
    private $errors = [];
    private $warnings = [];
    private $confirmations = [];

    public function __construct()
    {
        $this->name = 'mpstock';
        $this->tab = 'administration';
        $this->version = '1.1.6';
        $this->author = 'Massimiliano Palermo';
        $this->need_instance = 0;
        $this->bootstrap = true;
        /** CONSTRUCT **/
        parent::__construct();
        /** OTHER CONFIG **/
        $this->displayName = $this->l('MP Gestione Magazzino');
        $this->description = $this->l('Gestisce le quantità di magazzino.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
        $this->ps_versions_compliancy = ['min' => '1.6', 'max' => _PS_VERSION_];
        $this->id_lang = (int) Context::getContext()->language->id;
        $this->id_shop = (int) Context::getContext()->shop->id;
        $this->link = Context::getContext()->link;
        $this->smarty = Context::getContext()->smarty;
    }

    public function install()
    {
        $hooks = [
            'actionObjectOrderDetailAddAfter',
            'actionObjectOrderDetailDeleteAfter',
            'actionObjectOrderDetailUpdateAfter',
            'actionAdminControllerSetMedia',
            'displayAdminProductsExtra',
        ];
        $res = parent::install();

        foreach ($hooks as $hook) {
            $res = $res && $this->registerHook($hook);
        }

        $res = $res
            // && MpStockMvtReasonObjectModel::install()
            // && MpStockDocumentObjectModel::install()
            // && MpStockProductObjectModel::install()
            && InstallTab::installRoot($this->name, $this->adminClassName, $this->l('Magazzino'))
            && InstallTab::installWithParent($this->name, $this->adminClassName, 'AdminMpStockDocuments', $this->l('Documenti'))
            && InstallTab::installWithParent($this->name, $this->adminClassName, 'AdminMpStockMovements', $this->l('Movimenti'))
            && InstallTab::installWithParent($this->name, $this->adminClassName, 'AdminMpStockImport', $this->l('Import'))
            && InstallTab::installWithParent($this->name, $this->adminClassName, 'AdminMpStockQuickMovement', $this->l('Movimento Veloce'))
            && InstallTab::installWithParent($this->name, $this->adminClassName, 'AdminMpStockAvailability', $this->l('Disponibilità'));
    }

    public function uninstall()
    {
        return parent::uninstall()
            && InstallTab::uninstall($this->adminClassName)
            && InstallTab::uninstall('AdminMpStockDocuments')
            && InstallTab::uninstall('AdminMpStockMovements')
            && InstallTab::uninstall('AdminMpStockImport')
            && InstallTab::uninstall('AdminMpStockQuickMovement')
            && InstallTab::uninstall('AdminMpStockAvailability');
    }

    public function hookDisplayAdminProductsExtra()
    {
        return '';
    }

    public function hookActionAdminControllerSetMedia($params)
    {
        /** @var ModuleAdminController */
        $controller = $this->context->controller;
        $controller->addCSS($this->getLocalPath() . 'views/css/icon-menu.css', 'all', 1001);
    }

    public function hookActionObjectOrderDetailAddAfter($params)
    {
        $object = $params['object'];
        $importClass = new importOrdersDetails();
        $record = $importClass->getOrderDetail($object->getFields(), importOrdersDetails::MOVEMENT_WEB_SELL);
        $model = new ModelMpStockMovement();
        $model->hydrate($record);

        try {
            $model->date_add = date('Y-m-d H:i:s');
            $res = $model->add(false, true);
            if (!$res) {
                /** @var ModuleAdminController */
                $controller = $this->context->controller;
                $controller->errors[] = Db::getInstance()->getMsgError();
            }
        } catch (\Throwable $th) {
            /** @var ModuleAdminController */
            $controller = $this->context->controller;
            $controller->errors[] = $th->getMessage();
            $res = false;
        }
    }

    public function hookActionObjectOrderDetailUpdateAfter($params)
    {
        $object = $params['object'];
        $importClass = new importOrdersDetails();
        $record = $importClass->getOrderDetail($object->getFields(), importOrdersDetails::MOVEMENT_WEB_SELL);
        $model = ModelMpStockMovement::getObjectByIdOrderDetail($object->id);
        if ($model) {
            $model->hydrate($record);
            $model->date_add = date('Y-m-d H:i:s');
        } else {
            $model = new ModelMpStockMovement();
            $model->hydrate($record);
        }

        try {
            $res = $model->save(true, false);
            if (!$res) {
                /** @var ModuleAdminController */
                $controller = $this->context->controller;
                $controller->errors[] = Db::getInstance()->getMsgError();
            }
        } catch (\Throwable $th) {
            /** @var ModuleAdminController */
            $controller = $this->context->controller;
            $controller->errors[] = $th->getMessage();
            $res = false;
        }
    }

    public function hookActionObjectOrderDetailDeleteAfter($params)
    {
        $object = $params['object'];
        $model = ModelMpStockMovement::getObjectByIdOrderDetail($object->id);
        if ($model) {
            try {
                $res = $model->delete();
                if (!$res) {
                    /** @var ModuleAdminController */
                    $controller = $this->context->controller;
                    $controller->errors[] = Db::getInstance()->getMsgError();
                }
            } catch (\Throwable $th) {
                /** @var ModuleAdminController */
                $controller = $this->context->controller;
                $controller->errors[] = $th->getMessage();
                $res = false;
            }
        }
    }
}