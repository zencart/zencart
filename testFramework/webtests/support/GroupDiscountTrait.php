<?php
/**
 * File contains common unit/web test resources
 *
 * @package tests
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  $
 */

/**
 * Class GroupDiscountTrait
 */
trait GroupDiscountTrait
{

    public function setCustomerGroupDiscount($customerEmail, $groupDiscountId)
    {
        $customerId = $this->getCustomerIdFromEmail($customerEmail);
        if ($customerId === false) {
            return;
        }

        $this->loginStandardAdmin(WEBTEST_ADMIN_NAME_INSTALL, WEBTEST_ADMIN_PASSWORD_INSTALL);
        $this->url('https://' . DIR_WS_ADMIN . 'customers.php?page=1&cID=' . $customerId . '&action=edit');
        $this->select($this->byName('customers_group_pricing'))->selectOptionByValue($groupDiscountId);
        $this->byXpath("//input[@type='image']")->click();

    }

}
