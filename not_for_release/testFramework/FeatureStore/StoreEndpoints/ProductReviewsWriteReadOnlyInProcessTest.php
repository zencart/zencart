<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\FeatureStore\StoreEndpoints;

use Tests\Support\zcInProcessFeatureTestCaseStore;

/**
 * @group parallel-candidate
 */
class ProductReviewsWriteReadOnlyInProcessTest extends zcInProcessFeatureTestCaseStore
{
    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    public function testGuestIsRedirectedToLoginForReviewWritePage(): void
    {
        $this->getSsl('/index.php?main_page=product_reviews_write&products_id=2')
            ->assertRedirect('main_page=login');
    }
}
