<?php
/**
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  New in v1.6.0 $
 */
namespace ZenCart\Controllers;

use ZenCart\Services\IndexRoute;
use ZenCart\Request\Request as Request;
use ZenCart\AdminUser\AdminUser as User;

/**
 * Class Index
 * @package ZenCart\Controllers
 */
class SystemInspection extends AbstractInfoController
{

    /**
     *
     */
    public function mainExecute()
    {
        $this->tplVars['contentTemplate'] = 'tplSystemInspection.php';
        $this->buildNewAdminPages();
        $this->buildNewDBTables();
        $this->buildNewModules();
        $this->buildMissingAdminPages();
    }

    private function buildNewAdminPages()
    {
        $this->tplVars['hasAdminPages'] = false;
        $this->tplVars['hasFoundAdminPages'] = false;
        $sql = " SELECT * FROM " . TABLE_ADMIN_PAGES;
        $pages = $this->dbConn->Execute($sql);
        $foundPages = 0;
        if ($pages->RecordCount() > 0) {
            $this->tplVars['hasAdminPages'] = true;
            foreach ($pages as $page) {
                $key = $page['language_key'];
                if (in_array($key, $GLOBALS['built_in_boxes'])) {
                    continue;
                }
                $foundPages++;
                $languageKey = "(" . $page['language_key'] . ")";
                if (defined($page['language_key'])) {
                    $languageKey = constant($page['language_key']);
                }
                $pageLink = NO_LINK;
                if (defined($page['language_key']) && defined($page['main_page'])) {
                    $pageLink = '<a href="' . zen_href_link(constant($page['main_page']),
                            $page['page_params']) . '">' . constant($page['language_key']) . '</a>';
                }
                $this->tplVars['newAdminPages'][] = array(
                    'name' => $languageKey,
                    'menuKey' => $page['menu_key'],
                    'display' => $page['display_on_menu'],
                    'pageLink' => $pageLink
                );
            }
            if ($foundPages != 0) {
                $this->tplVars['hasFoundAdminPages'] = true;
            }
        }
    }

    private function buildNewDBTables()
    {
        $this->tplVars['hasDBShemaTable'] = false;
        $this->tplVars['hasFoundDBTables'] = false;
        $sql = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '" . DB_DATABASE . "'";
        $tables = $this->dbConn->Execute($sql);
        $foundTables = 0;
        if ($tables->RecordCount() > 0) {
            $this->tplVars['hasDBShemaTable'] = true;
            foreach ($tables as $table) {
                $key = $table['TABLE_NAME'];
                if (DB_PREFIX != '') {
                    $key = substr($key, strlen(DB_PREFIX));
                }
                if (in_array($key, $GLOBALS['built_in_tables']) || in_array($key, $GLOBALS['optional_tables'])) {
                    continue;
                }
                $foundTables++;
                $this->tplVars['newDBTables'][] = $table['TABLE_NAME'];
            }
            if ($foundTables != 0) {
                $this->tplVars['hasFoundDBTables'] = true;
            }
        }
    }

    private function buildNewModules()
    {
        $this->tplVars['hasFoundModules'] = false;
        $count = 0;
        $list = explode (';', MODULE_PAYMENT_INSTALLED);
        foreach ($list as $item) {
            if (!in_array($item, $GLOBALS['built_in_payments'])) {
                $count++;
                $this->tplVars['newModules'][] = array('type' => BOX_MODULES_PAYMENT, 'value' => $item);
            }
        }
        $list = explode (';', MODULE_SHIPPING_INSTALLED);
        foreach ($list as $item) {
            if (!in_array($item, $GLOBALS['built_in_shippings'])) {
                $count++;
                $this->tplVars['newModules'][] = array('type' => BOX_MODULES_SHIPPING, 'value' => $item);
            }
        }
        $list = explode (';', MODULE_ORDER_TOTAL_INSTALLED);
        foreach ($list as $item) {
            if (!in_array($item, $GLOBALS['built_in_order_totals'])) {
                $count++;
                $this->tplVars['newModules'][] = array('type' => BOX_MODULES_ORDER_TOTAL, 'value' => $item);
            }
        }
        if ($count > 0) {
            $this->tplVars['hasFoundModules'] = true;
        }
    }

    private function buildMissingAdminPages()
    {
        $sql = " SELECT * FROM " . TABLE_CONFIGURATION_GROUP . " WHERE visible = '1'";
        $pages = $this->dbConn->Execute($sql);
        $missing_pages = array();
        foreach ($pages as $page) {
            $gid = $page['configuration_group_id'];
            $sql = "SELECT * FROM " . TABLE_ADMIN_PAGES . " WHERE page_params = 'gid=". (int)$gid . "'";
            $admin_entry = $this->dbConn->Execute($sql);
            if ($admin_entry->EOF) {
                $missing_pages[] = array('gid' => $gid,
                                         'name' => $page['configuration_group_title']);
            }
        }
        $this->tplVars['missingPages'] = $missing_pages;
        $this->tplVars['hasMissingAdminPages'] = (count($missing_pages) > 0) ? true : false;;

    }
}
