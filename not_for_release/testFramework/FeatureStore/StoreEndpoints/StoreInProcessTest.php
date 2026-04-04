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
class StoreInProcessTest extends zcInProcessFeatureTestCaseStore
{
    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    /**
     * @dataProvider simpleStorePageProvider
     */
    public function testSimpleStorePagesCanBeRenderedInProcess(string $page, string $expectedText, array $server = []): void
    {
        $this->getMainPage($page, $server)
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'storefront')
            ->assertSee($expectedText);
    }

    public static function simpleStorePageProvider(): array
    {
        return [
            'products_all' => ['products_all', 'Zen Cart! : All Products'],
            'about_us' => ['about_us', 'Zen Cart! : About Us'],
            'privacy' => ['privacy', 'Zen Cart! : Privacy Notice'],
        ];
    }

    public function testContactUsRedirectsToSslInProcess(): void
    {
        $this->getMainPage('contact_us')
            ->assertRedirect('main_page=contact_us');
    }

    public function testContactUsPageCanBeRenderedInProcessOverSsl(): void
    {
        $this->visitContactUs()
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'storefront')
            ->assertSee('Zen Cart! : Contact Us');
    }

    public function testContactUsShowsValidationErrorsForInvalidSubmission(): void
    {
        $this->submitContactUsForm(
            [
                'contactname' => '',
                'email' => 'not-an-email',
                'enquiry' => '',
            ]
        )
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'storefront')
            ->assertSee('Zen Cart! : Contact Us')
            ->assertSee('Sorry, is your name correct?')
            ->assertSee('Sorry, our system does not understand your email address.')
            ->assertSee('Did you forget your message?');
    }

    public function testContactUsCanBeSubmittedSuccessfully(): void
    {
        $response = $this->submitContactUsForm(
            [
                'contactname' => 'Test Customer',
                'email' => 'test@example.com',
                'telephone' => '555-0100',
                'enquiry' => 'This is a test contact request.',
            ]
        )->assertRedirect('main_page=contact_us&action=success');

        $this->followRedirect($response)
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'storefront')
            ->assertSee('Your message has been successfully sent.');
    }
}
