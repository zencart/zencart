<?php
/**
 *
 * @package tests
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id$
 */
require_once(__DIR__ . '/../support/zcTestCase.php');

/**
 * Testing Library
 */
class testAllDefaultCase extends zcTestCase
{
    public function setup()
    {
        parent::setup();
        require_once DIR_FS_CATALOG . 'includes/functions/functions_general.php';
        require_once DIR_FS_CATALOG . DIR_WS_CLASSES . 'db/mysql/query_factory.php';
        if (!defined('PRODUCT_LISTING_MULTIPLE_ADD_TO_CART')) {
            define('PRODUCT_LISTING_MULTIPLE_ADD_TO_CART', 0);
        }
        define('STORE_STATUS', 0);
        define('CUSTOMERS_APPROVAL', '');
        define('CUSTOMERS_APPROVAL_AUTHORIZATION', '');
        define('PRODUCT_ALL_LIST_SORT_DEFAULT', 0);
        define('MAX_DISPLAY_PRODUCTS_ALL', 0);
        define('IMAGE_PRODUCT_ALL_LISTING_WIDTH', '');
        define('IMAGE_PRODUCT_ALL_LISTING_HEIGHT', '');
        define('TABLE_HEADING_ALL_PRODUCTS', '');
        define('PRODUCT_LIST_ALPHA_SORTER', 1);
        define('PRODUCT_LIST_ALPHA_SORTER_LIST', 'A - C:A,B,C;D - F:D,E,F;G - I:G,H,I;J - L:J,K,L;M - N:M,N;O - Q:O,P,Q;R - T:R,S,T;U - W:U,V,W;X - Z:X,Y,Z;#:0,1,2,3,4,5,6,7,8,9');
        define('EXCLUDE_ADMIN_IP_FOR_MAINTENANCE', '');
        define('DOWN_FOR_MAINTENANCE', '');
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $GLOBALS['new_products_category_id'] = 1;
        $_SESSION['languages_id'] = 1;
        $_SESSION['customer_id'] = 1;
        $_SESSION['customers_authorization'] = 1;
        $loader = new \Aura\Autoload\Loader;
        $loader->register();
        $loader->addPrefix('\ZenCart\QueryBuilderDefinitions', DIR_CATALOG_LIBRARY . 'zencart/QueryBuilderDefinitions/src');
        $loader->addPrefix('\Aura\Web', DIR_CATALOG_LIBRARY . 'aura/web/src');
        $loader->addPrefix('\Aura\Di', DIR_CATALOG_LIBRARY . 'aura/AuraDi/src');
        $loader->addPrefix('\ZenCart\Request', DIR_CATALOG_LIBRARY . 'zencart/Request/src');
//        require_once DIR_FS_CATALOG . 'includes/diConfigs/AuraWeb.php';
//        $config = new AuraWeb();
//        $builder = new \Aura\Di\ContainerBuilder();
//        $di = $builder->newConfiguredInstance(array($config));
    }

    public function testInstantiate()
    {
        $scroller = $this->getMock('paginator', array('getResults'));
        $scroller->method('getResults')->willReturn(array('resultList' => array()));
        $paginator = $this->getMock('paginator', array('doPagination', 'getScroller'));
        $paginator->method('getScroller')->willReturn($scroller);
        $r = $this->getMockBuilder('\ZenCart\Request\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $qfr = $this->getMockBuilder('queryFactoryResult')
            ->disableOriginalConstructor()
            ->getMock();
        $db = $this->getMockBuilder('queryFactory')
            ->getMock();
        $db->method('Execute')->willReturn($qfr);
        $GLOBALS['db'] = $db;
        $qb = $this->getMockBuilder('queryBuilder')
            ->disableOriginalConstructor()
            ->setMethods(array('processQuery', 'getQuery'))
            ->getMock();
        $qb->method('getQuery')->willReturn(array('mainSql' => '', 'countSql' => ''));
        $lb = new \ZenCart\QueryBuilderDefinitions\definitions\AllProductsPage($r, $db);
        $lb->buildResults($qb, $db, $paginator);
    }
}
