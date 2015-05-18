<?php
/**
 * @package tests
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id$
 */
require_once(__DIR__ . '/../support/zcTestCase.php');

/**
 * Unit Tests for password hashing rules
 */
class testIssetorArray extends zcTestCase
{
    public function setup()
    {
        parent::setup();
        require DIR_FS_CATALOG . 'includes/functions/functions_general.php';
    }

    public function testIssetor()
    {
        $somearray = [];
        $result = issetorArray($somearray, 'key', 'default');
        $this->assertTrue($result == 'default');
        $somearray = array('key' => 'notdefault');
        $result = issetorArray($somearray, 'key', 'default');
        $this->assertTrue($result == 'notdefault');
    }
}
