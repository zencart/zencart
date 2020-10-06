<?php

namespace Tests\Browser;

use Tests\Browser\Pages\AdminReportProductsViewed;
use Tests\Browser\DuskTestCase;
use Laravel\Dusk\Browser;

class AdminReportsTest extends DuskTestCase
{
    /** @ test */
    public function products_viewed_report_displays()
    {
        $this->markTestIncomplete('Incomplete test: requires scripted install before accessing Admin, and then requires login');
        $this->browse(function (Browser $browser) {
            $browser->resize(1920, 1080);
            $browser->visit(new AdminReportProductsViewed())
                    ->assertSee('Most-Viewed Products');
        });
    }
}
