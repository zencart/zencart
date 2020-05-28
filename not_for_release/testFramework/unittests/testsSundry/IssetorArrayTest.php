<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */
require_once(__DIR__ . '/../support/zcTestCase.php');

class IssetorArrayTest extends zcTestCase
{
    public function setup(): void
    {
        parent::setup();
        require_once DIR_FS_CATALOG . 'includes/functions/functions_general_shared.php';
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
