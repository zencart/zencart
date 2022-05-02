<?php

namespace Tests\Browser;

use Tests\Browser\Pages\zcInstallPage;
use Laravel\Dusk\Browser;

class ZcInstallTest extends InstallDuskTestCase
{
    /** @test */
    public function do_a_full_install()
    {
        $this->browse(function (Browser $browser) {
            $browser->resize(1920, 1080);
            $browser->visit(HTTP_SERVER . '/zc_install/')
                ->assertSee('System Inspection')
                ->click('#btnsubmit')
                ->assertSee('Admin Settings')
                ->check('#agreeLicense')
                ->click('#btnsubmit')
                ->type('#db_host', TESTING_DB_SERVER)
                ->type('#db_user', TESTING_DB_SERVER_USERNAME)
                ->type('#db_password', TESTING_DB_SERVER_PASSWORD)
                ->type('#db_name', TESTING_DB_DATABASE)
                ->click('#btnsubmit')
                ->waitFor('#admin_user', 1000)
                ->type('#admin_user', ADMIN_NAME)
                ->type('#admin_email', ADMIN_EMAIL)
                ->type('#admin_email2', ADMIN_EMAIL)
                ->click('#btnsubmit')
                ->assertSee('Setup Complete')
            ;
        });
    }
}
