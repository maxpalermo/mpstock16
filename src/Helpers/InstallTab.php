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

class InstallTab
{
    public const HIDDEN_CLASS_NAME = 'hidden';
    public const EMPTY_CLASS_NAME = '';

    private $error = '';
    private $tab = null;

    /**
     * Summary of __construct
     *
     * @param string $moduleName The module Name
     * @param string $parentClassName The parent class name, or EMPTY_CLASS_NAME for root or HIDDEN_CLASS_NAME for hidden
     * @param mixed $tabClassName The Admin Controller name
     * @param mixed $menuLabel The menu label, can be an array of multilingual values formatted as ['id_lang' => 'label']
     * @param mixed $active If true, Tab menu will be shown, default true
     */
    public function __construct($moduleName, $parentClassName, $tabClassName, $menuLabel, $active = true)
    {
        $tab = new \Tab();

        $tab->id_parent = $this->getParentId($parentClassName);
        $tab->class_name = $tabClassName;
        if (is_array($menuLabel)) {
            foreach (   $menuLabel as $langId => $label) {
                $tab->name[$langId] = $label;
            }
        } else {
            foreach (\Language::getLanguages() as $lang) {
                $tab->name[$lang['id_lang']] = $menuLabel;
            }
        }
        $tab->module = $moduleName;
        $tab->icon = 'icon-cogs';
        $tab->position = $tab->getNewLastPosition($tab->id_parent);
        $tab->active = $active;
        $tab->hide_host_mode = 0;

        $this->tab = $tab;
    }

    public function getParentId($name)
    {
        if ($name == self::HIDDEN_CLASS_NAME) {
            return -1;
        }

        return (int) \Tab::getIdFromClassName($name);
    }

    public function install()
    {
        try {
            $result = $this->tab->add();
        } catch (\Throwable $th) {
            $this->error = $th->getMessage();
            $result = false;
        }

        return $result;
    }

    public static function installRoot($moduleName, $tabClassName, $menuLabel, $active = true)
    {
        return (new InstallTab($moduleName, self::EMPTY_CLASS_NAME, $tabClassName, $menuLabel, $active))->install();
    }

    public static function installWithParent($moduleName, $parentClassName, $tabClassName, $menuLabel, $active = true)
    {
        return (new InstallTab($moduleName, $parentClassName, $tabClassName, $menuLabel, $active))->install();
    }

    public static function installHidden($moduleName, $tabClassName, $menuLabel, $active = true)
    {
        return (new InstallTab($moduleName, self::HIDDEN_CLASS_NAME, $tabClassName, $menuLabel, $active))->install();
    }

    public static function uninstall($adminClassName)
    {
        $id_tab = (int) \Tab::getIdFromClassName($adminClassName);
        if (!$id_tab) {
            return true;
        }

        $tab = new \Tab($id_tab);

        return $tab->delete();
    }

    public function getError()
    {
        return $this->error;
    }
}
