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

require_once _PS_MODULE_DIR_ . 'mpstockv2/src/Models/autoload.php';
require_once _PS_MODULE_DIR_ . 'mpstockv2/vendor/autoload.php';

class MpStockV2 extends Module
{
    public const MPSTOCK_DEFAULT_LOAD_MVT_ID = 'MPSTOCK_DEFAULT_LOAD_MVT_ID';
    public const MPSTOCK_DEFAULT_UNLOAD_MVT_ID = 'MPSTOCK_DEFAULT_UNLOAD_MVT_ID';
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
        $this->name = 'mpstockv2';
        $this->tab = 'administration';
        $this->version = '1.2.2';
        $this->author = 'Massimiliano Palermo';
        $this->need_instance = 0;
        $this->bootstrap = true;
        /** CONSTRUCT **/
        parent::__construct();
        /** OTHER CONFIG **/
        $this->displayName = $this->l('MP Gestione Magazzino V2');
        $this->description = $this->l('Gestisce le quantità di magazzino.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
        $this->ps_versions_compliancy = ['min' => '1.6', 'max' => '1.6.99'];
        $this->id_lang = (int) Context::getContext()->language->id;
        $this->id_shop = (int) Context::getContext()->shop->id;
        $this->link = Context::getContext()->link;
        $this->smarty = Context::getContext()->smarty;
    }

    public function install()
    {
        $install = MpSoft\MpStockV2\Helpers\InstallTab::class;
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
            && $install::installRoot($this->name, $this->adminClassName, $this->l('MagazzinoV2'))
            && $install::installWithParent($this->name, $this->adminClassName, 'AdminMpStockDocuments', $this->l('Documenti'))
            && $install::installWithParent($this->name, $this->adminClassName, 'AdminMpStockMovements', $this->l('Movimenti'))
            && $install::installWithParent($this->name, $this->adminClassName, 'AdminMpStockImport', $this->l('Import'))
            && $install::installWithParent($this->name, $this->adminClassName, 'AdminMpStockQuickMovement', $this->l('Movimento Veloce'))
            && $install::installWithParent($this->name, $this->adminClassName, 'AdminMpStockAvailability', $this->l('Disponibilità'))
            && $install::installWithParent($this->name, $this->adminClassName, 'AdminMpStockConfig', $this->l('Configurazione'));

        return $res;
    }

    public function uninstall()
    {
        $install = MpSoft\MpStockV2\Helpers\InstallTab::class;

        return parent::uninstall()
            && $install::uninstall($this->adminClassName)
            && $install::uninstall('AdminMpStockDocuments')
            && $install::uninstall('AdminMpStockMovements')
            && $install::uninstall('AdminMpStockImport')
            && $install::uninstall('AdminMpStockQuickMovement')
            && $install::uninstall('AdminMpStockAvailability')
            && $install::uninstall('AdminMpStockConfig');
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

        if ($controller->controller_name == 'AdminModules' || preg_match('/^AdminMp/', $controller->controller_name)) {
            $controller->addJS($this->getLocalPath() . 'views/js/plugins/htmx/htmx.js');
            $controller->addJS($this->getLocalPath() . 'views/js/plugins/swal2/swal2.js');
            $controller->addCSS($this->getLocalPath() . 'views/css/fa-list.css', 'all', 1001);
            $controller->addCSS($this->getLocalPath() . 'views/css/process-icon-list.css', 'all', 1002);
        }
    }

    protected function updateMovement($orderDetail, $type = 'update')
    {
        $movement = new \ModelMpStockMovementV2();
        $movement->hydrateFromOrderDetail($orderDetail, $type == 'delete');

        switch ($type) {
            case 'add':
                $movement->document_number = "ADD-{$movement->document_number}";

                break;
            case 'delete':
                $movement->document_number = "DEL-{$movement->document_number}";

                break;
            default:
                $movement->document_number = "UPD-{$movement->document_number}";

                break;
        }

        try {
            $res = $movement->add(false, true);
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

    public function hookActionObjectOrderDetailAddAfter($params)
    {
        /** @var OrderDetail */
        $object = $params['object'];
        $this->updateMovement($object, 'add');
    }

    public function hookActionObjectOrderDetailUpdateAfter($params)
    {
        /** @var OrderDetail */
        $object = $params['object'];
        $this->updateMovement($object, 'update');
    }

    public function hookActionObjectOrderDetailDeleteAfter($params)
    {
        $object = $params['object'];
        $this->updateMovement($object, 'delete');
    }

    public function getContent()
    {
        $tpl_path = $this->getlocalPath() . 'views/templates/admin/getContent/index.tpl';
        $tpl = $this->context->smarty->createTemplate($tpl_path);
        $tpl->assign('link', $this->context->link);
        $tpl->assign('mvtReasons', $this->getMvtReasons());
        $tpl->assign('mvtLoadReasonId', self::getLoadMvtId());
        $tpl->assign('mvtUnloadReasonId', self::getUnloadMvtId());
        $content = $tpl->fetch();

        return $content;
    }

    public function getMvtReasons()
    {
        return ModelMpStockMvtReasonV2::getMvtReasons();
    }

    public static function updateLoadMvtId($value)
    {
        Configuration::updateValue(MpStockV2::MPSTOCK_DEFAULT_LOAD_MVT_ID, $value);
    }

    public static function updateUnloadMvtId($value)
    {
        Configuration::updateValue(MpStockV2::MPSTOCK_DEFAULT_UNLOAD_MVT_ID, $value);
    }

    public static function getConfig()
    {
        return [
            'mvtLoadReasonId' => (int) Configuration::get(MpStockV2::MPSTOCK_DEFAULT_LOAD_MVT_ID),
            'mvtUnloadReasonId' => (int) Configuration::get(MpStockV2::MPSTOCK_DEFAULT_UNLOAD_MVT_ID),
        ];
    }

    public static function getLoadMvtId()
    {
        return (int) Configuration::get(MpStockV2::MPSTOCK_DEFAULT_LOAD_MVT_ID);
    }

    public static function getUnloadMvtId()
    {
        return (int) Configuration::get(MpStockV2::MPSTOCK_DEFAULT_UNLOAD_MVT_ID);
    }
}
