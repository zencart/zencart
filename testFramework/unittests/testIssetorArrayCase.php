<?php
/**
 * File contains tests for issetorArray function
 *
 * @package tests
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id$
 */
require_once('support/zcCatalogTestCase.php');
/**
 * Unit Tests for password hashing rules
 */
class testIssetorArrayCase extends zcCatalogTestCase
{
    public function testIssetor()
    {
        $somearray = [];
        $result = issetorArray($somearray, 'key', 'default');
        $this->assertTrue($result == 'default');
        $somearray = array('key'=>'notdefault');
        $result = issetorArray($somearray, 'key', 'default');
        $this->assertTrue($result == 'notdefault');
    }
}
