<?php
/**
 * File contains zcRequest test cases
 *
 * @package tests
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id$
 */
use ZenCart\Platform\Paginator\HelperFactory;
require_once(__DIR__ . '/../support/zcPaginatorTestCase.php');

/**
 * Testing Library
 */
class testPaginationHelperFactoryCase extends zcPaginatorTestCase
{
    public function setUp()
    {
        parent::setUp();
        require DIR_CATALOG_LIBRARY . 'aura/autoload/src/Loader.php';
        $loader = new \Aura\Autoload\Loader;
        $loader->register();
        $loader->addPrefix('\ZenCart\Platform', DIR_CATALOG_LIBRARY . 'zencart/platform/src');
    }

    public function testMakeDataSource()
    {
        $ds = HelperFactory::makeDataSource('mysqli', array(), array());
    }
    public function testMakScroller()
    {
        $ds = HelperFactory::makeScroller('standard', array(), array());
    }
}
