<?php
/**
 * File contains zc_install tests and some general preliminary test-environment setup scripts
 *
 * @package tests
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:
 */

/**
 * Class postInstallCatalogChecksTest
 */
class postInstallCatalogChecksTest extends CommonTestResources
{
    function testLoadStoreMainPage()
    {
        $this->url('http://' . BASE_URL);
        $this->assertTextPresent( WEBTEST_STORE_NAME);
    }

    function testLoadStoreCategoryPage()
    {
        $this->url('http://' . BASE_URL . 'index.php?main_page=index&cPath=1');
        $this->assertTextPresent('CDROM Drives');
        $this->assertTextPresent('Keyboards');
        $this->assertTextPresent('Monitors');
    }
}
