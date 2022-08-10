<?php
/**
* @copyright Copyright 2003-2020 Zen Cart Development Team
* @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
*/

use App\Models\Admin;
use Tests\Support\Traits\DatabaseConcerns;
use Tests\Support\zcUnitTestCase;

class DatabaseTest extends zcUnitTestCase
{
    use DatabaseConcerns;

    public $databaseFixtures = ['adminEmpty'];

    public function testExample()
    {
        $f = Admin::all();
        $this->assertTrue(!count($f));
        $f = $this->db->Execute('SELECT * FROM ' . TABLE_ADMIN);
        $this->assertTrue(!$f->count());
    }

}
