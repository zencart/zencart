<?php

declare(strict_types=1);
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Unit\testsEmail;

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\Support\zcUnitTestCase;

/**
 * Covers the disclaimer branch of Email::sanitizeTextContent().
 *
 * Split out from EmailTextSanitizationTest because the branch is driven by EMAIL_DISCLAIMER
 * constants, which vary per test. PHP constants cannot be redefined, so this class needs
 * process isolation - which is slow, and is why the rest of the sanitizer coverage lives in a
 * sibling class that stubs config through $configurationRepository instead.
 */
#[RunTestsInSeparateProcesses]
class EmailDisclaimerTest extends zcUnitTestCase
{
    /**
     * Listed in Email::isNonTransactional(), so disclaimers apply.
     */
    private const NON_TRANSACTIONAL_MODULE = 'newsletters';

    /**
     * Not listed there, so disclaimers are skipped.
     */
    private const TRANSACTIONAL_MODULE = 'checkout';

    private const STORE_OWNER = 'owner@example.com';
    private const CUSTOMER = 'customer@example.com';

    public function setUp(): void
    {
        parent::setUp();
        require_once DIR_FS_CATALOG . 'includes/classes/Email.php';
    }

    public function tearDown(): void
    {
        unset($GLOBALS['configurationRepository'], $GLOBALS['productTypeLayoutRepository']);
        parent::tearDown();
    }

    private function sanitize(string $text, string $module, string $toAddress = self::CUSTOMER): string
    {
        $GLOBALS['configurationRepository'] = new class (self::STORE_OWNER) {
            public function __construct(private string $storeOwner) {}

            public function get(string $key): mixed
            {
                return $key === 'STORE_OWNER_EMAIL_ADDRESS' ? $this->storeOwner : null;
            }
        };
        $GLOBALS['productTypeLayoutRepository'] = new class {
            public function get(string $key): mixed
            {
                return null;
            }
        };

        $sanitizer = new class extends \Email {
            public function sanitize(string $text, string $module, string $toAddress): string
            {
                return $this->sanitizeTextContent($text, '', $module, $toAddress);
            }
        };

        return $sanitizer->sanitize($text, $module, $toAddress);
    }

    public function testDisclaimerIsAppendedForNonTransactionalModules(): void
    {
        define('EMAIL_DISCLAIMER', 'Questions? Contact %s');

        $actual = $this->sanitize('Hello', self::NON_TRANSACTIONAL_MODULE);

        self::assertSame("Hello\nQuestions? Contact " . self::STORE_OWNER, $actual);
    }

    public function testDisclaimerIsNotAppendedForTransactionalModules(): void
    {
        define('EMAIL_DISCLAIMER', 'Questions? Contact %s');

        $actual = $this->sanitize('Your order has shipped', self::TRANSACTIONAL_MODULE);

        self::assertSame('Your order has shipped', $actual);
    }

    public function testDisclaimerIsNotAppendedWhenRecipientIsTheStoreOwner(): void
    {
        define('EMAIL_DISCLAIMER', 'Questions? Contact %s');

        $actual = $this->sanitize('Hello', self::NON_TRANSACTIONAL_MODULE, self::STORE_OWNER);

        self::assertSame('Hello', $actual);
    }

    public function testDisclaimerIsNotRepeatedWhenAlreadyPresentInTheBody(): void
    {
        define('EMAIL_DISCLAIMER', 'Questions? Contact %s');
        $body = 'Hello. Questions? Contact ' . self::STORE_OWNER;

        $actual = $this->sanitize($body, self::NON_TRANSACTIONAL_MODULE);

        self::assertSame($body, $actual);
    }

    /**
     * Defining EMAIL_DISCLAIMER_NEW_CUSTOMER suppresses the standard disclaimer entirely -
     * the constant's mere existence is the switch; its value is never read here.
     */
    public function testDisclaimerIsSuppressedWhenNewCustomerConstantIsDefined(): void
    {
        define('EMAIL_DISCLAIMER', 'Questions? Contact %s');
        define('EMAIL_DISCLAIMER_NEW_CUSTOMER', 'anything at all');

        $actual = $this->sanitize('Hello', self::NON_TRANSACTIONAL_MODULE);

        self::assertSame('Hello', $actual);
    }

    public function testSpamDisclaimerIsAppendedForNonTransactionalModules(): void
    {
        define('EMAIL_SPAM_DISCLAIMER', 'You received this because you subscribed.');

        $actual = $this->sanitize('Hello', self::NON_TRANSACTIONAL_MODULE);

        self::assertSame("Hello\n\nYou received this because you subscribed.", $actual);
    }

    public function testSpamDisclaimerIsNotRepeatedWhenAlreadyPresentInTheBody(): void
    {
        define('EMAIL_SPAM_DISCLAIMER', 'You received this because you subscribed.');
        $body = 'Hello. You received this because you subscribed.';

        $actual = $this->sanitize($body, self::NON_TRANSACTIONAL_MODULE);

        self::assertSame($body, $actual);
    }

    public function testBothDisclaimersAppendInOrder(): void
    {
        define('EMAIL_DISCLAIMER', 'Questions? Contact %s');
        define('EMAIL_SPAM_DISCLAIMER', 'You received this because you subscribed.');

        $actual = $this->sanitize('Hello', self::NON_TRANSACTIONAL_MODULE);

        self::assertSame(
            "Hello\nQuestions? Contact " . self::STORE_OWNER . "\n\nYou received this because you subscribed.",
            $actual
        );
    }
}
