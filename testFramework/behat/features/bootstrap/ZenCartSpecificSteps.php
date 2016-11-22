<?php
/**
 * @package tests
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  $
 */

/**
 * Class ZenCartSpecificSteps
 */
trait ZenCartSpecificSteps
{

    /**
     * @Given I add swedish kroner
     */
    public function iAddSwedishKroner()
    {
        $sql = "INSERT INTO currencies VALUES ('','Swedish Krona','SEK','SEK','',',','','2','1', now());";
        $this->doDbQuery($sql);
    }


    /**
     * @Then I should reset install
     */
    public function iShouldResetInstall()
    {
        if (!defined('CWD')) {
            define('CWD', getcwd());
        }
        $file_contents = file_get_contents(CWD . '/includes/dist-configure.php');
        $fp = fopen(CWD . '/includes/configure.php', 'w');
        chmod(CWD . '/includes/configure.php', 0777);
        if ($fp) {
            fputs($fp, $file_contents);
            fclose($fp);
        }

        $the_file = getcwd() . '/zc_install/includes/extra_configures/dev----developer_mode.php';
        if (!file_exists($the_file)) {
            $fp = fopen($the_file, 'w');
            if ($fp) {
                fputs($fp, "<?php define('DEVELOPER_MODE', true);");
                fclose($fp);
            }
        }

        $the_file = getcwd() . '/admin/includes/extra_configures/dev----developer_mode.php';
        if (!file_exists($the_file)) {
            $fp = fopen($the_file, 'w');
            if ($fp) {
                fputs($fp, "<?php define('DEVELOPER_MODE', true);\ndefine('ADMIN_BLOCK_WARNING_OVERRIDE', 'true');");
                fclose($fp);
            }
        }

    }

    /**
     * @Given I do a standard admin login with <param>:arg1, <param>:arg2
     */
    public function iDoAStandardAdminLoginWithParamParam($arg1, $arg2)
    {
        $this->visit('/admin/index.php?cmd=logoff');
        $this->visit('/admin/');
        $this->assertPageContainsText('Admin Login');
        $this->fillField('admin_name', $this->configParams[$arg1]);
        $this->fillField('admin_pass', $this->configParams[$arg2]);
        $this->iSubmitTheForm('loginForm');
    }

    /**
     * @Given I do a standard customer login with <param>:arg1, <param>:arg2
     */
    public function iDoAStandardCustomerLoginWithParamParam($arg1, $arg2)
    {
        $this->visit('/');
        $this->visit('/index.php?main_page=logoff');
        $this->visit('/index.php?main_page=login');
        $this->fillField('login-email-address', $this->configParams[$arg1]);
        $this->fillField('login-password', $this->configParams[$arg2]);
        $this->iClickOnTheElementWithXPath("//*[@id='loginForm']/div[1]/input");
    }


    /**
     * @Given I purchase a gift voucher queue on with <param>:arg1, <param>:arg2, :arg3
     */
    public function iPurchaseAGiftVoucherQueueOnWithParamParam($arg1, $arg2, $arg3)
    {
        $this->iDoAStandardCustomerLoginWithParamParam($arg1, $arg2);
        $this->visit('index.php?main_page=shopping_cart&action=empty_cart');
        $this->visit('index.php?main_page=document_product_info&cPath=21&products_id=32');
        $this->iFillInCssElementWith('input[name=cart_quantity]', $arg3);
        $this->iClickOnTheElementWithXPath("//*[@id='cartAdd']/input[3]");
        $this->visit('index.php?main_page=shopping_cart');
        $this->assertPageContainsText('Gift Certificate');
        $amount = number_format(100 * $arg3, 2);
        $this->assertPageContainsText('Amount: $' . $amount);
        $this->visit('index.php?main_page=checkout_shipping');
        $this->assertPageContainsText('Free Shipping');
        $this->iClickOnTheElementWithXPath("//*[@id='paymentSubmit']/input");
        $this->assertPageContainsText('Order Confirmation');
        $this->iClickOnTheElementWithXPath("//*[@id='btn_submit']");
        $this->assertPageContainsText('Checkout Success Sample Text');
        $this->iDoAStandardAdminLoginWithParamParam('admin_user_main', 'admin_password_main');
        $this->visit('/admin/index.php?cmd=gv_queue');
        $this->assertPageContainsText('Gift Certificate Release Queue');
        $this->iClickOnTheElementWithXPath("//*[@id='adminLeadItemRows']/tr/td[6]/a");
        $this->iClickOnTheElementWithXPath("//*[@id='rowGvReleaseConfirm']");
    }


    /**
     * @Then I use select2 to fill in field, search value with :arg1, :arg2
     */
    public function iUseSelectToFillInFieldSearchValueAndFinalValueWith($field, $searchValue)
    {
        $field = 's2id_entry_field_' . $field;
        $this->iClickOnTheElementWithXPath("//div[contains(@id, '" . $field . "')]/descendant::a");
        $this->iWait(1);
        $this->iClickOnTheElementWithXPath("//div[contains(@class, 'select2-result-label') and .//text()='" . $searchValue . "']");
    }

    /**
     * @Given I do a first admin login with <param>:arg1, <param>:arg2, <param>:arg3
     */
    public function iDoAFirstAdminLoginWithParamParamParam($arg1, $arg2, $arg3)
    {
        $this->visit('/admin/');

        $this->iDoAStandardAdminLoginWithParamParam($arg1, $arg2);

        if ($this->configParams['serverSchema'] == 'https://') {
            return;
        }

        $this->iFillInWithParam('admin_name', 'admin_user_main');
        $this->iFillInWithParam('old_pwd', 'admin_password_install');
        $this->iFillInWithParam('admin_pass', 'admin_password_main');
        $this->iFillInWithParam('admin_pass2', 'admin_password_main');
        $this->iClickOnTheElementWithXPath("//*[@id='btn_submit']");
    }

    /**
     * @Given I set a configuration value :arg1, :arg2
     */
    public function iSetAConfigurationValue($configKey, $configValue)
    {
        $sql = "UPDATE " . $this->configParams['db_prefix'] . "configuration SET configuration_value = '" . $configValue . "' where configuration_key = '" . $configKey . "'";
        $this->doDbQuery($sql);
    }


    /**
     * @Given I switch shipping tax :arg1
     */
    public function iSwitchShippingTax($mode = 'on')
    {
        $sql = "UPDATE " . $this->configParams['db_prefix'] . "configuration SET configuration_value = '" . ($mode == 'on' ? '2' : '0') . "' where configuration_key = 'MODULE_SHIPPING_FLAT_TAX_CLASS'";
        $this->doDbQuery($sql);
    }

    /**
     * @Given I create a discount coupon :arg1
     */
    public function iCreateADiscountCoupon($arg1)
    {
        $this->createCoupon($arg1);
    }
    /**
     * @Given I switch to tax :arg1
     */
    public function iSwitchToTax($arg1)
    {
        $setting = 'true';
        if ($arg1 == 'non-inclusive') {
            $setting = 'false';
        }
        $sql = "UPDATE " . $this->configParams['db_prefix'] . "configuration SET configuration_value = '" . $setting . "' where configuration_key = 'DISPLAY_PRICE_WITH_TAX'";
        $this->doDbQuery($sql);
    }


    /**
     * @Given I switch item shipping tax :arg1
     */
    function iSwitchItemShippingTax($mode = 'on')
    {
        $sql = "UPDATE " . $this->configParams['db_prefix'] . "configuration SET configuration_value = '" . ($mode == 'on' ? '2' : '0') . "' where configuration_key = 'MODULE_SHIPPING_ITEM_TAX_CLASS'";
        $this->doDbQuery($sql);
    }

    /**
     * @Given I switch flat shipping tax :arg1
     */
    function iSwitchFlatShippingTax($mode = 'on')
    {
        $sql = "UPDATE " . $this->configParams['db_prefix'] . "configuration SET configuration_value = '" . ($mode == 'on' ? '2' : '0') . "' where configuration_key = 'MODULE_SHIPPING_FLAT_TAX_CLASS'";
        $this->doDbQuery($sql);
    }

    /**
     * @Given I switch split tax mode :arg1
     */
    function iSwitchSplitTaxMode($mode = 'on')
    {
        $this->doDbQuery("UPDATE " . $this->configParams['db_prefix'] . "configuration SET configuration_value = '" . ($mode == 'on' ? 'true' : 'false') . "' where configuration_key = 'SHOW_SPLIT_TAX_CHECKOUT'");
    }

    /**
     * @Given I set customer :arg1 to group discount id :arg2
     */
    public function iSetCustomerToGroupDiscountId($arg1, $arg2)
    {
        $customerId = $this->getCustomerIdFromEmail($this->configParams[$arg1]);
        if ($customerId === false) {
            return;
        }
        $sql = "UPDATE " . $this->configParams['db_prefix'] . "customers set customers_group_pricing = " . (int)$arg2 . " where customers_id = " . (int)$customerId;
        $this->doDbQuery($sql);

    }

    /**
     * @Given I ensure gift voucher balance is :arg1 for customer :arg2, :arg3
     */
    public function iEnsureGiftVoucherBalanceIsForCustomer($arg1, $arg2, $arg3)
    {
        $gvAmount = $this->getGVBalanceCustomer($this->configParams[$arg2]);
        if ($gvAmount < $arg1) {
            $this->iPurchaseAGiftVoucherQueueOnWithParamParam($arg2, $arg3, $arg1/100);
        }
    }

    /**
     * @Given I switch low order fee :arg1
     */
    public function iSwitchLowOrderFee($mode)
    {
        $sql = "UPDATE " . $this->configParams['db_prefix'] . "configuration set configuration_value = '" . ($mode == 'on' ? 'true' : 'false') . "' WHERE configuration_key = 'MODULE_ORDER_TOTAL_LOWORDERFEE_LOW_ORDER_FEE'";
        $this->doDbQuery($sql);
    }


}
