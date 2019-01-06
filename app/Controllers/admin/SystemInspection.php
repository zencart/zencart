<?php
/**
 * @copyright Copyright 2003-2017 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  New in v1.6.0 $
 */
namespace App\Controllers\admin;

use App\Controllers\AbstractInfoController;

use ZenCart\Services\IndexRoute;
use ZenCart\Request\Request as Request;
use ZenCart\AdminUser\AdminUser as User;

/**
 * Class SystemInspection
 * @package App\Controllers
 */
class SystemInspection extends AbstractInfoController
{

    /**
     *
     */
    public function mainExecute()
    {
        $this->tplVarManager->set('contentTemplate', 'tplSystemInspection.php');
        $this->buildNewAdminPages();
        $this->buildNewDBTables();
        $this->buildNewModules();
        $this->buildMissingAdminPages();
    }

    private function buildNewAdminPages()
    {
        $this->tplVarManager->set('hasAdminPages', false);
        $this->tplVarManager->set('hasFoundAdminPages', false);
        $sql = " SELECT * FROM " . TABLE_ADMIN_PAGES;
        $pages = $this->dbConn->Execute($sql);
        $foundPages = 0;
        if ($pages->RecordCount() > 0) {
            $this->tplVarManager->set('hasAdminPages', true);
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
                $this->tplVarManager->push('newAdminPages', array(
                    'name' => $languageKey,
                    'menuKey' => $page['menu_key'],
                    'display' => $page['display_on_menu'],
                    'pageLink' => $pageLink
                ));
            }
            if ($foundPages != 0) {
                $this->tplVarManager->set('hasFoundAdminPages', true);
            }
        }
    }

    private function buildNewDBTables()
    {
        $this->tplVarManager->set('hasFoundDBTables', false);
        $this->tplVarManager->set('newDBTables', array());
        $sql = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '" . DB_DATABASE . "'";
        $tables = $this->dbConn->Execute($sql);
        $foundTables = 0;
        if ($tables->RecordCount() > 0) {
            $this->tplVarManager->set('hasDBShemaTable', true);
            foreach ($tables as $table) {
                $key = $table['TABLE_NAME'];
                if (DB_PREFIX != '') {
                    $key = substr($key, strlen(DB_PREFIX));
                }
                if (in_array($key, $GLOBALS['built_in_tables']) || in_array($key, $GLOBALS['optional_tables'])) {
                    continue;
                }
                $foundTables++;
                $this->tplVarManager->push('newDBTables', $table['TABLE_NAME']);
            }
            if ($foundTables != 0) {
                $this->tplVarManager->set('hasFoundDBTables', true);
            }
        }
    }

    private function buildNewModules()
    {
        $this->tplVarManager->set('hasFoundModules', false);
        $count = 0;
        $list = explode (';', MODULE_PAYMENT_INSTALLED);
        foreach ($list as $item) {
            if (!in_array($item, $GLOBALS['built_in_payments'])) {
                $count++;
                $this->tplVarManager->push('newModules', array('type' => BOX_MODULES_PAYMENT, 'value' => $item));
            }
        }
        $list = explode (';', MODULE_SHIPPING_INSTALLED);
        foreach ($list as $item) {
            if (!in_array($item, $GLOBALS['built_in_shippings'])) {
                $count++;
                $this->tplVarManager->push('newModules', array('type' => BOX_MODULES_SHIPPING, 'value' => $item));
           }
        }
        $list = explode (';', MODULE_ORDER_TOTAL_INSTALLED);
        foreach ($list as $item) {
            if (!in_array($item, $GLOBALS['built_in_order_totals'])) {
                $count++;
                $this->tplVarManager->push('newModules', array('type' => BOX_MODULES_ORDER_TOTAL, 'value' => $item));
            }
        }
        if ($count > 0) {
            $this->tplVarManager->set('hasFoundModules', true);
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
        $this->tplVarManager->set('missingPages', $missing_pages);
        $this->tplVarManager->set('hasMissingAdminPages', (count($missing_pages) > 0) ? true : false);
    }
}
