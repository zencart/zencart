<?php
/**
 * File contains framework test cases
 *
 * @package tests
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id$
 */
require_once('support/zcCatalogTestCase.php');
/**
 * Testing Library
 */
class testQueryFactory extends zcCatalogTestCase
{
    public function setup()
    {
        parent::setup();
        require_once(DIR_FS_CATALOG . DIR_WS_CLASSES . 'db/mysql/query_factory.php');

    }
    public function testEmptyResult()
    {

    }
}
