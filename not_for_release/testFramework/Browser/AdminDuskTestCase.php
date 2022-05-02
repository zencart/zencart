<?php

namespace Tests\Browser;


use Laravel\Dusk\Browser;

abstract class AdminDuskTestCase extends DuskTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->loadDuskConfigure();
        $this->createInitialConfigures();

        $this->createDatabase();
        $this->populateDatabase();
        $this->createDummyAdminUser();
        $this->pdoConnection = null;
    }

    public function doLogin(Browser $browser)
    {
        $browser->waitFor('#admin_pass')
            ->type('admin_name', 'Admin')
            ->type('#admin_pass', 'develop1')
            ->press('Submit');
    }
}
