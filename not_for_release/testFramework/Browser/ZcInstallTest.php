<?php

namespace Tests\Browser;

use Tests\Browser\Pages\zcInstallPage;
use Tests\Browser\DuskTestCase;
use Laravel\Dusk\Browser;

class ZcInstallTest extends DuskTestCase
{
    /** @test */
    public function zcinstall_page_displays()
    {
        $this->browse(function (Browser $browser) {
            $browser->resize(1920, 1080);
            $browser->visit(new zcInstallPage)
                    ->assertSee('System Inspection');
        });
    }

}
