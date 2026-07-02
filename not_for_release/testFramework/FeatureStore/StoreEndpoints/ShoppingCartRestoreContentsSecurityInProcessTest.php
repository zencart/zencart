<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\FeatureStore\StoreEndpoints;

use Tests\Support\Database\TestDb;
use Tests\Support\helpers\ProfileManager;
use Tests\Support\Traits\CustomerAccountConcerns;
use Tests\Support\zcInProcessFeatureTestCaseStore;

#[\PHPUnit\Framework\Attributes\Group('parallel-candidate')]
#[\PHPUnit\Framework\Attributes\Group('customer-account-write')]
class ShoppingCartRestoreContentsSecurityInProcessTest extends zcInProcessFeatureTestCaseStore
{
    use CustomerAccountConcerns;

    public function testTamperedAttributeKeyDoesNotReachSavedBasketAttributesThroughNormalStorefrontFlow(): void
    {
        $profile = ProfileManager::getProfileForLogin('florida-basic1');
        $customerId = $this->getCustomerIdFromEmail($profile['email_address']);
        if ($customerId === null) {
            $this->createCustomerAccountOrLogin('florida-basic1');
            $customerId = $this->getCustomerIdFromEmail($profile['email_address']);
            $this->assertNotNull($customerId);
            $this->followRedirect($this->visitLogoff())->assertOk();
        }

        $this->clearSavedBasket((int) $customerId);

        $productId = 34;
        $validAttributes = $this->validAttributesForProduct($productId);
        $this->assertNotSame([], $validAttributes, 'Expected fixture product to have selectable attributes.');

        $tamperedOption = "1' OR 1=1--";
        $tamperedValue = (string) ($validAttributes[1] ?? reset($validAttributes));
        $validAttributes[$tamperedOption] = $tamperedValue;
        unset($validAttributes[1]);

        $page = $this->visitProduct($productId)
            ->assertOk()
            ->assertSee('addToCartForm');

        $securityToken = $page->securityToken();
        $this->assertNotNull($securityToken);

        $response = $this->post(
            '/index.php?main_page=product_info&products_id=' . $productId . '&action=add_product',
            [
                'products_id' => (string) $productId,
                'cart_quantity' => '1',
                'securityToken' => $securityToken,
                'id' => $validAttributes,
            ]
        );

        $this->assertContains($response->statusCode, [200, 301, 302, 303, 307, 308]);

        $sessionDump = $this->guestSessionPayload();
        $this->assertStringNotContainsString($tamperedOption, $sessionDump, 'Tampered option key should not survive into guest-session cart state.');

        $loginResponse = $this->submitLoginForm($profile);
        if ($loginResponse->isRedirect()) {
            $this->followRedirect($loginResponse)->assertOk();
        } else {
            $loginResponse->assertOk();
        }

        $restoredSessionDump = $this->guestSessionPayload();
        $this->assertStringContainsString('_chk', $restoredSessionDump, 'Checkbox attribute key should survive in post-login cart state.');

        $savedRows = $this->savedBasketAttributes((int) $customerId);
        $this->assertNotSame([], $savedRows, 'Expected saved basket attributes after login restore.');
        $checkboxRow = null;
        foreach ($savedRows as $row) {
            $this->assertMatchesRegularExpression('/^\d+(?:_chk\d+)?$/', (string) $row['products_options_id']);
            $this->assertSame((string) (int) $row['products_options_value_id'], (string) $row['products_options_value_id']);
            if (str_contains((string) $row['products_options_id'], '_chk')) {
                $checkboxRow = $row;
            }
        }
        $this->assertNotNull($checkboxRow, 'Expected checkbox attribute storage key to survive login cart restore.');
    }

    private function clearSavedBasket(int $customerId): void
    {
        $deleteAttributes = TestDb::pdo()->prepare(
            'DELETE FROM customers_basket_attributes WHERE customers_id = :customer_id'
        );
        $deleteAttributes->execute([':customer_id' => $customerId]);

        $deleteBasket = TestDb::pdo()->prepare(
            'DELETE FROM customers_basket WHERE customers_id = :customer_id'
        );
        $deleteBasket->execute([':customer_id' => $customerId]);
    }

    private function validAttributesForProduct(int $productId): array
    {
        $statement = TestDb::pdo()->prepare(
            'SELECT pa.options_id, MIN(pa.options_values_id) AS options_values_id, po.products_options_type
               FROM products_attributes pa
               JOIN products_options po
                 ON po.products_options_id = pa.options_id
                AND po.language_id = 1
              WHERE pa.products_id = :products_id
              GROUP BY pa.options_id, po.products_options_type
              ORDER BY options_id'
        );
        $statement->execute([':products_id' => $productId]);

        $attributes = [];
        foreach ($statement->fetchAll() as $row) {
            if ((string) $row['products_options_type'] === '4') {
                $attributes[(int) $row['options_id']] = [(string) $row['options_values_id'] => (string) $row['options_values_id']];
                continue;
            }
            $attributes[(int) $row['options_id']] = (string) $row['options_values_id'];
        }

        return $attributes;
    }

    private function guestSessionPayload(): string
    {
        $sessionId = $this->cookies['zenid'] ?? '';
        $this->assertNotSame('', $sessionId, 'Expected storefront session cookie to be present.');

        $payload = TestDb::selectValue(
            'SELECT value FROM sessions WHERE sesskey = :session_id',
            [':session_id' => $sessionId]
        );

        return base64_decode((string) $payload, true) ?: '';
    }

    private function savedBasketAttributes(int $customerId): array
    {
        $statement = TestDb::pdo()->prepare(
            'SELECT products_options_id, products_options_value_id, products_options_value_text
               FROM customers_basket_attributes
              WHERE customers_id = :customer_id
              ORDER BY customers_basket_attributes_id ASC'
        );
        $statement->execute([':customer_id' => $customerId]);

        return $statement->fetchAll();
    }
}
