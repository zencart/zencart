<?php
/**
 * File Two Factor Authentication hooks Tests
 *
 * @package tests
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: testTwoFactorAuthenticationHooks.php 18781 2011-05-23 16:40:40Z wilt $
 */

/**
 *
 * @package tests
 */
class testTwoFactorAuthenticationHooks extends zcCommonTestResources
{
    public function testTwoFactorAuthenticationTrue()
    {
        $this->createTwoFactorAuthenticationOverrideTrue();
        $this->open('https://' . DIR_WS_ADMIN);
        $this->waitForPageToLoad(10000);
        $this->type("admin_name", WEBTEST_ADMIN_NAME_INSTALL);
        $this->type("admin_pass", WEBTEST_ADMIN_PASSWORD_INSTALL_SSL);
        $this->clickAndWait("submit");
        $this->assertTextPresent('Hit Counter Started:');
        $this->removeTwoFactorAuthenticationOverride();
    }

    public function testTwoFactorAuthenticationFalse()
    {
        $this->createTwoFactorAuthenticationOverrideFalse();
        $this->open('https://' . DIR_WS_ADMIN);
        $this->waitForPageToLoad(10000);
        $this->type("admin_name", WEBTEST_ADMIN_NAME_INSTALL);
        $this->type("admin_pass", WEBTEST_ADMIN_PASSWORD_INSTALL_SSL);
        $this->clickAndWait("submit");
        $this->assertTextPresent('You entered the wrong username or password.');
        $this->removeTwoFactorAuthenticationOverride();
    }
}
