<?php

namespace Tests\Browser;

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
                ->type('#db_host', DB_SERVER)
                ->type('#db_user', DB_SERVER_USERNAME)
                ->type('#db_password', DB_SERVER_PASSWORD)
                ->type('#db_name', DB_DATABASE)
                ->click('#btnsubmit')
                ->waitFor('#admin_user', 1000)
                ->type('#admin_user', ADMIN_NAME)
                ->type('#admin_email', ADMIN_EMAIL)
                ->type('#admin_email2', ADMIN_EMAIL)
                ->click('#btnsubmit')
                ->assertSee('Setup Complete');
        });
    }
}
