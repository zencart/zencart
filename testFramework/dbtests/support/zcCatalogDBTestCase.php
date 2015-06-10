<?php
/**
 * File contains framework test cases
 *
 * @package tests
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id$
 */

require_once('zcDBTestCase.php');

if (!defined('IS_ADMIN_FLAG')) {
    define('IS_ADMIN_FLAG', false);
}

/**
 * Testing Library
 */
abstract class zcCatalogDBTestCase extends zcTestCase
{
}
