<?php
/**
 * zcActionAdminIndex Class.
 *
 * @package classes
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id:$
 */
use ZenCart\Admin\DashboardWidget\WidgetManager;

require_once __DIR__ . '/../class.zcActionAdminBase.php';

class zcActionAdminIndex extends zcActionAdminBase
{
    public $useFoundation = TRUE;

    public function initDefinitions()
    {
        $this->templateVariables ['cssList'] [] = array(
            'href' => 'includes/template/css/index.css',
            'id' => 'indexCSS'
        );
    }

    public function mainExecute()
    {
        global $hasDoneStartWizard;
        if ($hasDoneStartWizard == FALSE) {
            $this->doStartWizardDisplay();
        } else {
            $this->doWidgetsDisplay();
        }
    }

    public function doWidgetsDisplay()
    {
        $widgetProfileList = WidgetManager::getInstallableWidgetsList($_SESSION ['admin_id'], $_SESSION ['languages_id']);
        $widgetInfoList = WidgetManager::getWidgetInfoForUser($_SESSION ['admin_id'], $_SESSION ['languages_id']);
        if (sizeof($widgetInfoList) > 0) { 
           $this->templateVariables ['widgetList'] = WidgetManager::loadWidgetClasses($widgetInfoList);
           $this->templateVariables ['widgets'] = WidgetManager::prepareTemplateVariables($this->templateVariables ['widgetList']);
           $this->templateVariables ['widgetInfoList'] = $widgetInfoList;
        }
    }

    public function doStartWizardDisplay()
    {
        $this->mainTemplate = 'tplIndexStartWizard.php';
        $storeAddress = $this->request->readPost('store_address', ((STORE_NAME_ADDRESS != '') ? STORE_NAME_ADDRESS : ''));
        $storeName = $this->request->readPost('store_name', ((STORE_NAME != '') ? STORE_NAME : ''));
        $storeOwner = $this->request->readPost('store_owner', ((STORE_OWNER != '') ? STORE_OWNER : ''));
        $storeOwnerEmail = $this->request->readPost('store_owner_email', ((STORE_OWNER_EMAIL_ADDRESS != '') ? STORE_OWNER_EMAIL_ADDRESS : ''));
        $storeCountry = $this->request->readPost('store_country', ((STORE_COUNTRY != '') ? STORE_COUNTRY : ''));
        $storeZone = $this->request->readPost('store_zone', ((STORE_ZONE != '') ? STORE_ZONE : ''));
        $country_string = zen_draw_pull_down_menu('store_country', zen_get_countries(), $storeCountry, 'id="store_country" tabindex="4"');
        $zone_string = zen_draw_pull_down_menu('store_zone', zen_get_country_zones($storeCountry), $storeZone, 'id="store_zone" tabindex="5"');
        $this->templateVariables ['storeName'] = $storeName;
        $this->templateVariables ['storeAddress'] = $storeAddress;
        $this->templateVariables ['storeOwner'] = $storeOwner;
        $this->templateVariables ['storeOwnerEmail'] = $storeOwnerEmail;
        $this->templateVariables ['countryString'] = $country_string;
        $this->templateVariables ['zoneString'] = $zone_string;
    }

    public function setupWizardExecute()
    {
        global $db;
        if ($this->request->readPost('store_name', '') != '') {
            $sql = "UPDATE " . TABLE_CONFIGURATION . " set configuration_value = :configValue:
                    WHERE configuration_key = 'STORE_NAME'";
            $sql = $db->bindVars($sql, ':configValue:', $this->request->readPost('store_name'), 'string');
            $db->execute($sql);
        }
        if ($this->request->readPost('store_owner', '') != '') {
            $sql = "UPDATE " . TABLE_CONFIGURATION . " set configuration_value = :configValue:
                    WHERE configuration_key = 'STORE_OWNER'";
            $sql = $db->bindVars($sql, ':configValue:', $this->request->readPost('store_owner'), 'string');
            $db->execute($sql);
        }
        if ($this->request->readPost('store_owner_email', '') != '') {
            $sql = "UPDATE " . TABLE_CONFIGURATION . " set configuration_value = :configValue:
                    WHERE configuration_key in ('STORE_OWNER_EMAIL_ADDRESS', 'EMAIL_FROM', 'SEND_EXTRA_ORDER_EMAILS_TO',
                                                'SEND_EXTRA_CREATE_ACCOUNT_EMAILS_TO', 'SEND_EXTRA_LOW_STOCK_EMAILS_TO',
                                                'SEND_EXTRA_GV_CUSTOMER_EMAILS_TO', 'SEND_EXTRA_GV_ADMIN_EMAILS_TO',
                                                'SEND_EXTRA_DISCOUNT_COUPON_ADMIN_EMAILS_TO',
                                                'SEND_EXTRA_ORDERS_STATUS_ADMIN_EMAILS_TO',
                                                'SEND_EXTRA_REVIEW_NOTIFICATION_EMAILS_TO', 'MODULE_PAYMENT_CC_EMAIL')";
            $sql = $db->bindVars($sql, ':configValue:', $this->request->readPost('store_owner_email'), 'string');
            $db->execute($sql);
        }
        if ($this->request->readPost('store_country', '') != '') {
            $sql = "UPDATE " . TABLE_CONFIGURATION . " set configuration_value = :configValue:
                    WHERE configuration_key in ('STORE_COUNTRY', 'SHIPPING_ORIGIN_COUNTRY')";
            $sql = $db->bindVars($sql, ':configValue:', $this->request->readPost('store_country'), 'integer');
            $db->execute($sql);
        }
        if ($this->request->readPost('store_zone', '') != '') {
            $sql = "UPDATE " . TABLE_CONFIGURATION . " set configuration_value = :configValue:
                    WHERE configuration_key = 'STORE_ZONE'";
            $sql = $db->bindVars($sql, ':configValue:', $this->request->readPost('store_zone'), 'integer');
            $db->execute($sql);
        }
        if ($this->request->readPost('store_address', '') != '') {
            $sql = "UPDATE " . TABLE_CONFIGURATION . " set configuration_value = :configValue:
                    WHERE configuration_key = 'STORE_NAME_ADDRESS'";
            $sql = $db->bindVars($sql, ':configValue:', $this->request->readPost('store_address'), 'string');
            $db->execute($sql);
        }
        zen_redirect(zen_href_link(FILENAME_DEFAULT));
    }
}
