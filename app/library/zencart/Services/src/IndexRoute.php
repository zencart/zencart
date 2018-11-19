<?php
/**
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id:$
 */
namespace ZenCart\Services;

/**
 * Class IndexRoute
 * @package ZenCart\Services
 */
class IndexRoute extends AbstractService
{

    /**
     *
     */
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
            $sql = $this->dbConn->bindVars($sql, ':configValue:', $this->request->readPost('store_owner_email'), 'string');
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
