Square Connect PHP SDK [![Build Status](https://travis-ci.org/square/connect-php-sdk.svg?branch=master)](https://travis-ci.org/square/connect-php-sdk)
==================

**If you have feedback about the new SDKs, or just want to talk to other Square Developers, request an invite to the new [slack community for Square Developers](https://squ.re/2JkDBcO)**

This repository contains a generated PHP client SDK for the Square Connect APIs. Check out our [API
specification repository](https://github.com/square/connect-api-specification)
for the specification and template files we used to generate this.

If you are looking for a sample e-commerce application using these APIs, check out the [`connect-api-examples`](https://github.com/square/connect-api-examples/tree/master/connect-examples/v2/php_payment) repository.

To learn more about the Square APIs in general, head on over to the [Square API documentation](https://docs.connect.squareup.com/)

Requirements
------------
* `PHP >= 5.4.0`
* A Square account and [developer application](https://connect.squareup.com/apps/) (for authorization)

Installing
-----

##### With Composer

The PHP SDK is available on Packagist. To add it to Composer, simply run:

```
$ php composer.phar require square/connect
```

Or add this line under `"require"` to your composer.json:

```
"require": {
    ...
    "square/connect": "*",
    ...
}
```
And then install your composer dependencies with
```
$ php composer.phar install
```
##### From GitHub
Clone this repository, or download the zip into your project's folder and then add the following line in your code:
```
require('connect-php-sdk/autoload.php');
```
*Note: you might have to change the path depending on your project's folder structure.*
##### Without Command Line Access
If you cannot access the command line for your server, you can also install the SDK from github. Download the SDK from github with [this link](https://github.com/square/connect-php-sdk/archive/master.zip), unzip it and add the following line to your php files that will need to access the SDK:
```
require('connect-php-sdk-master/autoload.php');
```
*Note: you might have to change the path depending on where you place the SDK in relation to your other `php` files.*

## Getting Started

Please follow the [installation procedure](#installation--usage):


### Retrieve your location IDs
```php
require 'vendor/autoload.php';

$access_token = 'YOUR_ACCESS_TOKEN';
# setup authorization
\SquareConnect\Configuration::getDefaultConfiguration()->setAccessToken($access_token);
# create an instance of the Location API
$locations_api = new \SquareConnect\Api\LocationsApi();

try {
  $locations = $locations_api->listLocations();
  print_r($locations->getLocations());
} catch (\SquareConnect\ApiException $e) {
  echo "Caught exception!<br/>";
  print_r("<strong>Response body:</strong><br/>");
  echo "<pre>"; var_dump($e->getResponseBody()); echo "</pre>";
  echo "<br/><strong>Response headers:</strong><br/>";
  echo "<pre>"; var_dump($e->getResponseHeaders()); echo "</pre>";
  exit(1);
}
```

### Charge the card nonce
```php
require 'vendor/autoload.php';

$access_token = 'YOUR_ACCESS_TOKEN';
# setup authorization
\SquareConnect\Configuration::getDefaultConfiguration()->setAccessToken($access_token);
# create an instance of the Transaction API class
$transactions_api = new \SquareConnect\Api\TransactionsApi();
$location_id = 'YOUR_LOCATION_ID'
$nonce = 'YOUR_NONCE'

$request_body = array (
    "card_nonce" => $nonce,
    # Monetary amounts are specified in the smallest unit of the applicable currency.
    # This amount is in cents. It's also hard-coded for $1.00, which isn't very useful.
    "amount_money" => array (
        "amount" => 100,
        "currency" => "USD"
    ),
    # Every payment you process with the SDK must have a unique idempotency key.
    # If you're unsure whether a particular payment succeeded, you can reattempt
    # it with the same idempotency key without worrying about double charging
    # the buyer.
    "idempotency_key" => uniqid()
);

try {
    $result = $transactions_api->charge($location_id,  $request_body);
    print_r($result);
} catch (\SquareConnect\ApiException $e) {
    echo "Exception when calling TransactionApi->charge:";
    var_dump($e->getResponseBody());
}
```


## Documentation for API Endpoints

All URIs are relative to *https://connect.squareup.com*

Class | Method | HTTP request | Description
------------ | ------------- | ------------- | -------------
*ApplePayApi* | [**registerDomain**](docs/Api/ApplePayApi.md#registerdomain) | **POST** /v2/apple-pay/domains | RegisterDomain
*CatalogApi* | [**batchDeleteCatalogObjects**](docs/Api/CatalogApi.md#batchdeletecatalogobjects) | **POST** /v2/catalog/batch-delete | BatchDeleteCatalogObjects
*CatalogApi* | [**batchRetrieveCatalogObjects**](docs/Api/CatalogApi.md#batchretrievecatalogobjects) | **POST** /v2/catalog/batch-retrieve | BatchRetrieveCatalogObjects
*CatalogApi* | [**batchUpsertCatalogObjects**](docs/Api/CatalogApi.md#batchupsertcatalogobjects) | **POST** /v2/catalog/batch-upsert | BatchUpsertCatalogObjects
*CatalogApi* | [**catalogInfo**](docs/Api/CatalogApi.md#cataloginfo) | **GET** /v2/catalog/info | CatalogInfo
*CatalogApi* | [**deleteCatalogObject**](docs/Api/CatalogApi.md#deletecatalogobject) | **DELETE** /v2/catalog/object/{object_id} | DeleteCatalogObject
*CatalogApi* | [**listCatalog**](docs/Api/CatalogApi.md#listcatalog) | **GET** /v2/catalog/list | ListCatalog
*CatalogApi* | [**retrieveCatalogObject**](docs/Api/CatalogApi.md#retrievecatalogobject) | **GET** /v2/catalog/object/{object_id} | RetrieveCatalogObject
*CatalogApi* | [**searchCatalogObjects**](docs/Api/CatalogApi.md#searchcatalogobjects) | **POST** /v2/catalog/search | SearchCatalogObjects
*CatalogApi* | [**updateItemModifierLists**](docs/Api/CatalogApi.md#updateitemmodifierlists) | **POST** /v2/catalog/update-item-modifier-lists | UpdateItemModifierLists
*CatalogApi* | [**updateItemTaxes**](docs/Api/CatalogApi.md#updateitemtaxes) | **POST** /v2/catalog/update-item-taxes | UpdateItemTaxes
*CatalogApi* | [**upsertCatalogObject**](docs/Api/CatalogApi.md#upsertcatalogobject) | **POST** /v2/catalog/object | UpsertCatalogObject
*CheckoutApi* | [**createCheckout**](docs/Api/CheckoutApi.md#createcheckout) | **POST** /v2/locations/{location_id}/checkouts | CreateCheckout
*CustomersApi* | [**createCustomer**](docs/Api/CustomersApi.md#createcustomer) | **POST** /v2/customers | CreateCustomer
*CustomersApi* | [**createCustomerCard**](docs/Api/CustomersApi.md#createcustomercard) | **POST** /v2/customers/{customer_id}/cards | CreateCustomerCard
*CustomersApi* | [**deleteCustomer**](docs/Api/CustomersApi.md#deletecustomer) | **DELETE** /v2/customers/{customer_id} | DeleteCustomer
*CustomersApi* | [**deleteCustomerCard**](docs/Api/CustomersApi.md#deletecustomercard) | **DELETE** /v2/customers/{customer_id}/cards/{card_id} | DeleteCustomerCard
*CustomersApi* | [**listCustomers**](docs/Api/CustomersApi.md#listcustomers) | **GET** /v2/customers | ListCustomers
*CustomersApi* | [**retrieveCustomer**](docs/Api/CustomersApi.md#retrievecustomer) | **GET** /v2/customers/{customer_id} | RetrieveCustomer
*CustomersApi* | [**searchCustomers**](docs/Api/CustomersApi.md#searchcustomers) | **POST** /v2/customers/search | SearchCustomers
*CustomersApi* | [**updateCustomer**](docs/Api/CustomersApi.md#updatecustomer) | **PUT** /v2/customers/{customer_id} | UpdateCustomer
*InventoryApi* | [**batchChangeInventory**](docs/Api/InventoryApi.md#batchchangeinventory) | **POST** /v2/inventory/batch-change | BatchChangeInventory
*InventoryApi* | [**batchRetrieveInventoryChanges**](docs/Api/InventoryApi.md#batchretrieveinventorychanges) | **POST** /v2/inventory/batch-retrieve-changes | BatchRetrieveInventoryChanges
*InventoryApi* | [**batchRetrieveInventoryCounts**](docs/Api/InventoryApi.md#batchretrieveinventorycounts) | **POST** /v2/inventory/batch-retrieve-counts | BatchRetrieveInventoryCounts
*InventoryApi* | [**retrieveInventoryAdjustment**](docs/Api/InventoryApi.md#retrieveinventoryadjustment) | **GET** /v2/inventory/adjustment/{adjustment_id} | RetrieveInventoryAdjustment
*InventoryApi* | [**retrieveInventoryChanges**](docs/Api/InventoryApi.md#retrieveinventorychanges) | **GET** /v2/inventory/{catalog_object_id}/changes | RetrieveInventoryChanges
*InventoryApi* | [**retrieveInventoryCount**](docs/Api/InventoryApi.md#retrieveinventorycount) | **GET** /v2/inventory/{catalog_object_id} | RetrieveInventoryCount
*InventoryApi* | [**retrieveInventoryPhysicalCount**](docs/Api/InventoryApi.md#retrieveinventoryphysicalcount) | **GET** /v2/inventory/physical-count/{physical_count_id} | RetrieveInventoryPhysicalCount
*LocationsApi* | [**listLocations**](docs/Api/LocationsApi.md#listlocations) | **GET** /v2/locations | ListLocations
*MobileAuthorizationApi* | [**createMobileAuthorizationCode**](docs/Api/MobileAuthorizationApi.md#createmobileauthorizationcode) | **POST** /mobile/authorization-code | CreateMobileAuthorizationCode
*OAuthApi* | [**obtainToken**](docs/Api/OAuthApi.md#obtaintoken) | **POST** /oauth2/token | ObtainToken
*OAuthApi* | [**renewToken**](docs/Api/OAuthApi.md#renewtoken) | **POST** /oauth2/clients/{client_id}/access-token/renew | RenewToken
*OAuthApi* | [**revokeToken**](docs/Api/OAuthApi.md#revoketoken) | **POST** /oauth2/revoke | RevokeToken
*OrdersApi* | [**batchRetrieveOrders**](docs/Api/OrdersApi.md#batchretrieveorders) | **POST** /v2/locations/{location_id}/orders/batch-retrieve | BatchRetrieveOrders
*OrdersApi* | [**createOrder**](docs/Api/OrdersApi.md#createorder) | **POST** /v2/locations/{location_id}/orders | CreateOrder
*ReportingApi* | [**listAdditionalRecipientReceivableRefunds**](docs/Api/ReportingApi.md#listadditionalrecipientreceivablerefunds) | **GET** /v2/locations/{location_id}/additional-recipient-receivable-refunds | ListAdditionalRecipientReceivableRefunds
*ReportingApi* | [**listAdditionalRecipientReceivables**](docs/Api/ReportingApi.md#listadditionalrecipientreceivables) | **GET** /v2/locations/{location_id}/additional-recipient-receivables | ListAdditionalRecipientReceivables
*TransactionsApi* | [**captureTransaction**](docs/Api/TransactionsApi.md#capturetransaction) | **POST** /v2/locations/{location_id}/transactions/{transaction_id}/capture | CaptureTransaction
*TransactionsApi* | [**charge**](docs/Api/TransactionsApi.md#charge) | **POST** /v2/locations/{location_id}/transactions | Charge
*TransactionsApi* | [**createRefund**](docs/Api/TransactionsApi.md#createrefund) | **POST** /v2/locations/{location_id}/transactions/{transaction_id}/refund | CreateRefund
*TransactionsApi* | [**listRefunds**](docs/Api/TransactionsApi.md#listrefunds) | **GET** /v2/locations/{location_id}/refunds | ListRefunds
*TransactionsApi* | [**listTransactions**](docs/Api/TransactionsApi.md#listtransactions) | **GET** /v2/locations/{location_id}/transactions | ListTransactions
*TransactionsApi* | [**retrieveTransaction**](docs/Api/TransactionsApi.md#retrievetransaction) | **GET** /v2/locations/{location_id}/transactions/{transaction_id} | RetrieveTransaction
*TransactionsApi* | [**voidTransaction**](docs/Api/TransactionsApi.md#voidtransaction) | **POST** /v2/locations/{location_id}/transactions/{transaction_id}/void | VoidTransaction
*V1EmployeesApi* | [**createEmployee**](docs/Api/V1EmployeesApi.md#createemployee) | **POST** /v1/me/employees | Creates an employee for a business.
*V1EmployeesApi* | [**createEmployeeRole**](docs/Api/V1EmployeesApi.md#createemployeerole) | **POST** /v1/me/roles | Creates an employee role you can then assign to employees.
*V1EmployeesApi* | [**createTimecard**](docs/Api/V1EmployeesApi.md#createtimecard) | **POST** /v1/me/timecards | Creates a timecard for an employee. Each timecard corresponds to a single shift.
*V1EmployeesApi* | [**deleteTimecard**](docs/Api/V1EmployeesApi.md#deletetimecard) | **DELETE** /v1/me/timecards/{timecard_id} | Deletes a timecard. Deleted timecards are still accessible from Connect API endpoints, but the value of their deleted field is set to true. See Handling deleted timecards for more information.
*V1EmployeesApi* | [**listCashDrawerShifts**](docs/Api/V1EmployeesApi.md#listcashdrawershifts) | **GET** /v1/{location_id}/cash-drawer-shifts | Provides the details for all of a location&#39;s cash drawer shifts during a date range. The date range you specify cannot exceed 90 days.
*V1EmployeesApi* | [**listEmployeeRoles**](docs/Api/V1EmployeesApi.md#listemployeeroles) | **GET** /v1/me/roles | Provides summary information for all of a business&#39;s employee roles.
*V1EmployeesApi* | [**listEmployees**](docs/Api/V1EmployeesApi.md#listemployees) | **GET** /v1/me/employees | Provides summary information for all of a business&#39;s employees.
*V1EmployeesApi* | [**listTimecardEvents**](docs/Api/V1EmployeesApi.md#listtimecardevents) | **GET** /v1/me/timecards/{timecard_id}/events | Provides summary information for all events associated with a particular timecard.
*V1EmployeesApi* | [**listTimecards**](docs/Api/V1EmployeesApi.md#listtimecards) | **GET** /v1/me/timecards | Provides summary information for all of a business&#39;s employee timecards.
*V1EmployeesApi* | [**retrieveCashDrawerShift**](docs/Api/V1EmployeesApi.md#retrievecashdrawershift) | **GET** /v1/{location_id}/cash-drawer-shifts/{shift_id} | Provides the details for a single cash drawer shift, including all events that occurred during the shift.
*V1EmployeesApi* | [**retrieveEmployee**](docs/Api/V1EmployeesApi.md#retrieveemployee) | **GET** /v1/me/employees/{employee_id} | Provides the details for a single employee.
*V1EmployeesApi* | [**retrieveEmployeeRole**](docs/Api/V1EmployeesApi.md#retrieveemployeerole) | **GET** /v1/me/roles/{role_id} | Provides the details for a single employee role.
*V1EmployeesApi* | [**retrieveTimecard**](docs/Api/V1EmployeesApi.md#retrievetimecard) | **GET** /v1/me/timecards/{timecard_id} | Provides the details for a single timecard.
*V1EmployeesApi* | [**updateEmployee**](docs/Api/V1EmployeesApi.md#updateemployee) | **PUT** /v1/me/employees/{employee_id} | V1 UpdateEmployee
*V1EmployeesApi* | [**updateEmployeeRole**](docs/Api/V1EmployeesApi.md#updateemployeerole) | **PUT** /v1/me/roles/{role_id} | Modifies the details of an employee role.
*V1EmployeesApi* | [**updateTimecard**](docs/Api/V1EmployeesApi.md#updatetimecard) | **PUT** /v1/me/timecards/{timecard_id} | Modifies a timecard&#39;s details. This creates an API_EDIT event for the timecard. You can view a timecard&#39;s event history with the List Timecard Events endpoint.
*V1ItemsApi* | [**adjustInventory**](docs/Api/V1ItemsApi.md#adjustinventory) | **POST** /v1/{location_id}/inventory/{variation_id} | Adjusts an item variation&#39;s current available inventory.
*V1ItemsApi* | [**applyFee**](docs/Api/V1ItemsApi.md#applyfee) | **PUT** /v1/{location_id}/items/{item_id}/fees/{fee_id} | Associates a fee with an item, meaning the fee is automatically applied to the item in Square Register.
*V1ItemsApi* | [**applyModifierList**](docs/Api/V1ItemsApi.md#applymodifierlist) | **PUT** /v1/{location_id}/items/{item_id}/modifier-lists/{modifier_list_id} | Associates a modifier list with an item, meaning modifier options from the list can be applied to the item.
*V1ItemsApi* | [**createCategory**](docs/Api/V1ItemsApi.md#createcategory) | **POST** /v1/{location_id}/categories | Creates an item category.
*V1ItemsApi* | [**createDiscount**](docs/Api/V1ItemsApi.md#creatediscount) | **POST** /v1/{location_id}/discounts | Creates a discount.
*V1ItemsApi* | [**createFee**](docs/Api/V1ItemsApi.md#createfee) | **POST** /v1/{location_id}/fees | Creates a fee (tax).
*V1ItemsApi* | [**createItem**](docs/Api/V1ItemsApi.md#createitem) | **POST** /v1/{location_id}/items | Creates an item and at least one variation for it.
*V1ItemsApi* | [**createModifierList**](docs/Api/V1ItemsApi.md#createmodifierlist) | **POST** /v1/{location_id}/modifier-lists | Creates an item modifier list and at least one modifier option for it.
*V1ItemsApi* | [**createModifierOption**](docs/Api/V1ItemsApi.md#createmodifieroption) | **POST** /v1/{location_id}/modifier-lists/{modifier_list_id}/modifier-options | Creates an item modifier option and adds it to a modifier list.
*V1ItemsApi* | [**createPage**](docs/Api/V1ItemsApi.md#createpage) | **POST** /v1/{location_id}/pages | Creates a Favorites page in Square Register.
*V1ItemsApi* | [**createVariation**](docs/Api/V1ItemsApi.md#createvariation) | **POST** /v1/{location_id}/items/{item_id}/variations | Creates an item variation for an existing item.
*V1ItemsApi* | [**deleteCategory**](docs/Api/V1ItemsApi.md#deletecategory) | **DELETE** /v1/{location_id}/categories/{category_id} | Deletes an existing item category.
*V1ItemsApi* | [**deleteDiscount**](docs/Api/V1ItemsApi.md#deletediscount) | **DELETE** /v1/{location_id}/discounts/{discount_id} | Deletes an existing discount.
*V1ItemsApi* | [**deleteFee**](docs/Api/V1ItemsApi.md#deletefee) | **DELETE** /v1/{location_id}/fees/{fee_id} | Deletes an existing fee (tax).
*V1ItemsApi* | [**deleteItem**](docs/Api/V1ItemsApi.md#deleteitem) | **DELETE** /v1/{location_id}/items/{item_id} | Deletes an existing item and all item variations associated with it.
*V1ItemsApi* | [**deleteModifierList**](docs/Api/V1ItemsApi.md#deletemodifierlist) | **DELETE** /v1/{location_id}/modifier-lists/{modifier_list_id} | Deletes an existing item modifier list and all modifier options associated with it.
*V1ItemsApi* | [**deleteModifierOption**](docs/Api/V1ItemsApi.md#deletemodifieroption) | **DELETE** /v1/{location_id}/modifier-lists/{modifier_list_id}/modifier-options/{modifier_option_id} | Deletes an existing item modifier option from a modifier list.
*V1ItemsApi* | [**deletePage**](docs/Api/V1ItemsApi.md#deletepage) | **DELETE** /v1/{location_id}/pages/{page_id} | Deletes an existing Favorites page and all of its cells.
*V1ItemsApi* | [**deletePageCell**](docs/Api/V1ItemsApi.md#deletepagecell) | **DELETE** /v1/{location_id}/pages/{page_id}/cells | Deletes a cell from a Favorites page in Square Register.
*V1ItemsApi* | [**deleteVariation**](docs/Api/V1ItemsApi.md#deletevariation) | **DELETE** /v1/{location_id}/items/{item_id}/variations/{variation_id} | Deletes an existing item variation from an item.
*V1ItemsApi* | [**listCategories**](docs/Api/V1ItemsApi.md#listcategories) | **GET** /v1/{location_id}/categories | Lists all of a location&#39;s item categories.
*V1ItemsApi* | [**listDiscounts**](docs/Api/V1ItemsApi.md#listdiscounts) | **GET** /v1/{location_id}/discounts | Lists all of a location&#39;s discounts.
*V1ItemsApi* | [**listFees**](docs/Api/V1ItemsApi.md#listfees) | **GET** /v1/{location_id}/fees | Lists all of a location&#39;s fees (taxes).
*V1ItemsApi* | [**listInventory**](docs/Api/V1ItemsApi.md#listinventory) | **GET** /v1/{location_id}/inventory | Provides inventory information for all of a merchant&#39;s inventory-enabled item variations.
*V1ItemsApi* | [**listItems**](docs/Api/V1ItemsApi.md#listitems) | **GET** /v1/{location_id}/items | Provides summary information for all of a location&#39;s items.
*V1ItemsApi* | [**listModifierLists**](docs/Api/V1ItemsApi.md#listmodifierlists) | **GET** /v1/{location_id}/modifier-lists | Lists all of a location&#39;s modifier lists.
*V1ItemsApi* | [**listPages**](docs/Api/V1ItemsApi.md#listpages) | **GET** /v1/{location_id}/pages | Lists all of a location&#39;s Favorites pages in Square Register.
*V1ItemsApi* | [**removeFee**](docs/Api/V1ItemsApi.md#removefee) | **DELETE** /v1/{location_id}/items/{item_id}/fees/{fee_id} | Removes a fee assocation from an item, meaning the fee is no longer automatically applied to the item in Square Register.
*V1ItemsApi* | [**removeModifierList**](docs/Api/V1ItemsApi.md#removemodifierlist) | **DELETE** /v1/{location_id}/items/{item_id}/modifier-lists/{modifier_list_id} | Removes a modifier list association from an item, meaning modifier options from the list can no longer be applied to the item.
*V1ItemsApi* | [**retrieveItem**](docs/Api/V1ItemsApi.md#retrieveitem) | **GET** /v1/{location_id}/items/{item_id} | Provides the details for a single item, including associated modifier lists and fees.
*V1ItemsApi* | [**retrieveModifierList**](docs/Api/V1ItemsApi.md#retrievemodifierlist) | **GET** /v1/{location_id}/modifier-lists/{modifier_list_id} | Provides the details for a single modifier list.
*V1ItemsApi* | [**updateCategory**](docs/Api/V1ItemsApi.md#updatecategory) | **PUT** /v1/{location_id}/categories/{category_id} | Modifies the details of an existing item category.
*V1ItemsApi* | [**updateDiscount**](docs/Api/V1ItemsApi.md#updatediscount) | **PUT** /v1/{location_id}/discounts/{discount_id} | Modifies the details of an existing discount.
*V1ItemsApi* | [**updateFee**](docs/Api/V1ItemsApi.md#updatefee) | **PUT** /v1/{location_id}/fees/{fee_id} | Modifies the details of an existing fee (tax).
*V1ItemsApi* | [**updateItem**](docs/Api/V1ItemsApi.md#updateitem) | **PUT** /v1/{location_id}/items/{item_id} | Modifies the core details of an existing item.
*V1ItemsApi* | [**updateModifierList**](docs/Api/V1ItemsApi.md#updatemodifierlist) | **PUT** /v1/{location_id}/modifier-lists/{modifier_list_id} | Modifies the details of an existing item modifier list.
*V1ItemsApi* | [**updateModifierOption**](docs/Api/V1ItemsApi.md#updatemodifieroption) | **PUT** /v1/{location_id}/modifier-lists/{modifier_list_id}/modifier-options/{modifier_option_id} | Modifies the details of an existing item modifier option.
*V1ItemsApi* | [**updatePage**](docs/Api/V1ItemsApi.md#updatepage) | **PUT** /v1/{location_id}/pages/{page_id} | Modifies the details of a Favorites page in Square Register.
*V1ItemsApi* | [**updatePageCell**](docs/Api/V1ItemsApi.md#updatepagecell) | **PUT** /v1/{location_id}/pages/{page_id}/cells | Modifies a cell of a Favorites page in Square Register.
*V1ItemsApi* | [**updateVariation**](docs/Api/V1ItemsApi.md#updatevariation) | **PUT** /v1/{location_id}/items/{item_id}/variations/{variation_id} | Modifies the details of an existing item variation.
*V1LocationsApi* | [**listLocations**](docs/Api/V1LocationsApi.md#listlocations) | **GET** /v1/me/locations | Provides details for a business&#39;s locations, including their IDs.
*V1LocationsApi* | [**retrieveBusiness**](docs/Api/V1LocationsApi.md#retrievebusiness) | **GET** /v1/me | Get a business&#39;s information.
*V1TransactionsApi* | [**createRefund**](docs/Api/V1TransactionsApi.md#createrefund) | **POST** /v1/{location_id}/refunds | Issues a refund for a previously processed payment. You must issue a refund within 60 days of the associated payment.
*V1TransactionsApi* | [**listBankAccounts**](docs/Api/V1TransactionsApi.md#listbankaccounts) | **GET** /v1/{location_id}/bank-accounts | Provides non-confidential details for all of a location&#39;s associated bank accounts. This endpoint does not provide full bank account numbers, and there is no way to obtain a full bank account number with the Connect API.
*V1TransactionsApi* | [**listOrders**](docs/Api/V1TransactionsApi.md#listorders) | **GET** /v1/{location_id}/orders | Provides summary information for a merchant&#39;s online store orders.
*V1TransactionsApi* | [**listPayments**](docs/Api/V1TransactionsApi.md#listpayments) | **GET** /v1/{location_id}/payments | Provides summary information for all payments taken by a merchant or any of the merchant&#39;s mobile staff during a date range. Date ranges cannot exceed one year in length. See Date ranges for details of inclusive and exclusive dates.
*V1TransactionsApi* | [**listRefunds**](docs/Api/V1TransactionsApi.md#listrefunds) | **GET** /v1/{location_id}/refunds | Provides the details for all refunds initiated by a merchant or any of the merchant&#39;s mobile staff during a date range. Date ranges cannot exceed one year in length.
*V1TransactionsApi* | [**listSettlements**](docs/Api/V1TransactionsApi.md#listsettlements) | **GET** /v1/{location_id}/settlements | Provides summary information for all deposits and withdrawals initiated by Square to a merchant&#39;s bank account during a date range. Date ranges cannot exceed one year in length.
*V1TransactionsApi* | [**retrieveBankAccount**](docs/Api/V1TransactionsApi.md#retrievebankaccount) | **GET** /v1/{location_id}/bank-accounts/{bank_account_id} | Provides non-confidential details for a merchant&#39;s associated bank account. This endpoint does not provide full bank account numbers, and there is no way to obtain a full bank account number with the Connect API.
*V1TransactionsApi* | [**retrieveOrder**](docs/Api/V1TransactionsApi.md#retrieveorder) | **GET** /v1/{location_id}/orders/{order_id} | Provides comprehensive information for a single online store order, including the order&#39;s history.
*V1TransactionsApi* | [**retrievePayment**](docs/Api/V1TransactionsApi.md#retrievepayment) | **GET** /v1/{location_id}/payments/{payment_id} | Provides comprehensive information for a single payment.
*V1TransactionsApi* | [**retrieveSettlement**](docs/Api/V1TransactionsApi.md#retrievesettlement) | **GET** /v1/{location_id}/settlements/{settlement_id} | Provides comprehensive information for a single settlement, including the entries that contribute to the settlement&#39;s total.
*V1TransactionsApi* | [**updateOrder**](docs/Api/V1TransactionsApi.md#updateorder) | **PUT** /v1/{location_id}/orders/{order_id} | Updates the details of an online store order. Every update you perform on an order corresponds to one of three actions:


## Documentation For Models

 - [AdditionalRecipient](docs/Model/AdditionalRecipient.md)
 - [AdditionalRecipientReceivable](docs/Model/AdditionalRecipientReceivable.md)
 - [AdditionalRecipientReceivableRefund](docs/Model/AdditionalRecipientReceivableRefund.md)
 - [Address](docs/Model/Address.md)
 - [BatchChangeInventoryRequest](docs/Model/BatchChangeInventoryRequest.md)
 - [BatchChangeInventoryResponse](docs/Model/BatchChangeInventoryResponse.md)
 - [BatchDeleteCatalogObjectsRequest](docs/Model/BatchDeleteCatalogObjectsRequest.md)
 - [BatchDeleteCatalogObjectsResponse](docs/Model/BatchDeleteCatalogObjectsResponse.md)
 - [BatchRetrieveCatalogObjectsRequest](docs/Model/BatchRetrieveCatalogObjectsRequest.md)
 - [BatchRetrieveCatalogObjectsResponse](docs/Model/BatchRetrieveCatalogObjectsResponse.md)
 - [BatchRetrieveInventoryChangesRequest](docs/Model/BatchRetrieveInventoryChangesRequest.md)
 - [BatchRetrieveInventoryChangesResponse](docs/Model/BatchRetrieveInventoryChangesResponse.md)
 - [BatchRetrieveInventoryCountsRequest](docs/Model/BatchRetrieveInventoryCountsRequest.md)
 - [BatchRetrieveInventoryCountsResponse](docs/Model/BatchRetrieveInventoryCountsResponse.md)
 - [BatchRetrieveOrdersRequest](docs/Model/BatchRetrieveOrdersRequest.md)
 - [BatchRetrieveOrdersResponse](docs/Model/BatchRetrieveOrdersResponse.md)
 - [BatchUpsertCatalogObjectsRequest](docs/Model/BatchUpsertCatalogObjectsRequest.md)
 - [BatchUpsertCatalogObjectsResponse](docs/Model/BatchUpsertCatalogObjectsResponse.md)
 - [CaptureTransactionRequest](docs/Model/CaptureTransactionRequest.md)
 - [CaptureTransactionResponse](docs/Model/CaptureTransactionResponse.md)
 - [Card](docs/Model/Card.md)
 - [CardBrand](docs/Model/CardBrand.md)
 - [CatalogCategory](docs/Model/CatalogCategory.md)
 - [CatalogDiscount](docs/Model/CatalogDiscount.md)
 - [CatalogDiscountType](docs/Model/CatalogDiscountType.md)
 - [CatalogIdMapping](docs/Model/CatalogIdMapping.md)
 - [CatalogInfoRequest](docs/Model/CatalogInfoRequest.md)
 - [CatalogInfoResponse](docs/Model/CatalogInfoResponse.md)
 - [CatalogInfoResponseLimits](docs/Model/CatalogInfoResponseLimits.md)
 - [CatalogItem](docs/Model/CatalogItem.md)
 - [CatalogItemModifierListInfo](docs/Model/CatalogItemModifierListInfo.md)
 - [CatalogItemProductType](docs/Model/CatalogItemProductType.md)
 - [CatalogItemVariation](docs/Model/CatalogItemVariation.md)
 - [CatalogModifier](docs/Model/CatalogModifier.md)
 - [CatalogModifierList](docs/Model/CatalogModifierList.md)
 - [CatalogModifierListSelectionType](docs/Model/CatalogModifierListSelectionType.md)
 - [CatalogModifierOverride](docs/Model/CatalogModifierOverride.md)
 - [CatalogObject](docs/Model/CatalogObject.md)
 - [CatalogObjectBatch](docs/Model/CatalogObjectBatch.md)
 - [CatalogObjectType](docs/Model/CatalogObjectType.md)
 - [CatalogPricingType](docs/Model/CatalogPricingType.md)
 - [CatalogQuery](docs/Model/CatalogQuery.md)
 - [CatalogQueryExact](docs/Model/CatalogQueryExact.md)
 - [CatalogQueryItemsForModifierList](docs/Model/CatalogQueryItemsForModifierList.md)
 - [CatalogQueryItemsForTax](docs/Model/CatalogQueryItemsForTax.md)
 - [CatalogQueryPrefix](docs/Model/CatalogQueryPrefix.md)
 - [CatalogQueryRange](docs/Model/CatalogQueryRange.md)
 - [CatalogQuerySortedAttribute](docs/Model/CatalogQuerySortedAttribute.md)
 - [CatalogQueryText](docs/Model/CatalogQueryText.md)
 - [CatalogTax](docs/Model/CatalogTax.md)
 - [CatalogV1Id](docs/Model/CatalogV1Id.md)
 - [ChargeRequest](docs/Model/ChargeRequest.md)
 - [ChargeRequestAdditionalRecipient](docs/Model/ChargeRequestAdditionalRecipient.md)
 - [ChargeResponse](docs/Model/ChargeResponse.md)
 - [Checkout](docs/Model/Checkout.md)
 - [Country](docs/Model/Country.md)
 - [CreateCheckoutRequest](docs/Model/CreateCheckoutRequest.md)
 - [CreateCheckoutResponse](docs/Model/CreateCheckoutResponse.md)
 - [CreateCustomerCardRequest](docs/Model/CreateCustomerCardRequest.md)
 - [CreateCustomerCardResponse](docs/Model/CreateCustomerCardResponse.md)
 - [CreateCustomerRequest](docs/Model/CreateCustomerRequest.md)
 - [CreateCustomerResponse](docs/Model/CreateCustomerResponse.md)
 - [CreateMobileAuthorizationCodeRequest](docs/Model/CreateMobileAuthorizationCodeRequest.md)
 - [CreateMobileAuthorizationCodeResponse](docs/Model/CreateMobileAuthorizationCodeResponse.md)
 - [CreateOrderRequest](docs/Model/CreateOrderRequest.md)
 - [CreateOrderRequestDiscount](docs/Model/CreateOrderRequestDiscount.md)
 - [CreateOrderRequestLineItem](docs/Model/CreateOrderRequestLineItem.md)
 - [CreateOrderRequestModifier](docs/Model/CreateOrderRequestModifier.md)
 - [CreateOrderRequestTax](docs/Model/CreateOrderRequestTax.md)
 - [CreateOrderResponse](docs/Model/CreateOrderResponse.md)
 - [CreateRefundRequest](docs/Model/CreateRefundRequest.md)
 - [CreateRefundResponse](docs/Model/CreateRefundResponse.md)
 - [Currency](docs/Model/Currency.md)
 - [Customer](docs/Model/Customer.md)
 - [CustomerCreationSource](docs/Model/CustomerCreationSource.md)
 - [CustomerCreationSourceFilter](docs/Model/CustomerCreationSourceFilter.md)
 - [CustomerFilter](docs/Model/CustomerFilter.md)
 - [CustomerGroupInfo](docs/Model/CustomerGroupInfo.md)
 - [CustomerInclusionExclusion](docs/Model/CustomerInclusionExclusion.md)
 - [CustomerPreferences](docs/Model/CustomerPreferences.md)
 - [CustomerQuery](docs/Model/CustomerQuery.md)
 - [CustomerSort](docs/Model/CustomerSort.md)
 - [CustomerSortField](docs/Model/CustomerSortField.md)
 - [DeleteCatalogObjectRequest](docs/Model/DeleteCatalogObjectRequest.md)
 - [DeleteCatalogObjectResponse](docs/Model/DeleteCatalogObjectResponse.md)
 - [DeleteCustomerCardRequest](docs/Model/DeleteCustomerCardRequest.md)
 - [DeleteCustomerCardResponse](docs/Model/DeleteCustomerCardResponse.md)
 - [DeleteCustomerRequest](docs/Model/DeleteCustomerRequest.md)
 - [DeleteCustomerResponse](docs/Model/DeleteCustomerResponse.md)
 - [Device](docs/Model/Device.md)
 - [Error](docs/Model/Error.md)
 - [ErrorCategory](docs/Model/ErrorCategory.md)
 - [ErrorCode](docs/Model/ErrorCode.md)
 - [InventoryAdjustment](docs/Model/InventoryAdjustment.md)
 - [InventoryAlertType](docs/Model/InventoryAlertType.md)
 - [InventoryChange](docs/Model/InventoryChange.md)
 - [InventoryChangeType](docs/Model/InventoryChangeType.md)
 - [InventoryCount](docs/Model/InventoryCount.md)
 - [InventoryPhysicalCount](docs/Model/InventoryPhysicalCount.md)
 - [InventoryState](docs/Model/InventoryState.md)
 - [InventoryTransfer](docs/Model/InventoryTransfer.md)
 - [ItemVariationLocationOverrides](docs/Model/ItemVariationLocationOverrides.md)
 - [ListAdditionalRecipientReceivableRefundsRequest](docs/Model/ListAdditionalRecipientReceivableRefundsRequest.md)
 - [ListAdditionalRecipientReceivableRefundsResponse](docs/Model/ListAdditionalRecipientReceivableRefundsResponse.md)
 - [ListAdditionalRecipientReceivablesRequest](docs/Model/ListAdditionalRecipientReceivablesRequest.md)
 - [ListAdditionalRecipientReceivablesResponse](docs/Model/ListAdditionalRecipientReceivablesResponse.md)
 - [ListCatalogRequest](docs/Model/ListCatalogRequest.md)
 - [ListCatalogResponse](docs/Model/ListCatalogResponse.md)
 - [ListCustomersRequest](docs/Model/ListCustomersRequest.md)
 - [ListCustomersResponse](docs/Model/ListCustomersResponse.md)
 - [ListLocationsRequest](docs/Model/ListLocationsRequest.md)
 - [ListLocationsResponse](docs/Model/ListLocationsResponse.md)
 - [ListRefundsRequest](docs/Model/ListRefundsRequest.md)
 - [ListRefundsResponse](docs/Model/ListRefundsResponse.md)
 - [ListTransactionsRequest](docs/Model/ListTransactionsRequest.md)
 - [ListTransactionsResponse](docs/Model/ListTransactionsResponse.md)
 - [Location](docs/Model/Location.md)
 - [LocationCapability](docs/Model/LocationCapability.md)
 - [LocationStatus](docs/Model/LocationStatus.md)
 - [LocationType](docs/Model/LocationType.md)
 - [Money](docs/Model/Money.md)
 - [ObtainTokenRequest](docs/Model/ObtainTokenRequest.md)
 - [ObtainTokenResponse](docs/Model/ObtainTokenResponse.md)
 - [Order](docs/Model/Order.md)
 - [OrderLineItem](docs/Model/OrderLineItem.md)
 - [OrderLineItemDiscount](docs/Model/OrderLineItemDiscount.md)
 - [OrderLineItemDiscountScope](docs/Model/OrderLineItemDiscountScope.md)
 - [OrderLineItemDiscountType](docs/Model/OrderLineItemDiscountType.md)
 - [OrderLineItemModifier](docs/Model/OrderLineItemModifier.md)
 - [OrderLineItemTax](docs/Model/OrderLineItemTax.md)
 - [OrderLineItemTaxType](docs/Model/OrderLineItemTaxType.md)
 - [Product](docs/Model/Product.md)
 - [Refund](docs/Model/Refund.md)
 - [RefundStatus](docs/Model/RefundStatus.md)
 - [RegisterDomainRequest](docs/Model/RegisterDomainRequest.md)
 - [RegisterDomainResponse](docs/Model/RegisterDomainResponse.md)
 - [RegisterDomainResponseStatus](docs/Model/RegisterDomainResponseStatus.md)
 - [RenewTokenRequest](docs/Model/RenewTokenRequest.md)
 - [RenewTokenResponse](docs/Model/RenewTokenResponse.md)
 - [RetrieveCatalogObjectRequest](docs/Model/RetrieveCatalogObjectRequest.md)
 - [RetrieveCatalogObjectResponse](docs/Model/RetrieveCatalogObjectResponse.md)
 - [RetrieveCustomerRequest](docs/Model/RetrieveCustomerRequest.md)
 - [RetrieveCustomerResponse](docs/Model/RetrieveCustomerResponse.md)
 - [RetrieveInventoryAdjustmentRequest](docs/Model/RetrieveInventoryAdjustmentRequest.md)
 - [RetrieveInventoryAdjustmentResponse](docs/Model/RetrieveInventoryAdjustmentResponse.md)
 - [RetrieveInventoryChangesRequest](docs/Model/RetrieveInventoryChangesRequest.md)
 - [RetrieveInventoryChangesResponse](docs/Model/RetrieveInventoryChangesResponse.md)
 - [RetrieveInventoryCountRequest](docs/Model/RetrieveInventoryCountRequest.md)
 - [RetrieveInventoryCountResponse](docs/Model/RetrieveInventoryCountResponse.md)
 - [RetrieveInventoryPhysicalCountRequest](docs/Model/RetrieveInventoryPhysicalCountRequest.md)
 - [RetrieveInventoryPhysicalCountResponse](docs/Model/RetrieveInventoryPhysicalCountResponse.md)
 - [RetrieveTransactionRequest](docs/Model/RetrieveTransactionRequest.md)
 - [RetrieveTransactionResponse](docs/Model/RetrieveTransactionResponse.md)
 - [RevokeTokenRequest](docs/Model/RevokeTokenRequest.md)
 - [RevokeTokenResponse](docs/Model/RevokeTokenResponse.md)
 - [SearchCatalogObjectsRequest](docs/Model/SearchCatalogObjectsRequest.md)
 - [SearchCatalogObjectsResponse](docs/Model/SearchCatalogObjectsResponse.md)
 - [SearchCustomersRequest](docs/Model/SearchCustomersRequest.md)
 - [SearchCustomersResponse](docs/Model/SearchCustomersResponse.md)
 - [SortOrder](docs/Model/SortOrder.md)
 - [SourceApplication](docs/Model/SourceApplication.md)
 - [TaxCalculationPhase](docs/Model/TaxCalculationPhase.md)
 - [TaxInclusionType](docs/Model/TaxInclusionType.md)
 - [Tender](docs/Model/Tender.md)
 - [TenderCardDetails](docs/Model/TenderCardDetails.md)
 - [TenderCardDetailsEntryMethod](docs/Model/TenderCardDetailsEntryMethod.md)
 - [TenderCardDetailsStatus](docs/Model/TenderCardDetailsStatus.md)
 - [TenderCashDetails](docs/Model/TenderCashDetails.md)
 - [TenderType](docs/Model/TenderType.md)
 - [TimeRange](docs/Model/TimeRange.md)
 - [Transaction](docs/Model/Transaction.md)
 - [TransactionProduct](docs/Model/TransactionProduct.md)
 - [UpdateCustomerRequest](docs/Model/UpdateCustomerRequest.md)
 - [UpdateCustomerResponse](docs/Model/UpdateCustomerResponse.md)
 - [UpdateItemModifierListsRequest](docs/Model/UpdateItemModifierListsRequest.md)
 - [UpdateItemModifierListsResponse](docs/Model/UpdateItemModifierListsResponse.md)
 - [UpdateItemTaxesRequest](docs/Model/UpdateItemTaxesRequest.md)
 - [UpdateItemTaxesResponse](docs/Model/UpdateItemTaxesResponse.md)
 - [UpsertCatalogObjectRequest](docs/Model/UpsertCatalogObjectRequest.md)
 - [UpsertCatalogObjectResponse](docs/Model/UpsertCatalogObjectResponse.md)
 - [V1AdjustInventoryRequest](docs/Model/V1AdjustInventoryRequest.md)
 - [V1BankAccount](docs/Model/V1BankAccount.md)
 - [V1CashDrawerEvent](docs/Model/V1CashDrawerEvent.md)
 - [V1CashDrawerShift](docs/Model/V1CashDrawerShift.md)
 - [V1Category](docs/Model/V1Category.md)
 - [V1CreateRefundRequest](docs/Model/V1CreateRefundRequest.md)
 - [V1Discount](docs/Model/V1Discount.md)
 - [V1Employee](docs/Model/V1Employee.md)
 - [V1EmployeeRole](docs/Model/V1EmployeeRole.md)
 - [V1Fee](docs/Model/V1Fee.md)
 - [V1InventoryEntry](docs/Model/V1InventoryEntry.md)
 - [V1Item](docs/Model/V1Item.md)
 - [V1ItemImage](docs/Model/V1ItemImage.md)
 - [V1Merchant](docs/Model/V1Merchant.md)
 - [V1MerchantLocationDetails](docs/Model/V1MerchantLocationDetails.md)
 - [V1ModifierList](docs/Model/V1ModifierList.md)
 - [V1ModifierOption](docs/Model/V1ModifierOption.md)
 - [V1Money](docs/Model/V1Money.md)
 - [V1Order](docs/Model/V1Order.md)
 - [V1OrderHistoryEntry](docs/Model/V1OrderHistoryEntry.md)
 - [V1Page](docs/Model/V1Page.md)
 - [V1PageCell](docs/Model/V1PageCell.md)
 - [V1Payment](docs/Model/V1Payment.md)
 - [V1PaymentDiscount](docs/Model/V1PaymentDiscount.md)
 - [V1PaymentItemDetail](docs/Model/V1PaymentItemDetail.md)
 - [V1PaymentItemization](docs/Model/V1PaymentItemization.md)
 - [V1PaymentModifier](docs/Model/V1PaymentModifier.md)
 - [V1PaymentSurcharge](docs/Model/V1PaymentSurcharge.md)
 - [V1PaymentTax](docs/Model/V1PaymentTax.md)
 - [V1PhoneNumber](docs/Model/V1PhoneNumber.md)
 - [V1Refund](docs/Model/V1Refund.md)
 - [V1Settlement](docs/Model/V1Settlement.md)
 - [V1SettlementEntry](docs/Model/V1SettlementEntry.md)
 - [V1Tender](docs/Model/V1Tender.md)
 - [V1Timecard](docs/Model/V1Timecard.md)
 - [V1TimecardEvent](docs/Model/V1TimecardEvent.md)
 - [V1UpdateModifierListRequest](docs/Model/V1UpdateModifierListRequest.md)
 - [V1UpdateOrderRequest](docs/Model/V1UpdateOrderRequest.md)
 - [V1Variation](docs/Model/V1Variation.md)
 - [VoidTransactionRequest](docs/Model/VoidTransactionRequest.md)
 - [VoidTransactionResponse](docs/Model/VoidTransactionResponse.md)


## Documentation For Authorization

## oauth2

- **Type**: OAuth
- **Flow**: accessCode
- **Authorization URL**: `https://connect.squareup.com/oauth2/authorize`
- **Scopes**:
 - **MERCHANT_PROFILE_READ**: GET endpoints related to a merchant's business and location entities. Almost all Connect API applications need this permission in order to obtain a merchant's location IDs
 - **PAYMENTS_READ**: GET endpoints related to transactions and refunds
 - **PAYMENTS_WRITE**: POST, PUT, and DELETE endpoints related to transactions and refunds. E-commerce applications must request this permission
 - **CUSTOMERS_READ**: GET endpoints related to customer management
 - **CUSTOMERS_WRITE**: POST, PUT, and DELETE endpoints related to customer management
 - **SETTLEMENTS_READ**: GET endpoints related to settlements (deposits)
 - **BANK_ACCOUNTS_READ**: GET endpoints related to a merchant's bank accounts
 - **ITEMS_READ**: GET endpoints related to a merchant's item library
 - **ITEMS_WRITE**: POST, PUT, and DELETE endpoints related to a merchant's item library
 - **ORDERS_READ**: GET endpoints related to a merchant's orders
 - **ORDERS_WRITE**: POST, PUT, and DELETE endpoints related to a merchant's orders
 - **EMPLOYEES_READ**: GET endpoints related to employee management
 - **EMPLOYEES_WRITE**: POST, PUT, and DELETE endpoints related to employee management
 - **TIMECARDS_READ**: GET endpoints related to employee timecards
 - **TIMECARDS_WRITE**: POST, PUT, and DELETE endpoints related to employee timecards
 - **PAYMENTS_WRITE_ADDITIONAL_RECIPIENTS**: Allow third party applications to deduct a portion of each transaction amount.
 - **PAYMENTS_WRITE_IN_PERSON**: POST, PUT, and DELETE endpoints. Grants write access to transaction and refunds information.
 - **INVENTORY_READ**: GET endpoints related to a merchant's inventory
 - **INVENTORY_WRITE**: POST, PUT, and DELETE endpoints related to a merchant's inventory

## oauth2ClientSecret

- **Type**: API key
- **API key parameter name**: Authorization
- **Location**: HTTP header


## Pagination of V1 Endpoints

V1 Endpoints return pagination information via HTTP headers. In order to obtain
response headers and extract the `batch_token` parameter you will need to follow
the following steps:

1. Use the full information endpoint methods of each API to get the response HTTP
Headers. They are named as their simple counterpart with a `WithHttpInfo` suffix.
Hence `listEmployeeRoles` would be called `listEmployeeRolesWithHttpInfo`. This
method returns an array with 3 parameters: `$response`, `$http_status`, and
`$http_headers`.
2. Use `$batch_token = \SquareConnect\ApiClient::getV1BatchTokenFromHeaders($http_headers)`
to extract the token and proceed to get the following page if a token is present.

### Example

```php
<?php
...
$api_instance = new SquareConnect\Api\V1EmployeesApi();
$order = null;
$limit = 20;
$batch_token = null;
$roles = array();

try {
    do {
        list($result, $status, $headers) = $api_instance->listEmployeeRolesWithHttpInfo($order, $limit, $batch_token);
        $batch_token = \SquareConnect\ApiClient::getV1BatchTokenFromHeaders($headers);
        $roles = array_merge($roles, $result);
    } while (!is_null($batch_token));
    print_r($roles);
} catch (Exception $e) {
    echo 'Exception when calling V1EmployeesApi->listEmployeeRolesWithHttpInfo: ', $e->getMessage(), PHP_EOL;
}
?>
```


Contributing
------------

Send bug reports, feature requests, and code contributions to the [API
specifications repository](https://github.com/square/connect-api-specification),
as this repository contains only the generated SDK code. If you notice something wrong about this SDK in particular, feel free to raise an issue [here](https://github.com/square/connect-php-sdk/issues).

License
-------

```
Copyright 2017 Square, Inc.

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

   http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
```
