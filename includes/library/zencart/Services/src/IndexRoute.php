<?php
/**
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id:$
 */
namespace ZenCart\Services;
use ZenCart\DashboardWidget\WidgetManager;
use ZenCart\Request\Request as Request;
use Zencart\Controllers\AbstractController as Controller;

/**
 * Class IndexRoute
 * @package ZenCart\Services
 */
class IndexRoute extends AbstractService
{

    /**
     *
     */
    public function displayHomePage()
    {
        if (STORE_NAME == '' || STORE_OWNER == '') {
            $this->doStartWizardDisplay();
        } else {
            $this->doWidgetsDisplay();
        }

    }

    /**
     *
     */
    public function doWidgetsDisplay()
    {
        $widgetInfoList = WidgetManager::getWidgetInfoForUser($_SESSION ['admin_id'], $_SESSION ['languages_id']);
        $widgetList = widgetManager::loadWidgetClasses($widgetInfoList);
        $this->listener->setTplVar('widgetList', $widgetList);
        $this->listener->setTplVar('widgets', WidgetManager::prepareTemplateVariables($widgetList));
        $this->listener->setTplVar('widgetInfoList', $widgetInfoList);
    }

    /**
     *
     */
    public function doStartWizardDisplay()
    {
        $this->listener->setMainTemplate('tplIndexStartWizard.php');
        $storeAddress = $this->request->readPost('store_address',
            ((STORE_NAME_ADDRESS != '') ? STORE_NAME_ADDRESS : ''));
        $storeName = $this->request->readPost('store_name', ((STORE_NAME != '') ? STORE_NAME : ''));
        $storeOwner = $this->request->readPost('store_owner', ((STORE_OWNER != '') ? STORE_OWNER : ''));
        $storeOwnerEmail = $this->request->readPost('store_owner_email',
            ((STORE_OWNER_EMAIL_ADDRESS != '') ? STORE_OWNER_EMAIL_ADDRESS : ''));
        $storeCountry = $this->request->readPost('store_country', ((STORE_COUNTRY != '') ? STORE_COUNTRY : ''));
        $storeZone = $this->request->readPost('store_zone', ((STORE_ZONE != '') ? STORE_ZONE : ''));
        $country_string = zen_draw_pull_down_menu('store_country', zen_get_countries(), $storeCountry,
            'id="store_country" tabindex="4"');
        $zone_string = zen_draw_pull_down_menu('store_zone', zen_get_country_zones($storeCountry), $storeZone,
            'id="store_zone" tabindex="5"');
        $this->listener->setTplVar('storeName', $storeName);
        $this->listener->setTplVar('storeAddress', $storeAddress);
        $this->listener->setTplVar('storeOwner', $storeOwner);
        $this->listener->setTplVar('storeOwnerEmail', $storeOwnerEmail);
        $this->listener->setTplVar('countryString', $country_string);
        $this->listener->setTplVar('zoneString', $zone_string);
    }

    /**
     *
     */
    public function setupWizardExecute()
    {
        if ($this->request->readPost('store_name', '') != '') {
            $sql = "UPDATE " . TABLE_CONFIGURATION . " set configuration_value = :configValue:
                    WHERE configuration_key = 'STORE_NAME'";
            $sql = $this->dbConn->bindVars($sql, ':configValue:', $this->request->readPost('store_name'), 'string');
            $this->dbConn->execute($sql);
        }
        if ($this->request->readPost('store_owner', '') != '') {
            $sql = "UPDATE " . TABLE_CONFIGURATION . " set configuration_value = :configValue:
                    WHERE configuration_key = 'STORE_OWNER'";
            $sql = $this->dbConn->bindVars($sql, ':configValue:', $this->request->readPost('store_owner'), 'string');
            $this->dbConn->execute($sql);
        }
        if ($this->request->readPost('store_owner_email', '') != '') {
            $sql = "UPDATE " . TABLE_CONFIGURATION . " set configuration_value = :configValue:
                    WHERE configuration_key in ('STORE_OWNER_EMAIL_ADDRESS', 'EMAIL_FROM', 'SEND_EXTRA_ORDER_EMAILS_TO',
                                                'SEND_EXTRA_CREATE_ACCOUNT_EMAILS_TO', 'SEND_EXTRA_LOW_STOCK_EMAILS_TO',
                                                'SEND_EXTRA_GV_CUSTOMER_EMAILS_TO', 'SEND_EXTRA_GV_ADMIN_EMAILS_TO',
                                                'SEND_EXTRA_DISCOUNT_COUPON_ADMIN_EMAILS_TO',
                                                'SEND_EXTRA_ORDERS_STATUS_ADMIN_EMAILS_TO',
                                                'SEND_EXTRA_REVIEW_NOTIFICATION_EMAILS_TO', 'MODULE_PAYMENT_CC_EMAIL')";
            $sql = $this->dbConn->bindVars($sql, ':configValue:', $this->request->readPost('store_owner_email'),
                'string');
            $this->dbConn->execute($sql);
        }
        if ($this->request->readPost('store_country', '') != '') {
            $sql = "UPDATE " . TABLE_CONFIGURATION . " set configuration_value = :configValue:
                    WHERE configuration_key in ('STORE_COUNTRY', 'SHIPPING_ORIGIN_COUNTRY')";
            $sql = $this->dbConn->bindVars($sql, ':configValue:', $this->request->readPost('store_country'), 'integer');
            $this->dbConn->execute($sql);
        }
        if ($this->request->readPost('store_zone', '') != '') {
            $sql = "UPDATE " . TABLE_CONFIGURATION . " set configuration_value = :configValue:
                    WHERE configuration_key = 'STORE_ZONE'";
            $sql = $this->dbConn->bindVars($sql, ':configValue:', $this->request->readPost('store_zone'), 'integer');
            $this->dbConn->execute($sql);
        }
        if ($this->request->readPost('store_address', '') != '') {
            $sql = "UPDATE " . TABLE_CONFIGURATION . " set configuration_value = :configValue:
                    WHERE configuration_key = 'STORE_NAME_ADDRESS'";
            $sql = $this->dbConn->bindVars($sql, ':configValue:', $this->request->readPost('store_address'), 'string');
            $this->dbConn->execute($sql);
        }
        return true;
    }
}
