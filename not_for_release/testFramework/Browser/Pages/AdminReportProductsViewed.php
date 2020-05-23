<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class AdminReportProductsViewed extends Page
{
    /**
     * Get the URL for the page.
     *
     * @return string
     */
    public function url()
    {
        return '/admin/stats_products_viewed.php';
    }

    /**
     * Assert that the browser is on the page.
     *
     * @param  Browser  $browser
     * @return void
     */
    public function assert(Browser $browser)
    {
        $browser->assertPathBeginsWith($this->url());
        $browser->assertSee('Most-Viewed Products');
    }

    /**
     * Get the element shortcuts for the page.
     *
     * @return array
     */
    public function elements()
    {
        return [
            '@element' => '#selector',
        ];
    }
}
