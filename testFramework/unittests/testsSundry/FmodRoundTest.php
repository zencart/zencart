<?php
/**
 * @package tests
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id$
 */
require_once(__DIR__ . '/../support/zcTestCase.php');

class testlFmodRound extends zcTestCase
{
    public function setup()
    {
        parent::setup();
        require DIR_FS_CATALOG . 'includes/functions/functions_general.php';
    }

    /**
     * @dataProvider fmodRoundProvider
     */
    public function testFmodRound($a, $b, $expected)
    {
        $this->assertEquals($expected, fmod_round($a, $b));
    }

    public function fmodRoundProvider()
    {
        return array(
            array(0.01, 0.01, 0),
            array(0.02, 0.02, 0),
            array(0.0003, 0.0003, 0),
            array(0.1, 0.1, 0),
            array(0.2, 0.2, 0),
            array(0.3, 0.3, 0),
            array(14, 0.00007, 0),
            array(517.8, 17.26, 0),
            array(400000, 800, 0),
            array(7.3, 0.2, 0.1),
            array(517.8, 17.27, .3),
        );
    }

}
