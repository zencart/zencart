<?php

namespace Tests\Browser;


use Laravel\Dusk\Browser;

abstract class AdminDuskTestCase extends DuskTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->createInitialConfigures();

        if (!$this->hasDatabase()) {
            $this->createDatabase();
            $this->populateDatabase();
        }
        $this->createDummyAdminUser();
        $this->pdoConnection = null;
    }

    public function doLogin(Browser $browser)
    {
//        $browser->screenshot('admin-login');
        $browser->waitFor('#admin_pass')
            ->type('admin_name', 'Admin')
            ->type('#admin_pass', 'develop1')
            ->press('Submit');
    }
}
