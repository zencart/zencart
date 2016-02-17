<?php

/**
 * File contains tests for Admin Sanitization
 *
 * @package tests
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:New in v1.5.5 $
 */

/**
 * Class testAdminSanitization
 */
class testAdminSanitization extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        if (!defined('DIR_FS_CATALOG')) define('DIR_FS_CATALOG', realpath(dirname(__FILE__) . '/../../../'));
        if (!defined('DIR_WS_CLASSES')) define('DIR_WS_CLASSES', '/admin/includes/classes/');
        require_once(DIR_FS_CATALOG . '/includes/classes/class.base.php');
        require_once(DIR_FS_CATALOG . DIR_WS_CLASSES . 'AdminRequestSanitizer.php');
    }

    public function testInstantitation()
    {
        $arq = new AdminRequestSanitizer(array(), array(), true);
        $getAlreadySanitized = $arq->getGetKeysAlreadySanitized();
        $this->assertTrue(count($getAlreadySanitized) == 0);
    }

    public function testSimpleAlphaNumPlus()
    {
        $group = array(
            'action',
            'add_products_id',
            'attribute_id',
            'attribute_page');
        $adminSanitizationConfig['SIMPLE_ALPHANUM_PLUS'] = $group;
        $adminSanitizerTypes = array('SIMPLE_ALPHANUM_PLUS' => array('type' => 'builtin', 'strict' => false));
;
        $_GET = array('action' => 'test<', 'add_products_id' => 'alert();', 'attribute_id' => '&nbsp;', 'attribute_page' => '</script>');
        $arq = new AdminRequestSanitizer($adminSanitizationConfig, $adminSanitizerTypes, true);
        $arq->runSanitizers();
        $getAlreadySanitized = $arq->getGetKeysAlreadySanitized();
        $this->assertTrue(count($getAlreadySanitized) == 4);
        $this->assertTrue($_GET['action'] == 'test');
        $this->assertTrue($_GET['add_products_id'] == 'alert');
        $this->assertTrue($_GET['attribute_id'] == 'nbsp');
        $this->assertTrue($_GET['attribute_page'] == '/script');
    }

    public function testConvertInt()
    {
        $group = array(
            'action',
            'add_products_id',
            'attribute_id',
            'attribute_page');
        $adminSanitizationConfig['CONVERT_INT'] = $group;
        $adminSanitizerTypes = array('CONVERT_INT' => array('type' => 'builtin', 'strict' => false));

        $_GET = array('action' => '100', 'add_products_id' => 'alert();', 'attribute_id' => '&nbsp;', 'attribute_page' => '</script>');
        $_POST = array('action' => '100', 'add_products_id' => 'alert();', 'attribute_id' => '&nbsp;', 'attribute_page' => '</script>');
        $arq = new AdminRequestSanitizer($adminSanitizationConfig, $adminSanitizerTypes, true);
        $arq->runSanitizers();
        $getAlreadySanitized = $arq->getGetKeysAlreadySanitized();
        $this->assertTrue(count($getAlreadySanitized) == 4);
        $postAlreadySanitized = $arq->getPostKeysAlreadySanitized();
        $this->assertTrue(count($postAlreadySanitized) == 4);
        $this->assertTrue($_GET['action'] == 100);
        $this->assertTrue($_GET['add_products_id'] == 0);
        $this->assertTrue($_GET['attribute_id'] == 0);
        $this->assertTrue($_GET['attribute_page'] == 0);
        $this->assertTrue($_POST['action'] == 100);
        $this->assertTrue($_POST['add_products_id'] == 0);
        $this->assertTrue($_POST['attribute_id'] == 0);
        $this->assertTrue($_POST['attribute_page'] == 0);
    }

    public function testFileDirRegex()
    {
        $group = array(
            'img_dir_safe',
            'img_dir_not_safe'
            );
        $adminSanitizationConfig['FILE_DIR_REGEX'] = $group;
        $adminSanitizerTypes = array('FILE_DIR_REGEX' => array('type' => 'builtin', 'strict' => false));

        $_POST = array('img_dir_safe' => '100', 'img_dir_not_safe' => 'alert();');
        $arq = new AdminRequestSanitizer($adminSanitizationConfig, $adminSanitizerTypes, true);
        $arq->runSanitizers();
        $postAlreadySanitized = $arq->getPostKeysAlreadySanitized();
        $this->assertTrue(count($postAlreadySanitized) == 2);
        $this->assertTrue($_POST['img_dir_safe'] == 100);
        $this->assertTrue($_POST['img_dir_not_safe'] === 'alert()');
    }

    public function testAlphaNumDashUnderScore()
    {
        $group = array(
            'action_safe',
            'action_not_safe'
        );
        $adminSanitizationConfig['ALPHANUM_DASH_UNDERSCORE'] = $group;
        $adminSanitizerTypes = array('ALPHANUM_DASH_UNDERSCORE' => array('type' => 'builtin', 'strict' => false));

        $_POST = array('action_safe' => '100xyz_-', 'action_not_safe' => '100xyz_</script>();');
        $arq = new AdminRequestSanitizer($adminSanitizationConfig, $adminSanitizerTypes, true);
        $arq->runSanitizers();
        $postAlreadySanitized = $arq->getPostKeysAlreadySanitized();
        $this->assertTrue(count($postAlreadySanitized) == 2);
        $this->assertTrue($_POST['action_safe'] == '100xyz_-');
        $this->assertTrue($_POST['action_not_safe'] === '100xyz_script');
    }

    public function testMetaTags()
    {
        $group = array(
            'metatags_title_safe',
            'metatags_title_not_safe'
        );
        $adminSanitizationConfig['META_TAGS'] = $group;
        $adminSanitizerTypes = array('META_TAGS' => array('type' => 'builtin', 'strict' => false));

        $_POST = array('metatags_title_safe' => array('100xyz_-'), 'metatags_title_not_safe' => array('100xyz_</script>();'));

        $arq = new AdminRequestSanitizer($adminSanitizationConfig, $adminSanitizerTypes, true);
        $arq->runSanitizers();
        $postAlreadySanitized = $arq->getPostKeysAlreadySanitized();
        $this->assertTrue(count($postAlreadySanitized) == 2);
        $this->assertTrue($_POST['metatags_title_safe'][0] == '100xyz_-');
        $this->assertTrue($_POST['metatags_title_not_safe'][0] == '100xyz_&lt;/script&gt;();');
    }
    public function testSanitizeEmail()
    {
        $group = array(
            'customers_email_address_safe',
            'customers_email_address_not_safe'
        );
        $adminSanitizationConfig['SANITIZE_EMAIL'] = $group;
        $adminSanitizerTypes = array('SANITIZE_EMAIL' => array('type' => 'builtin', 'strict' => false));

        $_POST = array('customers_email_address_safe' => 'xyz@domain.com', 'customers_email_address_not_safe' => '100xyz_</script>();');
        $arq = new AdminRequestSanitizer($adminSanitizationConfig, $adminSanitizerTypes, true);
        $arq->runSanitizers();
        $postAlreadySanitized = $arq->getPostKeysAlreadySanitized();
        $this->assertTrue(count($postAlreadySanitized) == 2);
        $this->assertTrue($_POST['customers_email_address_safe'] == 'xyz@domain.com');
        $this->assertTrue($_POST['customers_email_address_not_safe'] === '100xyz_script');
    }

    public function testProductDescRegex()
    {
        $group = array(
            'products_description_safe',
            'products_description_not_safe'
        );
        $adminSanitizationConfig['PRODUCT_DESC_REGEX'] = $group;
        $adminSanitizerTypes = array('PRODUCT_DESC_REGEX' => array('type' => 'builtin', 'strict' => false));

        $_POST = array('products_description_safe' => array('xyz@domain.com'), 'products_description_not_safe' => array('100xyz_</script>();'));
        $arq = new AdminRequestSanitizer($adminSanitizationConfig, $adminSanitizerTypes, true);
        $arq->runSanitizers();
        $postAlreadySanitized = $arq->getPostKeysAlreadySanitized();
        $this->assertTrue(count($postAlreadySanitized) == 2);
        $this->assertTrue($_POST['products_description_safe'][0] == 'xyz@domain.com');
        $this->assertTrue($_POST['products_description_not_safe'][0] === '100xyz_</script>();');
    }
    public function testProductUrlRegex()
    {
        $group = array(
            'products_url_safe',
            'products_url_not_safe'
        );
        $adminSanitizationConfig['PRODUCT_URL_REGEX'] = $group;
        $adminSanitizerTypes = array('PRODUCT_URL_REGEX' => array('type' => 'builtin', 'strict' => false));
        $_POST = array('products_url_safe' => array('100xyz_</script>();'), 'products_url_not_safe' => array('100xyz_</script>();££'));
        $arq = new AdminRequestSanitizer($adminSanitizationConfig, $adminSanitizerTypes, true);
        $arq->runSanitizers();
        $postAlreadySanitized = $arq->getPostKeysAlreadySanitized();
        $this->assertTrue(count($postAlreadySanitized) == 2);
        $this->assertTrue($_POST['products_url_safe'][0] == '100xyz_</script>();');
        $this->assertTrue($_POST['products_url_not_safe'][0] === '100xyz_</script>();');
    }
    public function testCurrencyValueRegex()
    {
        $group = array(
            'currency_value_safe',
            'currency_value_not_safe'
        );
        $adminSanitizationConfig['CURRENCY_VALUE_REGEX'] = $group;
        $adminSanitizerTypes = array('CURRENCY_VALUE_REGEX' => array('type' => 'builtin', 'strict' => false));

        $_POST = array('currency_value_safe' => '-10,000.00', 'currency_value_not_safe' => '-10000.00alert();');
        $arq = new AdminRequestSanitizer($adminSanitizationConfig, $adminSanitizerTypes, true);
        $arq->runSanitizers();
        $postAlreadySanitized = $arq->getPostKeysAlreadySanitized();
        $this->assertTrue(count($postAlreadySanitized) == 2);
        $this->assertTrue($_POST['currency_value_safe'] == '-10,000.00');
        $this->assertTrue($_POST['currency_value_not_safe'] == '-10000.00alert');
    }

    public function testProductNameDeepRegex()
    {
        $group = array(
            'products_name_safe',
            'products_name_not_safe'
        );
        $adminSanitizationConfig['PRODUCT_NAME_DEEP_REGEX'] = $group;
        $adminSanitizerTypes = array('PRODUCT_NAME_DEEP_REGEX' => array('type' => 'builtin', 'strict' => false));

        $_POST = array('products_name_safe' => array('<strong>Name</strong>'), 'products_name_not_safe' => array('100xyz_</script>();'));
        $arq = new AdminRequestSanitizer($adminSanitizationConfig, $adminSanitizerTypes, true);
        $arq->runSanitizers();
        $postAlreadySanitized = $arq->getPostKeysAlreadySanitized();
        $this->assertTrue(count($postAlreadySanitized) == 2);
        $this->assertTrue($_POST['products_name_safe'][0] == '<strong>Name</strong>');
        $this->assertTrue($_POST['products_name_not_safe'][0] === '100xyz_pt>();');
    }

    public function testProductNameRegex()
    {
        $group = array(
            'products_name_safe',
            'products_name_not_safe'
        );
        $adminSanitizationConfig['PRODUCT_NAME_REGEX'] = $group;
        $adminSanitizerTypes = array('PRODUCT_NAME_REGEX' => array('type' => 'builtin', 'strict' => false));


        $_POST = array('products_name_safe' => '<strong>Name</strong>', 'products_name_not_safe' => '100xyz_</script>();');
        $arq = new AdminRequestSanitizer($adminSanitizationConfig, $adminSanitizerTypes, true);
        $arq->runSanitizers();
        $postAlreadySanitized = $arq->getPostKeysAlreadySanitized();
        $this->assertTrue(count($postAlreadySanitized) == 2);
        $this->assertTrue($_POST['products_name_safe'] == '<strong>Name</strong>');
        $this->assertTrue($_POST['products_name_not_safe'] === '100xyz_pt>();');
    }

}
