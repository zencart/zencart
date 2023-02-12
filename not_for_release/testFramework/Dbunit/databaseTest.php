[<?php
/**
* @copyright Copyright 2003-2020 Zen Cart Development Team
* @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
*/

use App\Models\Admin;
use Tests\Support\Traits\DatabaseConcerns;
use Tests\Support\zcDBTestCase;

class DatabaseTest extends zcDBTestCase
{
    use DatabaseConcerns;

    public $databaseFixtures = ['adminEmpty' => ['admin'], 'configurationGroup' => ['configuration_group']];

    public function testExample()
    {
        $f = Admin::all();
        $this->assertTrue(!count($f));
        $f = $this->db->Execute('SELECT * FROM ' . TABLE_ADMIN);
        $this->assertTrue(!$f->count());
    }

    public function testZenGetConfigurationGroupValue()
    {
        require(DIR_FS_ADMIN . 'includes/functions/general.php');
        $result = zen_get_configuration_group_value(1);
        $this->assertEquals('test-group-title', $result);
        $result = zen_get_configuration_group_value(9);
        $this->assertEquals(9, $result);
    }
}
