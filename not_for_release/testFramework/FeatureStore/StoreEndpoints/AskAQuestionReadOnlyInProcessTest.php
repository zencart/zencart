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
class AskAQuestionReadOnlyInProcessTest extends zcInProcessFeatureTestCaseStore
{
    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    public function testGuestCanViewAskAQuestionPageForAProduct(): void
    {
        $this->getSsl('/index.php?main_page=ask_a_question&pID=2')
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'storefront')
            ->assertSee('Ask a Question About')
            ->assertSee('Matrox G400 32MB')
            ->assertSee('contactname')
            ->assertSee('enquiry');
    }

    public function testGuestSeesValidationErrorsForInvalidAskAQuestionSubmission(): void
    {
        $page = $this->getSsl('/index.php?main_page=ask_a_question&pID=2')
            ->assertOk()
            ->assertSee('What is Your Question?');

        $response = $this->postSsl('/index.php?main_page=ask_a_question&action=send&pID=2', array_merge(
            $page->formDefaults('ask_a_question'),
            [
                'contactname' => '',
                'email' => 'not-an-email',
                'telephone' => '5551234567',
                'enquiry' => '',
            ]
        ));

        $response->assertOk()
            ->assertSee('Ask a Question About')
            ->assertSee('Sorry, is your name correct?')
            ->assertSee('Sorry, our system does not understand your email address.')
            ->assertSee('Did you forget your message?');
    }
}
