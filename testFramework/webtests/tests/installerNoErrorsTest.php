<?php
/**
 * @package tests
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:
 */

/**
 * Class installerNoErrorsTest
 */
class installerNoErrorsTest extends CommonTestResources
{

    public function testInstallDo()
    {
        $this->url('https://' . BASE_URL);
        $this->assertTextPresent('Thank you for loading Zen Cart');
        $this->url('https://' . BASE_URL . 'zc_install/');
        $this->assertTextPresent('System Inspection');
        $continue = $this->byId('btnsubmit');
        $continue->click();
        $this->assertTextPresent('Agree to licence terms');

        $agreeLicense = $this->byId('agreeLicense');
        $agreeLicense->click();

        $continue = $this->byId('btnsubmit');
        $continue->click();
        $this->assertTextPresent('Load Demo Data');

        $demoData = $this->byId('demoData');
        $demoData->click();

        $this->byId('db_host')->clear();
        $this->byId('db_user')->clear();
        $this->byId('db_password')->clear();
        $this->byId('db_name')->clear();

        $this->byId('db_host')->value(DB_HOST);
        $this->byId('db_user')->value(DB_USER);
        $this->byId('db_password')->value(DB_PASS);
        $this->byId('db_name')->value(DB_DBNAME);

        $continue = $this->byId('btnsubmit');
        $continue->click();

        $this->byId('admin_user')->clear();
        $this->byId('admin_user')->value(WEBTEST_ADMIN_NAME_INSTALL);
        $this->byId('admin_email')->clear();
        $this->byId('admin_email')->value(WEBTEST_ADMIN_EMAIL);
        $this->byId('admin_email2')->clear();
        $this->byId('admin_email2')->value(WEBTEST_ADMIN_EMAIL);

        $continue = $this->byId('btnsubmit');
        $continue->click();

        $this->assertTextPresent('Installation is now complete');

        $this->url('https://' . DIR_WS_ADMIN);

        $this->assertTextPresent('Admin Login');
        $this->byId('admin_name')->value(WEBTEST_ADMIN_NAME_INSTALL);
        $this->byId('admin_pass')->value(WEBTEST_ADMIN_PASSWORD_INSTALL);
        $continue = $this->byId('btn_submit');
        $continue->click();


        $this->byId('store_name')->value(WEBTEST_STORE_NAME);
        $this->byId('store_owner')->value(WEBTEST_STORE_OWNER);
        $this->byId('store_owner_email')->value(WEBTEST_STORE_OWNER_EMAIL);
        $continue = $this->byId('btnsubmit');
        $continue->click();

        $this->assertTextPresent('Add Widget');
    }
}
