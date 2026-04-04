<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\FeatureStore\StoreEndpoints;

use Tests\Support\Database\TestDb;
use Tests\Support\Traits\CustomerAccountConcerns;
use Tests\Support\zcInProcessFeatureTestCaseStore;

/**
 * @group parallel-candidate
 */
class ProductReviewsWriteInProcessTest extends zcInProcessFeatureTestCaseStore
{
    use CustomerAccountConcerns;

    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    public function testLoggedInCustomerCanSubmitProductReview(): void
    {
        $profile = $this->createCustomerAccountOrLogin('florida-basic1');
        $customerId = $this->getCustomerIdFromEmail($profile['email_address']);

        $this->assertNotNull($customerId);

        $page = $this->getSsl('/index.php?main_page=product_reviews_write&products_id=2')
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'storefront')
            ->assertSee('Choose a ranking for this item')
            ->assertSee('Submit the Information');

        $response = $this->postSsl('/index.php?main_page=product_reviews_write&action=process&products_id=2', array_merge(
            $page->formDefaults('product_reviews_write'),
            [
                'rating' => '5',
                'review_text' => 'In-process review text for coverage that is long enough to pass validation.',
            ]
        ));

        $response->assertRedirect('main_page=product_reviews');

        $this->followRedirect($response)
            ->assertOk()
            ->assertSee('Reviews')
            ->assertSee('Matrox G400 32MB');

        $review = TestDb::selectOne(
            'SELECT r.reviews_id, r.reviews_rating, rd.reviews_text
               FROM reviews r
               INNER JOIN reviews_description rd ON rd.reviews_id = r.reviews_id
              WHERE r.products_id = :products_id
                AND r.customers_id = :customers_id
              ORDER BY r.reviews_id DESC
              LIMIT 1',
            [
                ':products_id' => 2,
                ':customers_id' => $customerId,
            ]
        );

        $this->assertNotNull($review);
        $this->assertSame('5', (string) $review['reviews_rating']);
        $this->assertSame('In-process review text for coverage that is long enough to pass validation.', $review['reviews_text']);
    }

    public function testLoggedInCustomerSeesValidationErrorsForInvalidReviewSubmission(): void
    {
        $this->createCustomerAccountOrLogin('florida-basic2');

        $page = $this->getSsl('/index.php?main_page=product_reviews_write&products_id=2')
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'storefront')
            ->assertSee('Choose a ranking for this item');

        $response = $this->postSsl('/index.php?main_page=product_reviews_write&action=process&products_id=2', array_merge(
            $page->formDefaults('product_reviews_write'),
            [
                'rating' => '0',
                'review_text' => 'Too short',
            ]
        ));

        $response->assertOk()
            ->assertSee('Choose a ranking for this item')
            ->assertSee('Please add a few more words to your comments.')
            ->assertSee('Please choose a rating for this item.');
    }
}
