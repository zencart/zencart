<?php
/**
 * File contains URL generation test cases for the catalog side of Zen Cart
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @package tests
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id$
 */
require_once(__DIR__ . '/../support/zcTestCase.php');

/**
 * Testing Library
 */
class testAdminUrlGeneration extends zcTestCase
{
    public function setUp()
    {
        if (!defined('IS_ADMIN_FLAG')) {
            define('IS_ADMIN_FLAG', true);
        }
        parent::setUp();
        require DIR_FS_ADMIN . 'includes/defined_paths.php';
        require DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'functions_general.php';
        require DIR_FS_ADMIN . 'includes/functions/html_output.php';

        if (!defined('ENABLE_SSL')) {
            define('ENABLE_SSL', 'true');
        }
        if (!defined('SESSION_USE_FQDN')) {
            define('SESSION_USE_FQDN', 'True');
        }
        // Do not fail due to E_USER_DEPRECATED warning in zen_href_link()
		$this->iniSet('error_reporting', -1 & ~E_USER_DEPRECATED);
    }

    public function testUrlFunctionsExist()
    {
        $this->assertTrue(function_exists('zen_href_link'), 'zen_href_link() did not exist');
        $reflect = new ReflectionFunction('zen_href_link');
        $this->assertEquals(4, $reflect->getNumberOfParameters());
        $params = array('page', 'parameters', 'connection', 'add_session_id');
        foreach ($reflect->getParameters() as $param) {
            $this->assertTrue(in_array($param->getName(), $params));
        }

        $this->assertTrue(function_exists('zen_admin_href_link'), 'zen_admin_href_link() did not exist');
        $reflect = new ReflectionFunction('zen_admin_href_link');
        $this->assertEquals(3, $reflect->getNumberOfParameters());
        $params = array('page', 'parameters', 'add_session_id');
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
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'test=test'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test'
        );
        
        // Tests for zen_admin_href_link
        $this->assertURLGenerated(
            zen_admin_href_link(),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN
        );
        $this->assertURLGenerated(
            zen_admin_href_link(FILENAME_DEFAULT),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN
        );
        $this->assertURLGenerated(
            zen_admin_href_link(FILENAME_DEFAULT, 'test=test'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test'
        );
    }

    /**
     * @depends testAdminPage
     */
    public function testAdminPageSsl()
    {
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, null, 'SSL'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'test=test', 'SSL'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test'
        );
    }

    /**
     * @depends testAdminPage
     */
    public function testAddSessionWhenSidDefined()
    {
        $GLOBALS['session_started'] = true;
        define('SID', 'zenadminid=1234567890');
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . '?zenadminid=1234567890'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'test=test'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zenadminid=1234567890'
        );
        
        // Tests for zen_admin_href_link
        $this->assertURLGenerated(
             zen_admin_href_link(FILENAME_DEFAULT),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . '?zenadminid=1234567890'
        );
        $this->assertURLGenerated(
             zen_admin_href_link(FILENAME_DEFAULT, 'test=test'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zenadminid=1234567890'
        );
    }

    /**
     * @depends testAdminPage
     */
    public function testAutoCorrectLeadingQuerySeparator()
    {
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, '&test=test'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, '&&test=test'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, '?test=test'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, '??test=test'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, '?&test=test'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, '&?test=test'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test'
        );
        
        // Tests for zen_admin_href_link
        $this->assertURLGenerated(
            zen_admin_href_link(FILENAME_DEFAULT, '&test=test'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test'
        );
        $this->assertURLGenerated(
            zen_admin_href_link(FILENAME_DEFAULT, '&&test=test'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test'
        );
        $this->assertURLGenerated(
            zen_admin_href_link(FILENAME_DEFAULT, '?test=test'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test'
        );
        $this->assertURLGenerated(
            zen_admin_href_link(FILENAME_DEFAULT, '??test=test'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test'
        );
        $this->assertURLGenerated(
            zen_admin_href_link(FILENAME_DEFAULT, '?&test=test'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test'
        );
        $this->assertURLGenerated(
            zen_admin_href_link(FILENAME_DEFAULT, '&?test=test'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test'
        );
    }

    /**
     * @depends testAdminPage
     */
    public function testAutoCorrectTrailingQuerySeparator()
    {
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'test=test&'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'test=test&&'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'test=test?'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'test=test??'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'test=test?&'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'test=test&?'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test'
        );
        
        // Tests for zen_admin_href_link
        $this->assertURLGenerated(
            zen_admin_href_link(FILENAME_DEFAULT, 'test=test&'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test'
        );
        $this->assertURLGenerated(
           zen_admin_href_link(FILENAME_DEFAULT, 'test=test&&'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test'
        );
        $this->assertURLGenerated(
            zen_admin_href_link(FILENAME_DEFAULT, 'test=test?'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test'
        );
        $this->assertURLGenerated(
            zen_admin_href_link(FILENAME_DEFAULT, 'test=test??'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test'
        );
        $this->assertURLGenerated(
            zen_admin_href_link(FILENAME_DEFAULT, 'test=test?&'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test'
        );
        $this->assertURLGenerated(
            zen_admin_href_link(FILENAME_DEFAULT, 'test=test&?'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test'
        );
    }

    /**
     * @depends testAdminPage
     */
    public function testAutoCorrectMultipleAmpersandsInQuery()
    {
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'test=test&&zen-cart=the-art-of-e-commerce'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'test=test&&&zen-cart=the-art-of-e-commerce'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'test=test&&&&zen-cart=the-art-of-e-commerce'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );

        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, '&&test=test&&zen-cart=the-art-of-e-commerce'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, '&&&test=test&&zen-cart=the-art-of-e-commerce'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, '&&&&test=test&&zen-cart=the-art-of-e-commerce'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );

        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'test=test&&zen-cart=the-art-of-e-commerce&&'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'test=test&&zen-cart=the-art-of-e-commerce&&&'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'test=test&&zen-cart=the-art-of-e-commerce&&&&'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );
        
        // Tests for zen_admin_href_link
        $this->assertURLGenerated(
            zen_admin_href_link(FILENAME_DEFAULT, 'test=test&&zen-cart=the-art-of-e-commerce'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );
        $this->assertURLGenerated(
            zen_admin_href_link(FILENAME_DEFAULT, 'test=test&&&zen-cart=the-art-of-e-commerce'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );
        $this->assertURLGenerated(
            zen_admin_href_link(FILENAME_DEFAULT, 'test=test&&&&zen-cart=the-art-of-e-commerce'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );

        $this->assertURLGenerated(
            zen_admin_href_link(FILENAME_DEFAULT, '&&test=test&&zen-cart=the-art-of-e-commerce'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );
        $this->assertURLGenerated(
            zen_admin_href_link(FILENAME_DEFAULT, '&&&test=test&&zen-cart=the-art-of-e-commerce'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );
        $this->assertURLGenerated(
            zen_admin_href_link(FILENAME_DEFAULT, '&&&&test=test&&zen-cart=the-art-of-e-commerce'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );

        $this->assertURLGenerated(
            zen_admin_href_link(FILENAME_DEFAULT, 'test=test&&zen-cart=the-art-of-e-commerce&&'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );
        $this->assertURLGenerated(
            zen_admin_href_link(FILENAME_DEFAULT, 'test=test&&zen-cart=the-art-of-e-commerce&&&'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );
        $this->assertURLGenerated(
            zen_admin_href_link(FILENAME_DEFAULT, 'test=test&&zen-cart=the-art-of-e-commerce&&&&'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );
    }

    /**
     * @depends testAdminPage
     */
    public function testAutoCorrectAmpersandEntitiesInQuery()
    {
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'test=test&amp;zen-cart=the-art-of-e-commerce'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'test=test&amp;&amp;zen-cart=the-art-of-e-commerce'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'test=test&amp;&amp;&amp;zen-cart=the-art-of-e-commerce'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'test=test&amp;&amp;&amp;&amp;zen-cart=the-art-of-e-commerce'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );
        
        // Tests for zen_admin_href_link
        $this->assertURLGenerated(
            zen_admin_href_link(FILENAME_DEFAULT, 'test=test&amp;zen-cart=the-art-of-e-commerce'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );
        $this->assertURLGenerated(
            zen_admin_href_link(FILENAME_DEFAULT, 'test=test&amp;&amp;zen-cart=the-art-of-e-commerce'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );
        $this->assertURLGenerated(
            zen_admin_href_link(FILENAME_DEFAULT, 'test=test&amp;&amp;&amp;zen-cart=the-art-of-e-commerce'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );
        $this->assertURLGenerated(
            zen_admin_href_link(FILENAME_DEFAULT, 'test=test&amp;&amp;&amp;&amp;zen-cart=the-art-of-e-commerce'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );
    }

    /**
     * @depends testAdminPage
     */
    public function testAutoCorrectMixedAmpersandAndAmbersandEntitiesInQuery()
    {
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'test=test&amp;&zen-cart=the-art-of-e-commerce'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'test=test&amp;&&zen-cart=the-art-of-e-commerce'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'test=test&amp;&&&zen-cart=the-art-of-e-commerce'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'test=test&&amp;zen-cart=the-art-of-e-commerce'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'test=test&&&amp;zen-cart=the-art-of-e-commerce'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'test=test&&&&amp;zen-cart=the-art-of-e-commerce'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'test=test&amp;&&amp;zen-cart=the-art-of-e-commerce'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'test=test&&amp;&zen-cart=the-art-of-e-commerce'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );
        
        // Tests for zen_admin_href_link
        $this->assertURLGenerated(
            zen_admin_href_link(FILENAME_DEFAULT, 'test=test&amp;&zen-cart=the-art-of-e-commerce'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );
        $this->assertURLGenerated(
            zen_admin_href_link(FILENAME_DEFAULT, 'test=test&amp;&&zen-cart=the-art-of-e-commerce'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );
        $this->assertURLGenerated(
            zen_admin_href_link(FILENAME_DEFAULT, 'test=test&amp;&&&zen-cart=the-art-of-e-commerce'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );
        $this->assertURLGenerated(
            zen_admin_href_link(FILENAME_DEFAULT, 'test=test&&amp;zen-cart=the-art-of-e-commerce'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );
        $this->assertURLGenerated(
            zen_admin_href_link(FILENAME_DEFAULT, 'test=test&&&amp;zen-cart=the-art-of-e-commerce'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );
        $this->assertURLGenerated(
            zen_admin_href_link(FILENAME_DEFAULT, 'test=test&&&&amp;zen-cart=the-art-of-e-commerce'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );
        $this->assertURLGenerated(
            zen_admin_href_link(FILENAME_DEFAULT, 'test=test&amp;&&amp;zen-cart=the-art-of-e-commerce'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );
        $this->assertURLGenerated(
            zen_admin_href_link(FILENAME_DEFAULT, 'test=test&&amp;&zen-cart=the-art-of-e-commerce'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );
    }

    /**
     * @depends testAdminPage
     */
    public function testConfigurationURLs()
    {
        $this->assertURLGenerated(
            zen_href_link(FILENAME_CONFIGURATION),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_CONFIGURATION
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_CONFIGURATION, 'gID=1'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_CONFIGURATION . '&amp;gID=1'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_CONFIGURATION, 'gID=1&cID=1'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_CONFIGURATION . '&amp;gID=1&amp;cID=1'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_CONFIGURATION, 'gID=1&cID=1&action=edit'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_CONFIGURATION . '&amp;gID=1&amp;cID=1&amp;action=edit'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_CONFIGURATION, array('gID' => '1', 'cID' => '1', 'action' => 'edit')),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_CONFIGURATION . '&amp;gID=1&amp;cID=1&amp;action=edit'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_CONFIGURATION, array('gID' => '1', 'cID' => '1', 'action' => 'save')),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_CONFIGURATION . '&amp;gID=1&amp;cID=1&amp;action=save'
        );
        
        // Tests for zen_admin_href_link
        $this->assertURLGenerated(
            zen_admin_href_link(FILENAME_CONFIGURATION),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_CONFIGURATION
        );
        $this->assertURLGenerated(
            zen_admin_href_link(FILENAME_CONFIGURATION, 'gID=1'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_CONFIGURATION . '&amp;gID=1'
        );
        $this->assertURLGenerated(
            zen_admin_href_link(FILENAME_CONFIGURATION, 'gID=1&cID=1'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_CONFIGURATION . '&amp;gID=1&amp;cID=1'
        );
        $this->assertURLGenerated(
            zen_admin_href_link(FILENAME_CONFIGURATION, 'gID=1&cID=1&action=edit'),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_CONFIGURATION . '&amp;gID=1&amp;cID=1&amp;action=edit'
        );
        $this->assertURLGenerated(
            zen_admin_href_link(FILENAME_CONFIGURATION, array('gID' => '1', 'cID' => '1', 'action' => 'edit')),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_CONFIGURATION . '&amp;gID=1&amp;cID=1&amp;action=edit'
        );
        $this->assertURLGenerated(
            zen_admin_href_link(FILENAME_CONFIGURATION, array('gID' => '1', 'cID' => '1', 'action' => 'save')),
            ADMIN_HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_CONFIGURATION . '&amp;gID=1&amp;cID=1&amp;action=save'
        );
    }
}
