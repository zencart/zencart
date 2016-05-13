<?php

/**
 * File contains tests for Admin Sanitization
 *
 * @package tests
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Author: zcwilt Wed May 1116:59:30 2016 +0000 New in v1.5.5 $
 */

/**
 * Class testAdminSanitization
 */
class testAdminSanitization extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        if (!defined('DIR_FS_CATALOG')) {
            define('DIR_FS_CATALOG', realpath(dirname(__FILE__) . '/../../../'));
        }
        if (!defined('DIR_WS_CLASSES')) {
            define('DIR_WS_CLASSES', '/admin/includes/classes/');
        }
        if (!defined('DIR_FS_SQL_CACHE')) {
            define('DIR_FS_SQL_CACHE', DIR_FS_CATALOG);
        }
        require_once(DIR_FS_CATALOG . '/includes/classes/class.base.php');
        require_once(DIR_FS_CATALOG . DIR_WS_CLASSES . 'AdminRequestSanitizer.php');
    }

    public function testInstanceInstantitation()
    {
        $arq = AdminRequestSanitizer::getInstance();
        $getAlreadySanitized = $arq->getGetKeysAlreadySanitized();
        $this->assertTrue(count($getAlreadySanitized) == 0);
    }

    public function testDebugInstantitation()
    {
        $arq = new AdminRequestSanitizer;
        $arq->setDebug(true);
        $getAlreadySanitized = $arq->getGetKeysAlreadySanitized();
        $this->assertTrue(count($getAlreadySanitized) == 0);
        $this->assertTrue($arq->getDebug() === true);
    }

    public function testSimpleAlphaNumPlus()
    {
        $arq = new AdminRequestSanitizer;
        $arq->setDebug(true);
        $group = array(
            'action_get',
            'add_products_id_get',
            'attribute_id_get',
            'attribute_page_get',
            'action_post',
            'add_products_id_post',
            'attribute_id_post',
            'attribute_page_post'
        );
        $adminSanitizerTypes = array('SIMPLE_ALPHANUM_PLUS' => array('type' => 'builtin'));
        $arq->addSanitizerTypes($adminSanitizerTypes);
        $arq->addSimpleSanitization('SIMPLE_ALPHANUM_PLUS', $group);
        $arq->runSanitizers();
        $_GET = array(
            'action_get' => 'test<',
            'add_products_id_get' => 'alert();',
            'attribute_id_get' => '&nbsp;',
            'attribute_page_get' => '</script>'
        );
        $_POST = array(
            'action_post' => 'test<',
            'add_products_id_post' => 'alert();',
            'attribute_id_post' => '&nbsp;',
            'attribute_page_post' => '</script>'
        );
        $arq->runSanitizers();
        $getAlreadySanitized = $arq->getGetKeysAlreadySanitized();
        $this->assertTrue(count($getAlreadySanitized) == 4);
        $this->assertTrue($_GET['action_get'] == 'test');
        $this->assertTrue($_GET['add_products_id_get'] == 'alert');
        $this->assertTrue($_GET['attribute_id_get'] == 'nbsp');
        $this->assertTrue($_GET['attribute_page_get'] == '/script');
        $postAlreadySanitized = $arq->getPostKeysAlreadySanitized();
        $this->assertTrue(count($postAlreadySanitized) == 4);
        $this->assertTrue($_POST['action_post'] == 'test');
        $this->assertTrue($_POST['add_products_id_post'] == 'alert');
        $this->assertTrue($_POST['attribute_id_post'] == 'nbsp');
        $this->assertTrue($_POST['attribute_page_post'] == '/script');
    }

    public function testConvertInt()
    {
        $arq = new AdminRequestSanitizer;
        $group = array(
            'action',
            'add_products_id',
            'attribute_id',
            'attribute_page'
        );
        $adminSanitizerTypes = array('CONVERT_INT' => array('type' => 'builtin'));
        $arq->addSanitizerTypes($adminSanitizerTypes);
        $arq->addSimpleSanitization('CONVERT_INT', $group);
        $arq->runSanitizers();
        $group = array(
            'id' => array('sanitizerType' => 'CONVERT_INT', 'method' => 'both', 'pages' => array('edit_orders'))
        );
        $arq->addComplexSanitization($group);

        $_GET = array(
            'id' => '1k',
            'action' => '100',
            'add_products_id' => 'alert();',
            'attribute_id' => '&nbsp;',
            'attribute_page' => '</script>'
        );
        $_POST = array(
            'id' => '1k',
            'action' => '100',
            'add_products_id' => 'alert();',
            'attribute_id' => '&nbsp;',
            'attribute_page' => '</script>'
        );

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
        $arq = new AdminRequestSanitizer;
        $group = array(
            'img_dir_safe',
            'img_dir_not_safe',
            'img_dir_windows',
            'img_dir_linux',
            'img_dir_linux_space'
        );
        $adminSanitizerTypes = array('FILE_DIR_REGEX' => array('type' => 'builtin'));
        $arq->addSanitizerTypes($adminSanitizerTypes);
        $arq->addSimpleSanitization('FILE_DIR_REGEX', $group);
        $_POST = array(
            'img_dir_safe' => '100',
            'img_dir_not_safe' => 'alert();',
            'img_dir_windows' => 'matrox\matrox.gif',
            'img_dir_linux' => 'matrox/matrox.gif',
            'img_dir_linux_space' => 'mat rox/matrox.gif'
        );
        $arq->runSanitizers();
        $postAlreadySanitized = $arq->getPostKeysAlreadySanitized();
        $this->assertTrue(count($postAlreadySanitized) == 5);
        $this->assertTrue($_POST['img_dir_safe'] == 100);
        $this->assertTrue($_POST['img_dir_not_safe'] === 'alert()');
        $this->assertTrue($_POST['img_dir_windows'] == 'matrox\matrox.gif');
        $this->assertTrue($_POST['img_dir_linux'] === 'matrox/matrox.gif');
        $this->assertTrue($_POST['img_dir_linux_space'] === 'mat rox/matrox.gif');
    }

    public function testAlphaNumDashUnderScore()
    {
        $arq = new AdminRequestSanitizer;
        $group = array(
            'action_safe_post',
            'action_not_safe_post',
            'action_safe_get',
            'action_not_safe_get'
        );
        $adminSanitizerTypes = array('ALPHANUM_DASH_UNDERSCORE' => array('type' => 'builtin'));
        $arq->addSanitizerTypes($adminSanitizerTypes);
        $arq->addSimpleSanitization('ALPHANUM_DASH_UNDERSCORE', $group);

        $_POST = array('action_safe_post' => '100xyz_-', 'action_not_safe_post' => '100xyz_</script>();');
        $_GET = array('action_safe_get' => '100xyz_-', 'action_not_safe_get' => '100xyz_</script>();');
        $arq->runSanitizers();
        $postAlreadySanitized = $arq->getPostKeysAlreadySanitized();
        $this->assertTrue(count($postAlreadySanitized) == 2);
        $this->assertTrue($_POST['action_safe_post'] == '100xyz_-');
        $this->assertTrue($_POST['action_not_safe_post'] === '100xyz_script');
        $getAlreadySanitized = $arq->getGetKeysAlreadySanitized();
        $this->assertTrue(count($getAlreadySanitized) == 2);
        $this->assertTrue($_GET['action_safe_get'] == '100xyz_-');
        $this->assertTrue($_GET['action_not_safe_get'] === '100xyz_script');
    }

    public function testMetaTags()
    {
        $arq = new AdminRequestSanitizer;
        $group = array(
            'metatags_title_safe',
            'metatags_title_not_safe'
        );
        $adminSanitizerTypes = array('META_TAGS' => array('type' => 'builtin'));
        $arq->addSanitizerTypes($adminSanitizerTypes);
        $arq->addSimpleSanitization('META_TAGS', $group);

        $_POST = array('metatags_title_safe' => array('100xyz_-'), 'metatags_title_not_safe' => array('100xyz_</script>();'));

        $arq->runSanitizers();
        $postAlreadySanitized = $arq->getPostKeysAlreadySanitized();
        $this->assertTrue(count($postAlreadySanitized) == 2);
        $this->assertTrue($_POST['metatags_title_safe'][0] == '100xyz_-');
        $this->assertTrue($_POST['metatags_title_not_safe'][0] == '100xyz_&lt;/script&gt;();');
    }

    public function testSanitizeEmail()
    {
        $arq = new AdminRequestSanitizer;
        $group = array(
            'customers_email_address_safe_post',
            'customers_email_address_not_safe_post',
            'customers_email_address_safe_get',
            'customers_email_address_not_safe_get'
        );
        $adminSanitizerTypes = array('SANITIZE_EMAIL' => array('type' => 'builtin'));
        $arq->addSanitizerTypes($adminSanitizerTypes);
        $arq->addSimpleSanitization('SANITIZE_EMAIL', $group);

        $_POST = array(
            'customers_email_address_safe_post' => 'xyz@domain.com',
            'customers_email_address_not_safe_post' => '100xyz_</script>();'
        );
        $_GET = array(
            'customers_email_address_safe_get' => 'xyz@domain.com',
            'customers_email_address_not_safe_get' => '100xyz_</script>();'
        );
        $arq->runSanitizers();
        $postAlreadySanitized = $arq->getPostKeysAlreadySanitized();
        $this->assertTrue(count($postAlreadySanitized) == 2);
        $this->assertTrue($_POST['customers_email_address_safe_post'] == 'xyz@domain.com');
        $this->assertTrue($_POST['customers_email_address_not_safe_post'] === '100xyz_script');
        $getAlreadySanitized = $arq->getGetKeysAlreadySanitized();
        $this->assertTrue(count($getAlreadySanitized) == 2);
        $this->assertTrue($_GET['customers_email_address_safe_get'] == 'xyz@domain.com');
        $this->assertTrue($_GET['customers_email_address_not_safe_get'] === '100xyz_script');
    }

    public function testProductDescRegex()
    {
        $arq = new AdminRequestSanitizer;
        $group = array(
            'products_description_safe_deep',
            'products_description_not_safe_deep',
            'products_description_safe',
            'products_description_not_safe'
        );
        $adminSanitizerTypes = array('PRODUCT_DESC_REGEX' => array('type' => 'builtin'));
        $arq->addSanitizerTypes($adminSanitizerTypes);
        $arq->addSimpleSanitization('PRODUCT_DESC_REGEX', $group);

        $_POST = array(
            'products_description_safe' => 'xyz@domain.com',
            'products_description_not_safe' => '100xyz_</script>();',
            'products_description_safe_deep' => array('xyz@domain.com'),
            'products_description_not_safe_deep' => array('100xyz_</script>();')
        );
        $arq->runSanitizers();
        $postAlreadySanitized = $arq->getPostKeysAlreadySanitized();
        $this->assertTrue(count($postAlreadySanitized) == 4);
        $this->assertTrue($_POST['products_description_safe_deep'][0] == 'xyz@domain.com');
        $this->assertTrue($_POST['products_description_not_safe_deep'][0] === '100xyz_</script>();');
        $this->assertTrue($_POST['products_description_safe'] == 'xyz@domain.com');
        $this->assertTrue($_POST['products_description_not_safe'] === '100xyz_</script>();');
    }

    public function testProductUrlRegex()
    {
        $arq = new AdminRequestSanitizer;
        $group = array(
            'products_url_safe',
            'products_url_not_safe'
        );
        $adminSanitizerTypes = array('PRODUCT_URL_REGEX' => array('type' => 'builtin'));
        $arq->addSanitizerTypes($adminSanitizerTypes);
        $arq->addSimpleSanitization('PRODUCT_URL_REGEX', $group);
        $_POST = array(
            'products_url_safe' => array('100xyz_</script>();'),
            'products_url_not_safe' => array('100xyz_</script>();££')
        );
        $arq->runSanitizers();
        $postAlreadySanitized = $arq->getPostKeysAlreadySanitized();
        $this->assertTrue(count($postAlreadySanitized) == 2);
        $this->assertTrue($_POST['products_url_safe'][0] == '100xyz_</script>();');
        $this->assertTrue($_POST['products_url_not_safe'][0] === '100xyz_</script>();');
    }

    public function testCurrencyValueRegex()
    {
        $arq = new AdminRequestSanitizer;
        $group = array(
            'currency_value_safe',
            'currency_value_not_safe'
        );
        $adminSanitizerTypes = array('CURRENCY_VALUE_REGEX' => array('type' => 'builtin'));
        $arq->addSanitizerTypes($adminSanitizerTypes);
        $arq->addSimpleSanitization('CURRENCY_VALUE_REGEX', $group);

        $_POST = array('currency_value_safe' => '-10,000.00', 'currency_value_not_safe' => '-10000.00alert();');
        $arq->runSanitizers();
        $postAlreadySanitized = $arq->getPostKeysAlreadySanitized();
        $this->assertTrue(count($postAlreadySanitized) == 2);
        $this->assertTrue($_POST['currency_value_safe'] == '-10,000.00');
        $this->assertTrue($_POST['currency_value_not_safe'] == '-10000.00alert');
    }

    public function testFloatValueRegex()
    {
        $arq = new AdminRequestSanitizer;
        $group = array(
            'float_value_safe',
            'float_value_not_safe'
        );
        $adminSanitizerTypes = array('FLOAT_VALUE_REGEX' => array('type' => 'builtin'));
        $arq->addSanitizerTypes($adminSanitizerTypes);
        $arq->addSimpleSanitization('FLOAT_VALUE_REGEX', $group);

        $_POST = array('float_value_safe' => '-10,000.00', 'float_value_not_safe' => '+10.000,00alert();');
        $arq->runSanitizers();
        $postAlreadySanitized = $arq->getPostKeysAlreadySanitized();
        $this->assertTrue(count($postAlreadySanitized) == 2);
        $this->assertTrue($_POST['float_value_safe'] == '-10,000.00');
        $this->assertTrue($_POST['float_value_not_safe'] == '+10.000,00');
    }

    public function testProductNameDeepRegex()
    {
        $arq = new AdminRequestSanitizer;
        $group = array(
            'products_name_safe',
            'products_name_not_safe'
        );
        $adminSanitizerTypes = array('PRODUCT_NAME_DEEP_REGEX' => array('type' => 'builtin'));
        $arq->addSanitizerTypes($adminSanitizerTypes);
        $arq->addSimpleSanitization('PRODUCT_NAME_DEEP_REGEX', $group);

        $_POST = array(
            'products_name_safe' => array('<strong>Name</strong>'),
            'products_name_not_safe' => array('100xyz_</script>();')
        );
        $arq->runSanitizers();
        $postAlreadySanitized = $arq->getPostKeysAlreadySanitized();
        $this->assertTrue(count($postAlreadySanitized) == 2);
        $this->assertTrue($_POST['products_name_safe'][0] == '<strong>Name</strong>');
        $this->assertTrue($_POST['products_name_not_safe'][0] === '100xyz_pt>();');
    }

    public function testWordsAndSymbolsRegex()
    {
        $arq = new AdminRequestSanitizer;
        $group = array(
            'products_name_safe_post',
            'products_name_not_safe_post',
            'products_name_safe_get',
            'products_name_not_safe_get'
        );
        $adminSanitizerTypes = array('WORDS_AND_SYMBOLS_REGEX' => array('type' => 'builtin'));
        $arq->addSanitizerTypes($adminSanitizerTypes);
        $arq->addSimpleSanitization('WORDS_AND_SYMBOLS_REGEX', $group);

        $_GET = array('products_name_safe_get' => '<strong>Name</strong>', 'products_name_not_safe_get' => '100xyz_</script>();');
        $_POST = array(
            'products_name_safe_post' => '<strong>Name</strong>',
            'products_name_not_safe_post' => '100xyz_</script>();'
        );
        $arq->runSanitizers();
        $postAlreadySanitized = $arq->getPostKeysAlreadySanitized();
        $this->assertTrue(count($postAlreadySanitized) == 2);
        $this->assertTrue($_POST['products_name_safe_post'] == '<strong>Name</strong>');
        $this->assertTrue($_POST['products_name_not_safe_post'] === '100xyz_pt>();');
        $getAlreadySanitized = $arq->getGetKeysAlreadySanitized();
        $this->assertTrue(count($getAlreadySanitized) == 2);
        $this->assertTrue($_GET['products_name_safe_get'] == '<strong>Name</strong>');
        $this->assertTrue($_GET['products_name_not_safe_get'] === '100xyz_pt>();');
    }

    public function testStrictSanitizeKeys()
    {
        $arq = new AdminRequestSanitizer;
        $_POST = array('some_post_OK' => '<strong>Name</strong>', 'some_pst_NOTOK<>' => '100xyz_</script>();');
        $_GET = array('some_get_OK' => '<strong>Name</strong>', 'some_get_NOTOK<>' => '100xyz_</script>();');
        $arq->setDoStrictSanitization(true);
        $arq->runSanitizers();
        $this->assertTrue(isset($_POST['some_post_OK']));
        $this->assertTrue(isset($_GET['some_get_OK']));
        $this->assertTrue(!isset($_POST['some_pst_NOTOK<>']));
        $this->assertTrue(!isset($_GET['some_get_NOTOK<>']));
    }

    public function testStrictSanitizeValues()
    {
        $arq = new AdminRequestSanitizer;
        $adminSanitizerTypes = array('STRICT_SANITIZE_VALUES' => array('type' => 'builtin'));
        $arq->addSanitizerTypes($adminSanitizerTypes);
        $group = array('some_param_ignore');
        $arq->addSimpleSanitization('STRICT_SANITIZE_VALUES', $group);

        $_POST = array(
            'some_param_ignore' => '<strong>Name</strong>',
            'some_param_simple' => '100xyz_</script>();',
            'some_param_array' => array('100xyz_</script>();'),
            'some_param_deep_array' => array(array('100xyz_</script>();'))
        );

        $arq->setDoStrictSanitization(true);
        $arq->runSanitizers();
        $postAlreadySanitized = $arq->getPostKeysAlreadySanitized();
        $this->assertTrue(count($postAlreadySanitized) == 4);
        $this->assertTrue($_POST['some_param_ignore'] == '<strong>Name</strong>');
        $this->assertTrue($_POST['some_param_simple'] == '100xyz_&lt;/script&gt;();');
        $this->assertTrue($_POST['some_param_array'][0] == '100xyz_&lt;/script&gt;();');
        $this->assertTrue($_POST['some_param_deep_array'][0][0] == '100xyz_&lt;/script&gt;();');
    }

    public function testMultiDimensional()
    {
        global $PHP_SELF;
        $PHP_SELF = 'edit_orders.php';
        $arq = new AdminRequestSanitizer;
        $group = array(
            'update_products' => array(
                'sanitizerType' => 'MULTI_DIMENSIONAL',
                'method' => 'post',
                'pages' => array('edit_orders'),
                'params' => array(
                    'update_products' => array('sanitizerType' => 'CONVERT_INT'),
                    'qty' => array('sanitizerType' => 'CONVERT_INT'),
                    'name' => array('sanitizerType' => 'WORDS_AND_SYMBOLS_REGEX'),
                    'onetime_charges' => array('sanitizerType' => 'CURRENCY_VALUE_REGEX'),
                    'attr' => array(
                        'sanitizerType' => 'MULTI_DIMENSIONAL',
                        'params' => array(
                            'attr' => array('sanitizerType' => 'CONVERT_INT'),
                            'value' => array('sanitizerType' => 'CONVERT_INT'),
                            'type' => array('sanitizerType' => 'CONVERT_INT')
                        )
                    ),
                    'model' => array('sanitizerType' => 'WORDS_AND_SYMBOLS_REGEX'),
                    'tax' => array('sanitizerType' => 'WORDS_AND_SYMBOLS_REGEX'),
                    'final_price' => array('sanitizerType' => 'WORDS_AND_SYMBOLS_REGEX'),
                )
            )
        );
        $adminSanitizerTypes = array(
            'MULTI_DIMENSIONAL' => array('type' => 'builtin'),
            'CONVERT_INT' => array('type' => 'builtin'),
            'WORDS_AND_SYMBOLS_REGEX' => array('type' => 'builtin'),
            'ALPHANUM_DASH_UNDERSCORE' => array('type' => 'builtin'),
            'CURRENCY_VALUE_REGEX' => array('type' => 'builtin'),
        );
        $arq->addSanitizerTypes($adminSanitizerTypes);
        $arq->addComplexSanitization($group);

        $_POST = array(
            'update_products' => array(
                array(
                    'name' => 'product_name1<script>',
                    'qty' => '5x',
                    'onetime_charges' => '1.00WZR',
                    'model' => 'model1',
                    'tax' => '1.00',
                    'final_price' => '1.00',
                    'attr' => array(array('value' => '1value1', 'type' => 1), array('value' => '2value2', 'type' => 2))
                ),
                array(
                    'name' => 'product_name2',
                    'qty' => '6',
                    'onetime_charges' => '2.00',
                    'model' => 'model2',
                    'tax' => '2.00',
                    'final_price' => '2.00'
                )
            ),
        );
        $arq->runSanitizers();
        $postAlreadySanitized = $arq->getPostKeysAlreadySanitized();
        $this->assertTrue(count($postAlreadySanitized) == 20);
        $this->assertTrue($_POST['update_products'][0]['name'] == 'product_name1pt>');
        $this->assertTrue($_POST['update_products'][0]['qty'] == '5');
        $this->assertTrue($_POST['update_products'][0]['attr'][0]['value'] == '1');
    }

    public function testMultiDimensionalLogError()
    {
        global $PHP_SELF;
        $PHP_SELF = 'edit_orders.php';
        $arq = new AdminRequestSanitizer;
        $group = array(
            'update_products' => array(
                'sanitizerType' => 'MULTI_DIMENSIONAL',
                'method' => 'post',
                'pages' => array('edit_orders'),
                'params' => array(
                    'update_products' => array('sanitizerType' => 'CONVERT_INT'),
                    'qty' => array('sanitizerType' => 'CONVERT_INT'),
                    'name' => array('sanitizerType' => 'WORDS_AND_SYMBOLS_REGEX'),
                    'onetime_charges' => array('sanitizerType' => 'CURRENCY_VALUE_REGEX'),
                    'attr' => array(
                        'sanitizerType' => 'MULTI_DIMENSIONAL',
                        'params' => array(
                            'attr' => array('sanitizerType' => 'CONVERT_INT'),
                            'value' => array('sanitizerType' => 'CONVERT_INT'),
                            'type' => array('sanitizerType' => 'CONVERT_INT')
                        )
                    ),
                    'model' => array('sanitizerType' => 'WORDS_AND_SYMBOLS_REGEX'),
                    'tax' => array('sanitizerType' => 'WORDS_AND_SYMBOLS_REGEX'),
                    'final_price' => array('sanitizerType' => 'WORDS_AND_SYMBOLS_REGEX'),
                )
            )
        );
        $adminSanitizerTypes = array(
            'MULTI_DIMENSIONAL' => array('type' => 'builtin'),
            'CONVERT_INT' => array('type' => 'builtin'),
            'WORDS_AND_SYMBOLS_REGEX' => array('type' => 'builtin'),
            'ALPHANUM_DASH_UNDERSCORE' => array('type' => 'builtin'),
            'CURRENCY_VALUE_REGEX' => array('type' => 'builtin'),
        );
        $arq->addSanitizerTypes($adminSanitizerTypes);
        $arq->addComplexSanitization($group);

        $_POST = array();
        $arq->runSanitizers();
    }



    public function testHasGetHasPost()
    {
        $arq = new AdminRequestSanitizer;
        $adminSanitizerTypes = array('CONVERT_INT' => array('type' => 'builtin'));
        $arq->addSanitizerTypes($adminSanitizerTypes);
        $group = array(
            'idg' => array('sanitizerType' => 'CONVERT_INT', 'method' => 'get', 'pages' => null)
        );
        $arq->addComplexSanitization($group);
        $group = array(
            'idp' => array('sanitizerType' => 'CONVERT_INT', 'method' => 'post', 'pages' => null)
        );
        $arq->addComplexSanitization($group);

        $_GET = array(
            'idg' => '1k',
        );
        $_POST = array(
            'idp' => '1k',
        );
        $arq->runSanitizers();

        $getAlreadySanitized = $arq->getGetKeysAlreadySanitized();
        $this->assertTrue(count($getAlreadySanitized) == 1);
        $this->assertTrue($_GET['idg'] == 1);
        $postAlreadySanitized = $arq->getPostKeysAlreadySanitized();
        $this->assertTrue(count($postAlreadySanitized) == 1);
        $this->assertTrue($_POST['idp'] == 1);

    }

    public function testNullAction()
    {
        $arq = new AdminRequestSanitizer;
        $group = array(
            'products_name_safe',
            'products_name_not_safe'
        );
        $adminSanitizerTypes = array('NULL_ACTION' => array('type' => 'builtin'));
        $arq->addSanitizerTypes($adminSanitizerTypes);
        $arq->addSimpleSanitization('NULL_ACTION', $group);
        $_GET = array(
            'products_name_safe' => '<strong>Name</strong>',
            'products_name_not_safe' => '100xyz_</script>();'
        );

        $_POST = array(
            'products_name_safe' => '<strong>Name</strong>',
            'products_name_not_safe' => '100xyz_</script>();'
        );
        $arq->runSanitizers();
        $postAlreadySanitized = $arq->getPostKeysAlreadySanitized();
        $this->assertTrue(count($postAlreadySanitized) == 2);
        $this->assertTrue($_POST['products_name_safe'] == '<strong>Name</strong>');
        $this->assertTrue($_POST['products_name_not_safe'] === '100xyz_</script>();');
        $getAlreadySanitized = $arq->getGetKeysAlreadySanitized();
        $this->assertTrue(count($getAlreadySanitized) == 2);
        $this->assertTrue($_GET['products_name_safe'] == '<strong>Name</strong>');
        $this->assertTrue($_GET['products_name_not_safe'] === '100xyz_</script>();');
    }

    public function testCustomFilter()
    {
        $arq = new AdminRequestSanitizer;
        $adminSanitizerTypes = array(
            'CUSTOM_TEST' => array(
                'type' => 'custom',
                'function' => function ($arq, $parameterName) {
                    if (isset($_POST[$parameterName])) {
                        $arq->setPostKeyAlreadySanitized($parameterName);
                        $_POST[$parameterName] = preg_replace('/[^\/ 0-9a-zA-Z_:@.-]/', '', $_POST[$parameterName]);
                    }
                    if (isset($_GET[$parameterName])) {
                        $arq->setGetKeyAlreadySanitized($parameterName);
                        $_GET[$parameterName] = preg_replace('/[^\/ 0-9a-zA-Z_:@.-]/', '', $_GET[$parameterName]);
                    }

                }
            )
        );
        $arq->addSanitizerTypes($adminSanitizerTypes);
        $group = array(
            'products_name_post',
            'products_name_get',
        );
        $arq->addSimpleSanitization('CUSTOM_TEST', $group);
        $_POST = array(
            'products_name_post' => '<strong>Name</strong>',
        );
        $_GET = array(
            'products_name_get' => '<strong>Name</strong>',
        );
        $arq->runSanitizers();
        $postAlreadySanitized = $arq->getPostKeysAlreadySanitized();
        $getAlreadySanitized = $arq->getGetKeysAlreadySanitized();
        $this->assertTrue(count($postAlreadySanitized) == 1);
        $this->assertTrue(count($getAlreadySanitized) == 1);
        $this->assertTrue($_POST['products_name_post'] == 'strongName/strong');
    }

}
