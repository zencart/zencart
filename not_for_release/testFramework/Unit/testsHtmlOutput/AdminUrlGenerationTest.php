<?php
/**
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

use Tests\Support\zcUnitTestCase;

/**
 * Testing Library
 */
class AdminUrlGenerationTest extends zcUnitTestCase
{
    public function setUp(): void
    {
        if (!defined('IS_ADMIN_FLAG')) {
            define('IS_ADMIN_FLAG', true);
        }
        parent::setUp();
        require DIR_FS_ADMIN . 'includes/defined_paths.php';
        require DIR_FS_ADMIN . DIR_WS_FUNCTIONS . 'general.php';
        require DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'functions_strings.php';
        require DIR_FS_ADMIN . 'includes/functions/html_output.php';

        if (!defined('ENABLE_SSL')) {
            define('ENABLE_SSL', 'true');
        }
        if (!defined('SESSION_USE_FQDN')) {
            define('SESSION_USE_FQDN', 'True');
        }
    }

    public function testUrlFunctionsExist()
    {
        $this->assertTrue(function_exists('zen_catalog_href_link'), 'zen_catalog_href_link() did not exist');
        $reflect = new ReflectionFunction('zen_catalog_href_link');
        $this->assertEquals(3, $reflect->getNumberOfParameters());
        $params = array('page', 'parameters', 'connection');
        foreach ($reflect->getParameters() as $param) {
            $this->assertTrue(in_array($param->getName(), $params));
        }

        $this->assertTrue(function_exists('zen_href_link'), 'zen_href_link() did not exist');
        $reflect = new ReflectionFunction('zen_href_link');
        $this->assertEquals(4, $reflect->getNumberOfParameters());
        $params = array('page', 'parameters', 'connection', 'add_session_id');
        foreach ($reflect->getParameters() as $param) {
            $this->assertTrue(in_array($param->getName(), $params));
        }
    }

    /**
     * @depends testUrlFunctionsExist
     */
    public function testAdminPage()
    {
        $this->assertURLGenerated(
            zen_href_link(),
            HTTP_SERVER . DIR_WS_ADMIN
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT),
            HTTP_SERVER . DIR_WS_ADMIN
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'test=test'),
            HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test'
        );
    }

    /**
     * @depends testAdminPage
     */
    public function testAddSessionWhenSidDefined()
    {
        if (PHP_VERSION_ID >= 80401) {
            $this->markTestSkipped('IgnoredAfterPHP841');
        }
        $GLOBALS['session_started'] = true;
        define('SID', 'zenadminid=1234567890');
        $this->assertURLGenerated(
             zen_href_link(FILENAME_DEFAULT),
            HTTP_SERVER . DIR_WS_ADMIN . '?zenadminid=1234567890'
        );
        $this->assertURLGenerated(
             zen_href_link(FILENAME_DEFAULT, 'test=test'),
            HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zenadminid=1234567890'
        );
    }

    /**
     * @depends testAdminPage
     */
    public function testAutoCorrectLeadingQuerySeparator()
    {
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, '&test=test'),
            HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, '&&test=test'),
            HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, '?test=test'),
            HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, '??test=test'),
            HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, '?&test=test'),
            HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, '&?test=test'),
            HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test'
        );
    }

    /**
     * @depends testAdminPage
     */
    public function testAutoCorrectTrailingQuerySeparator()
    {
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'test=test&'),
            HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test'
        );
        $this->assertURLGenerated(
           zen_href_link(FILENAME_DEFAULT, 'test=test&&'),
            HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'test=test?'),
            HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'test=test??'),
            HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'test=test?&'),
            HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'test=test&?'),
            HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test'
        );
    }

    /**
     * @depends testAdminPage
     */
    public function testAutoCorrectMultipleAmpersandsInQuery()
    {
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'test=test&&zen-cart=the-art-of-e-commerce'),
            HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'test=test&&&zen-cart=the-art-of-e-commerce'),
            HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'test=test&&&&zen-cart=the-art-of-e-commerce'),
            HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );

        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, '&&test=test&&zen-cart=the-art-of-e-commerce'),
            HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, '&&&test=test&&zen-cart=the-art-of-e-commerce'),
            HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, '&&&&test=test&&zen-cart=the-art-of-e-commerce'),
            HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );

        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'test=test&&zen-cart=the-art-of-e-commerce&&'),
            HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'test=test&&zen-cart=the-art-of-e-commerce&&&'),
            HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'test=test&&zen-cart=the-art-of-e-commerce&&&&'),
            HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );
    }

    /**
     * @depends testAdminPage
     */
    public function testAutoCorrectAmpersandEntitiesInQuery()
    {
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'test=test&amp;zen-cart=the-art-of-e-commerce'),
            HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'test=test&amp;&amp;zen-cart=the-art-of-e-commerce'),
            HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'test=test&amp;&amp;&amp;zen-cart=the-art-of-e-commerce'),
            HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'test=test&amp;&amp;&amp;&amp;zen-cart=the-art-of-e-commerce'),
            HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );
    }

    /**
     * @depends testAdminPage
     */
    public function testAutoCorrectMixedAmpersandAndAmbersandEntitiesInQuery()
    {
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'test=test&amp;&zen-cart=the-art-of-e-commerce'),
            HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'test=test&amp;&&zen-cart=the-art-of-e-commerce'),
            HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'test=test&amp;&&&zen-cart=the-art-of-e-commerce'),
            HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'test=test&&amp;zen-cart=the-art-of-e-commerce'),
            HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'test=test&&&amp;zen-cart=the-art-of-e-commerce'),
            HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'test=test&&&&amp;zen-cart=the-art-of-e-commerce'),
            HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'test=test&amp;&&amp;zen-cart=the-art-of-e-commerce'),
            HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'test=test&&amp;&zen-cart=the-art-of-e-commerce'),
            HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );
    }

    /**
     * @depends testAdminPage
     */
    public function testConfigurationURLs()
    {
        $this->assertURLGenerated(
            zen_href_link(FILENAME_CONFIGURATION),
            HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_CONFIGURATION
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_CONFIGURATION, 'gID=1'),
            HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_CONFIGURATION . '&amp;gID=1'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_CONFIGURATION, 'gID=1&cID=1'),
            HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_CONFIGURATION . '&amp;gID=1&amp;cID=1'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_CONFIGURATION, 'gID=1&cID=1&action=edit'),
            HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_CONFIGURATION . '&amp;gID=1&amp;cID=1&amp;action=edit'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_CONFIGURATION, array('gID' => '1', 'cID' => '1', 'action' => 'edit')),
            HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_CONFIGURATION . '&amp;gID=1&amp;cID=1&amp;action=edit'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_CONFIGURATION, array('gID' => '1', 'cID' => '1', 'action' => 'save')),
            HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_CONFIGURATION . '&amp;gID=1&amp;cID=1&amp;action=save'
        );
    }
}
