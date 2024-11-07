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

require_once _PS_MODULE_DIR_ . 'mpstock/models/autoload.php';
require_once _PS_MODULE_DIR_ . 'mpstock/helpers/autoload.php';

require_once _PS_MODULE_DIR_ . 'mpstock/classes/MpStockObjectModelTypeMovement.php';
require_once _PS_MODULE_DIR_ . 'mpstock/classes/MpStockHelperFormAddTypeMovement.php';
require_once _PS_MODULE_DIR_ . 'mpstock/classes/MpStockHelperListTypeMovement.php';
require_once _PS_MODULE_DIR_ . 'mpstock/classes/MpStockProductExtraHelperForm.php';
require_once _PS_MODULE_DIR_ . 'mpstock/classes/MpStockProductExtraHelperList.php';
require_once _PS_MODULE_DIR_ . 'mpstock/classes/MpStockTools.php';
require_once _PS_MODULE_DIR_ . 'mpstock/classes/MpStockProductExtraMovements.php';

require_once _PS_MODULE_DIR_ . 'mpstock/classes/MpStockMvtReasonObjectModel.php';
require_once _PS_MODULE_DIR_ . 'mpstock/classes/MpStockDocumentObjectModel.php';
require_once _PS_MODULE_DIR_ . 'mpstock/classes/MpStockProductObjectModel.php';

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
        $this->version = '1.1.0';
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
        $this->link = new Link();
        $this->smarty = Context::getContext()->smarty;
    }

    /**
     * Return the admin class name
     *
     * @return string Admin class name
     */
    public function getAdminClassName()
    {
        return $this->adminClassName;
    }

    /**
     * Return the Admin Template Path
     *
     * @return string The admin template path
     */
    public function getAdminTemplatePath()
    {
        return $this->getPath() . 'views/templates/admin/';
    }

    /**
     * Get the Id of current language
     *
     * @return int id language
     */
    public function getIdLang()
    {
        return (int) $this->id_lang;
    }

    /**
     * Get the Id of current shop
     *
     * @return int id shop
     */
    public function getIdShop()
    {
        return (int) $this->id_shop;
    }

    /**
     * Get The URL path of this module
     *
     * @return string The URL of this module
     */
    public function getUrl()
    {
        return $this->_path;
    }

    /**
     * Return the physical path of this module
     *
     * @return string The path of this module
     */
    public function getPath()
    {
        return $this->local_path;
    }

    /**
     * Add a message to Errors collection
     *
     * @param string $message Message to add to collection
     */
    public function addError($message)
    {
        $this->errors[] = $message;
    }

    /**
     * Add a message to Warnings collection
     *
     * @param string $message Message to add to collection
     */
    public function addWarning($message)
    {
        $this->warnings[] = $message;
    }

    /**
     * Add a message to Confirmations collection
     *
     * @param string $message Message to add to collection
     */
    public function addConfirmation($message)
    {
        $this->confirmations[] = $message;
    }

    /**
     * Check if there is an Ajax call and execute it.
     */
    public function ajax()
    {
        if (Tools::isSubmit('ajax') && Tools::isSubmit('action')) {
            $action = 'ajaxProcess' . Tools::ucfirst(Tools::getValue('action'));
            $this->$action();
            exit();
        }
    }

    /**
     * Display Messages collections
     *
     * @return string HTML messages
     */
    public function displayMessages()
    {
        $output = [];
        foreach ($this->errors as $msg) {
            $output[] = $this->displayError($msg);
        }
        foreach ($this->warnings as $msg) {
            $output[] = $this->displayWarning($msg);
        }
        foreach ($this->confirmations as $msg) {
            $output[] = $this->displayConfirmation($msg);
        }

        return implode('', $output);
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

        $res = $res && MpStockMvtReasonObjectModel::install()
            && MpStockDocumentObjectModel::install()
            && MpStockProductObjectModel::install()
            && $this->installTab('', $this->adminClassName, $this->l('Magazzino'))
            && $this->installTab('', 'AdminMpStockDocuments', $this->l('Magazzino - Documenti'))
            && $this->installTab('', 'AdminMpStockMovements', $this->l('Magazzino - Movimenti'));
    }

    public function uninstall()
    {
        return parent::uninstall()
            && $this->uninstallTab($this->adminClassName)
            && $this->uninstallTab('AdminMpStockDocuments')
            && $this->uninstallTab('AdminMpStockMovements');
    }

    /**
     * Install a new menu
     *
     * @param string $parent Parent tab name
     * @param string $class_name Class name of the module
     * @param string $name Display name of the module
     * @param boolean $active If true, Tab menu will be shown
     *
     * @return boolean True if successfull, False otherwise
     */
    public function installTab($parent, $class_name, $name, $active = 1)
    {
        // Create new admin tab
        $tab = new Tab();

        $tab->id_parent = (int) Tab::getIdFromClassName($parent);
        $tab->name = [];

        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = $name;
        }

        $tab->class_name = $class_name;
        $tab->module = $this->name;
        $tab->active = $active;

        if (!$tab->add()) {
            $this->addError($this->l('Error during Tab install.'));

            return false;
        }

        return true;
    }

    /**
     * Uninstall a menu
     *
     * @param string pe $class_name Class name of the module
     *
     * @return boolean True if successfull, False otherwise
     */
    public function uninstallTab($class_name)
    {
        $id_tab = (int) Tab::getIdFromClassName($class_name);
        if ($id_tab) {
            $tab = new Tab((int) $id_tab);

            return $tab->delete();
        }
    }

    public function hookDisplayBackOfficeHeader()
    {
        $ctrl = $this->context->controller;
        if ($ctrl instanceof AdminController && method_exists($ctrl, 'addCss')) {
            $ctrl->addCss($this->_path . 'views/css/icon-menu.css');
        }
    }

    public function hookDisplayAdminProductsExtra()
    {
        return $this->smarty->fetch($this->getAdminTemplatePath() . 'hookDisplayAdminProductExtra.tpl');
    }
}
