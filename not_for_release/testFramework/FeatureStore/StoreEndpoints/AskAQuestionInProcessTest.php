<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\FeatureStore\StoreEndpoints;

use Tests\Support\Traits\CustomerAccountConcerns;
use Tests\Support\zcInProcessFeatureTestCaseStore;

/**
 * @group parallel-candidate
 */
class AskAQuestionInProcessTest extends zcInProcessFeatureTestCaseStore
{
    use CustomerAccountConcerns;

    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    public function testLoggedInCustomerSeesPrefilledAskAQuestionForm(): void
    {
        $profile = $this->createCustomerAccountOrLogin('florida-basic1');

        $page = $this->getSsl('/index.php?main_page=ask_a_question&pID=2')
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'storefront')
            ->assertSee('Ask a Question About')
            ->assertSee('Matrox G400 32MB');

        $defaults = $page->formDefaults('ask_a_question');

        $this->assertSame($profile['firstname'] . ' ' . $profile['lastname'], $defaults['contactname'] ?? null);
        $this->assertSame($profile['email_address'], $defaults['email'] ?? null);
    }

    public function testGuestCanSubmitAskAQuestionFormSuccessfully(): void
    {
        $page = $this->getSsl('/index.php?main_page=ask_a_question&pID=2')
            ->assertOk()
            ->assertSee('Matrox G400 32MB');

        $response = $this->postSsl('/index.php?main_page=ask_a_question&action=send&pID=2', array_merge(
            $page->formDefaults('ask_a_question'),
            [
                'contactname' => 'Curious Shopper',
                'email' => 'curious@example.com',
                'telephone' => '5551234567',
                'enquiry' => 'Could you tell me whether this graphics card works well for a basic retro gaming setup?',
            ]
        ));

        $response->assertRedirect('main_page=ask_a_question');

        $this->followRedirect($response)
            ->assertOk()
            ->assertSee('Your message has been successfully sent.')
            ->assertSee('Matrox G400 32MB');
    }

    public function testGuestCanSubmitAskAQuestionFormWithHeaderOnlyCsrfToken(): void
    {
        $page = $this->getSsl('/index.php?main_page=ask_a_question&pID=2')
            ->assertOk()
            ->assertSee('Matrox G400 32MB');

        $formData = $page->formDefaults('ask_a_question');
        $securityToken = $formData['securityToken'] ?? null;

        $this->assertNotNull($securityToken);
        unset($formData['securityToken']);

        $response = $this->postSsl(
            '/index.php?main_page=ask_a_question&action=send&pID=2',
            array_merge($formData, [
                'contactname' => 'Curious Shopper',
                'email' => 'curious-header@example.com',
                'telephone' => '5551234567',
                'enquiry' => 'Could you tell me whether this graphics card works well for a basic retro gaming setup?',
            ]),
            ['HTTP_X_CSRF_TOKEN' => $securityToken]
        );

        $response->assertRedirect('main_page=ask_a_question');

        $this->followRedirect($response)
            ->assertOk()
            ->assertSee('Your message has been successfully sent.')
            ->assertSee('Matrox G400 32MB');
    }

}
