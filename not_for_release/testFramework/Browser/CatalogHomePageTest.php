<?php

namespace Tests\Browser;

use Tests\Browser\Pages\HomePage;
use Tests\Browser\DuskTestCase;
use Laravel\Dusk\Browser;

class CatalogHomePageTest extends DuskTestCase
{
    /** @test */
    public function home_page_displays()
    {
        $this->markTestIncomplete('Incomplete test: requires scripted install before visiting landing page');
        $this->browse(function (Browser $browser) {
            $browser->resize(1920, 1080);
            $browser->visit(new HomePage)
                    ->assertSee('Congratulations!');
        });
    }
}
