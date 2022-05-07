<?php

namespace Tests\Browser;


use Laravel\Dusk\Browser;
use Tests\Browser\Traits\ConfigureFileConcerns;
use Tests\Browser\Traits\DatabaseConcerns;

abstract class AdminDuskTestCase extends DuskTestCase
{
    use DatabaseConcerns;
    use ConfigureFileConcerns;

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
