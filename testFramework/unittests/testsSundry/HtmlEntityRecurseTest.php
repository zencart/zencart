<?php
/**
 * @package tests
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id$
 */

/**
 * Class testHtmlEntityRecurse
 */
class testHtmlEntityRecurse extends zcTestCase
{
    /**
     *
     */
    public function setup()
    {
        parent::setup();
        require DIR_FS_CATALOG . 'includes/functions/functions_general.php';
    }

    /**
     *
     */
    public function testRecurse()
    {
        $test = "<script>";
        $result = htmlentities_recurse($test);
        $this->assertEquals($result, '&lt;script&gt;');
        $test = array('value' => "<script>");
        $result = htmlentities_recurse($test);
        $this->assertEquals($result['value'], '&lt;script&gt;');
        $test = array(array('value'=>"<script>", 'value1' =>"<script>"), 'value' => "<script>");
        $result = htmlentities_recurse($test);
        $this->assertEquals($result[0]['value'], '&lt;script&gt;');
        $this->assertEquals($result[0]['value1'], '&lt;script&gt;');
        $this->assertEquals($result['value'], '&lt;script&gt;');
    }
}
