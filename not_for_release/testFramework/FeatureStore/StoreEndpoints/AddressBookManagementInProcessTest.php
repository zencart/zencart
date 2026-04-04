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

/**
 * @group parallel-candidate
 */
class AddressBookManagementInProcessTest extends zcInProcessFeatureTestCaseStore
{
    use CustomerAccountConcerns;

    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    public function testCustomerCanAddSecondaryAddressBookEntry(): void
    {
        $profile = $this->createCustomerAccountOrLogin('florida-basic2');
        $customerId = $this->getCustomerIdFromEmail($profile['email_address']);
        $newAddress = ProfileManager::getProfile('US-not-florida-basic');

        $this->assertNotNull($customerId);

        $page = $this->getSslMainPage('address_book_process')
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'storefront')
            ->assertSee('New Address Book Entry')
            ->assertSee('Street Address:');

        $response = $this->postSslMainPage('address_book_process', array_merge(
            $page->formDefaults('addressbook'),
            [
                'action' => 'process',
                'firstname' => 'Secondary',
                'lastname' => 'Address',
                'street_address' => $newAddress['street_address'],
                'city' => $newAddress['city'],
                'state' => $newAddress['state'],
                'zone_id' => $newAddress['zone_id'],
                'postcode' => $newAddress['postcode'],
                'zone_country_id' => $newAddress['zone_country_id'],
            ]
        ));

        $response->assertRedirect('main_page=address_book');

        $this->followRedirect($response)
            ->assertOk()
            ->assertSee('Your address book has been successfully updated.');

        $address = TestDb::selectOne(
            'SELECT entry_firstname, entry_lastname, entry_city, entry_zone_id
               FROM address_book
              WHERE customers_id = :customer_id
              ORDER BY address_book_id DESC
              LIMIT 1',
            [':customer_id' => $customerId]
        );

        $this->assertNotNull($address);
        $this->assertSame('Secondary', $address['entry_firstname']);
        $this->assertSame('Address', $address['entry_lastname']);
        $this->assertSame($newAddress['city'], $address['entry_city']);
        $this->assertSame((string) $newAddress['zone_id'], (string) $address['entry_zone_id']);
    }

    public function testCustomerCanDeleteSecondaryAddressBookEntry(): void
    {
        $profile = $this->createCustomerAccountOrLogin('florida-basic1');
        $customerId = $this->getCustomerIdFromEmail($profile['email_address']);

        $this->assertNotNull($customerId);

        $addressBookId = TestDb::insert('address_book', [
            'customers_id' => $customerId,
            'entry_firstname' => 'Delete',
            'entry_lastname' => 'Me',
            'entry_street_address' => '456 Secondary St',
            'entry_postcode' => '87101',
            'entry_city' => 'Albuquerque',
            'entry_country_id' => 223,
            'entry_zone_id' => 42,
            'entry_state' => '',
            'entry_gender' => 'm',
            'entry_company' => '',
            'entry_suburb' => '',
        ]);

        $page = $this->getSsl('/index.php?main_page=address_book_process&delete=' . $addressBookId)
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'storefront')
            ->assertSee('Delete Address Book Entry')
            ->assertSee('Delete Me');

        $response = $this->postSsl(
            '/index.php?main_page=address_book_process&action=deleteconfirm',
            array_merge($page->formDefaults('delete_address'), [
                'delete' => $addressBookId,
            ])
        );

        $response->assertRedirect('main_page=address_book');

        $this->followRedirect($response)
            ->assertOk()
            ->assertSee('The selected address has been successfully removed from your address book.');

        $remainingAddress = TestDb::selectValue(
            'SELECT COUNT(*) FROM address_book WHERE customers_id = :customer_id AND address_book_id = :address_book_id',
            [
                ':customer_id' => $customerId,
                ':address_book_id' => $addressBookId,
            ]
        );

        $this->assertSame('0', (string) $remainingAddress);
    }

    public function testCustomerCanEditSecondaryAddressAndSetItAsPrimary(): void
    {
        $profile = $this->createCustomerAccountOrLogin('florida-basic2');
        $customerId = $this->getCustomerIdFromEmail($profile['email_address']);

        $this->assertNotNull($customerId);

        $addressBookId = TestDb::insert('address_book', [
            'customers_id' => $customerId,
            'entry_firstname' => 'Old',
            'entry_lastname' => 'Primary',
            'entry_street_address' => '987 Old Rd',
            'entry_postcode' => '87101',
            'entry_city' => 'Albuquerque',
            'entry_country_id' => 223,
            'entry_zone_id' => 42,
            'entry_state' => '',
            'entry_gender' => 'm',
            'entry_company' => '',
            'entry_suburb' => '',
        ]);

        $page = $this->getSsl('/index.php?main_page=address_book_process&edit=' . $addressBookId)
            ->assertOk()
            ->assertHeader('X-ZC-InProcess-Runner', 'storefront')
            ->assertSee('Update Address Book Entry');

        $response = $this->postSsl(
            '/index.php?main_page=address_book_process&edit=' . $addressBookId,
            array_merge($page->formDefaults('addressbook'), [
                'action' => 'update',
                'edit' => $addressBookId,
                'firstname' => 'Updated',
                'lastname' => 'Primary',
                'street_address' => '246 New Primary St',
                'city' => 'Miami',
                'state' => 'Florida',
                'zone_id' => '18',
                'postcode' => '33101',
                'zone_country_id' => '223',
                'primary' => 'on',
            ])
        );

        $response->assertRedirect('main_page=address_book');

        $page = $this->followRedirect($response)
            ->assertOk()
            ->assertSee('Your address book has been successfully updated.')
            ->assertSee('Updated Primary')
            ->assertSee('(primary address)');

        $updatedAddress = TestDb::selectOne(
            'SELECT entry_firstname, entry_street_address, entry_city, entry_zone_id
               FROM address_book
              WHERE address_book_id = :address_book_id
              LIMIT 1',
            [':address_book_id' => $addressBookId]
        );

        $this->assertNotNull($updatedAddress);
        $this->assertSame('Updated', $updatedAddress['entry_firstname']);
        $this->assertSame('246 New Primary St', $updatedAddress['entry_street_address']);
        $this->assertSame('Miami', $updatedAddress['entry_city']);
        $this->assertSame('18', (string) $updatedAddress['entry_zone_id']);

        $defaultAddressId = TestDb::selectValue(
            'SELECT customers_default_address_id FROM customers WHERE customers_id = :customer_id LIMIT 1',
            [':customer_id' => $customerId]
        );

        $this->assertSame((string) $addressBookId, (string) $defaultAddressId);
    }
}
