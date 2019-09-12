# Change Log

## Version 2.20181205.0 (2018-12-05)

## New feature: Idempotent customer profile creation in Connect v2

Requests to the CreateCustomer endpoint now include a `idempotency_key` field to
ensure idempotent creation of new profiles.

## New feature: Refund Adjustment fields for Refunds in Connect v1

The Connect SDK now supports refund adjustments for the Connect v1
Refunds API with the addition of multiple new fields in the `Refund` data type

## Version 2.20180918.1 (2018-10-24)

### New feature: Support for Partial Payments in Connect v1

The Connect SDK now supports partial payment functionality for the Connect v1 Transactions API with the addition of a new `Payment` field:
* `Payment.is_partial` &mdash; Indicates whether or not the payment is only partially paid for. If `true`, the payment will have the tenders collected so far, but the itemizations will be empty until the payment is completed.

`Tender` also includes 2 new fields to help resolve timing around payments with multiple tenders. Invoices that involve partial payment (e.g., requiring a deposit) may include tenders settled well before the entire payment is completed:
* `Tender.tendered_at` &mdash; The time when the tender was accepted by the merchant.
* `Tender.settled_at` &mdash; The time when the tender was captured, in ISO 8601 format. Typically the same as (or within moments of) `tendered_at` unless the tender was part of a delay capture transaction.

The change also makes some behavioral changes to the Connect v1 Payment endpoints:
* **Create Refunds** rejects requests for invoices that have partial payments pending.
* **List Payments** takes a new request field, `include_partial` to indicate whether partial payments should be included in the response.

## Version 2.20180918.0 (2018-09-18)

We have added Connect v2 Inventory API and birthdays in `Customer` entities.

### New API: Inventory API (Connect V2)

The Connect v2 Inventory API replaces the Connect v1 Inventory API
and introduces new functionality:

* Moving item variations quantities through predefined states
  (e.g., from `IN_STOCK` to `WASTE`).
* Viewing the inventory adjustment history for an item variation.
* Batch inventory adjustments and information retrieval.

### New feature: Customer Birthdays (Connect V2)

* Customer profiles now include a `birthday` field.
  Dates are recorded in RFC-3339 format and can be
  set through the `CreateCustomer` and `UpdateCustomer` endpoints.
## Version 2.20180712.2 (2018-08-21)

The Connect SDK now includes functionality for the OAuth API. The Square OAuth API lets applications request and obtain permission from a Square account to make API calls on behalf of that account. Applications can request individual permissions so that users do not need to grant full access to their Square accounts.

### OAuth API

* `ObtainToken` endpoint &mdash; Exchanges the authorization code for an access token.  After a merchant authorizes your application with the permissions form, an authorization code is sent to the application's redirect URL (See [Implementing OAuth](https://docs.connect.squareup.com/api/oauth#implementingoauth) for information about how to set up the redirect URL).

* `RenewToken` endpoint &mdash; Renews an OAuth access token before it expires.  OAuth access tokens besides your application's personal access token expire after __30 days__. You can also renew expired tokens within __15 days__ of their expiration. You cannot renew an access token that has been expired for more than 15 days. Instead, the associated merchant must complete the [OAuth flow](https://docs.connect.squareup.com/api/oauth#implementingoauth) from the beginning.  __Important:__ The `Authorization` header you provide to this endpoint must have the following format:  ``` Authorization: Client APPLICATION_SECRET ```  Replace `APPLICATION_SECRET` with your application's secret, available from the [application dashboard](https://connect.squareup.com/apps).
* `RevokeToken` endpoint &mdash; Revokes an access token generated with the OAuth flow.  If a merchant has more than one access token for your application, this endpoint revokes all of them, regardless of which token you specify. If you revoke a merchant's access token, all of the merchant's active subscriptions associated with your application are canceled immediately.  __Important:__ The `Authorization` header you provide to this endpoint must have the following format:  ``` Authorization: Client APPLICATION_SECRET ```  Replace `APPLICATION_SECRET` with your application's secret, available from the [application dashboard](https://connect.squareup.com/apps).

## Version 2.20180712.1 (2018-08-02)

We have added MobileAuthorization API.

### New endpoint: MobileAuthorization API

* `CreateMobileAuthorizationCode` endpoint &mdash; Generate a mobile authorization code for an instance of your application. Mobile authorization credentials permit an instance of your application to accept payments for a given location using the Square Reader SDK. Mobile authorization codes are one-time-use and expire shortly after being issued.

## Version 2.20180712.0 (2018-07-12)

We introduce Square API versions. `Square-Version` is 2018-07-12 for this SDK.

### How versioning works

Square API versions (`Square-Version`) track changes in the evolution of Connect
v2 APIs. The `Square-Version` naming scheme is `YYYY-MM-DD`, which indicates
the date the version was released. Connect v1 APIs are not versioned. Square
continues to support Connect v1, but future releases will focus on improving
Connect v2 functionality.

By default, new Square applications are pinned to the version current at the
time the application was created in the Square Application Dashboard. Pinning an
application sets the default `Square-Version` for the application. The default
`Square-Version` of an application can be reviewed and updated at any time on
the settings pages for the application.


### Versioning and SDKs

When a new `Square-Version` is released, new Connect SDKs are publish on GitHub
and various package management systems. SDK updates follow the version
convention of the associated language and manager but include the related
`Square-Version` in the SDK version. For example, Connect SDKs tied to version
`2018-01-04` might look like `{SDK_VERSION}.20180104.{VERSION_INCREMENT}`.

While SDK versions can be mapped to a related Square-version, SDK versions
follow an independent, incremental versioning scheme to allow updates and
improvements to the SDKs outside of `Square-Version` updates.


### Migrating to new versions

In most cases, Square-version migration should be straightforward, with known
differences listed in the related Change Log.

To test migrations, developers can override the default `Square-Version` of an
application by explicitly setting the preferred `Square-Version` in the HTTP
header of the Connect v2 API request for REST calls. Requesting an API version
that does not exist returns an error. Successful API responses include the
`Square-Version` header to indicate the API version used to process request.

Connect SDK versions are locked to specific API versions and cannot be
overwritten. Instead, the SDK must be upgraded to work with new API versions.

## Version 2.9.0 (2018-06-28)

We have added search functionality to the Connect v2 Customer API.

### New features and Improvements: Customer API (Connect v2)

* `SearchCustomers` endpoint &mdash; retrieves groups of customer profiles
  based on a related characteristic. For example, retrieving all customers
  created in the past 24 hours.
* `creation_source` field is now available on `Customer` entities. The creation
  source exposes the process that created a customer profile. For example, if
  a customer is created using the API, the creation source will be
  `THIRD_PARTY`.
* **Instant Profiles** are now exposed in the following endpoints:
  `RetrieveCustomer`, `SearchCustomers`, `UpdateCustomer`, `DeleteCustomer`.

### Fixes: Inventory SDK (Connect v1)

* Fix SDK request property `adjustment_type` in V1 Adjust Inventory.

## Version 2.8.0 (2018-05-24)

We have added sorting functionality to the Connect v2 Customer API, updated
the Connect v1 Payments API to include information about surcharges and
improvements to the Item data type.

### New feature: Customer API (Connect v2)

* **ListCustomers** endpoint &mdash; now provides the ability to sort
  customers by their creation date using the `sort_field` and
  `sort_order` parameters.

### New features: Payments API (Connect v1)

The Payments API now returns information about surcharges applied to payments.
The new functionality introduces the following new data types:

* **SurchargeMoney** datatype &mdash; The total of all surcharges applied
  to the payment.
* **Surcharges** datatype &mdash; A list of all surcharges associated with
  the payment.
* **Surcharge** datatype &mdash; A surcharge that is applied to the payment.
  One example of a surcharge is auto-gratuity, which is a fixed-rate surcharge
  applied to every payment, often based on party size.

We are constantly evaluating new languages to add. In the meantime, if the
language you need is not supported, you can use our
[Swagger pipeline](<%= articles__client_libraries_path%>#generatingwithswagger)
to generate a custom SDK or continue sending JSON to the endpoint URLs directly.

### Improvement: Item (Connect v1)

**Item** will now provide two new properties:

* `category_id` &mdash; indicates if an item can be added to pickup orders
  from the merchant's online store
* `available_for_pickup` &mdash; indicates the item's category (if any).

## Version 2.7.0 (2018-04-26)

### New features: Transactions API and Payments API

The Transactions API in Connect v2 now includes payment and refund information from exchanges.

* `ListTransactions` now includes payment information from sales and exchanges and refund
information from returns and exchanges.
* `ListRefunds` now includes refunds that result from exchanges in addition to partial refunds and
itemized returns through Square's Point of Sale applications.

The Payments API in Connect v1 now includes payment and refund information from exchanges.

* `ListPayments` now includes refunds that are generated from exchanges to account for the
value of returned goods.
* `ListRefunds` now returns an approximate number of refunds (default: 100, max: 200).
The response may contain more results than the prescribed limit when refunds are made
simultaneously to multiple tenders in a payment or when refunds are generated from exchanges
to account for the value of returned goods.
* `is_exchange` is added to `V1Refund` and `V1Tender`. Refunds and tenders marked in this way
represent the value of returned goods in an exchange, rather than actual money movement.

## Version 2.6.1 (2018-03-28)

* Updates user-agent header

## Version 2.6.0 (2018-03-27)

### Improvements: Orders API

* `BatchRetrieveOrders` will now return uncharged orders.

### New features: Orders API

* For Catalog-backed line items, setting `CreateOrderRequestLineItem.base_price_money` will now override
  the catalog item variation's price.
* `CreateOrderRequestModifier`s may now be created ad hoc using the new `name` and `base_price_money` fields.

## Version 2.5.1 (2017-11-10)

* `ordinal` is added to `CatalogItemVariation`
* `website_url` is added to `Location`
* `tip_money` is added to `Tender`
* Changed `object_type` and `placeholder_type` from lists to single values in `V1PageCell` to mirror data model

## Version 2.5 (2017-11-02)

### New features: Transaction API and Reporting API

The Transaction API now supports the following request objects:

* `additional_recipients` &mdash; data type representing an additional recipient
  (in other words, a recipient other than the merchant or Square) receiving a
  portion of a tender.

The new Reporting API includes two endpoints that let you pull information about distributions you have received as an additional recipient:

* `AdditionalRecipientReceivables` &mdash; returns a list of receivables (across
  all source locations) representing monies credited to the given location ID by
  another Square account using the `additional_recipients` field in a transaction.

* `AdditionalRecipientReceivablesRefunds` &mdash; returns a list of refunded
  transactions (across all source locations) related to monies credited to the
  given location ID by another Square account using the `additional_recipients`
  field in a transaction.

## Version 2.4 (2017-09-27)

### New features: Register Domain for Apple Pay

* `RegisterDomain` endpoint activates a domain for use with Web Apple Pay.

### Other Changes

* `Location.type` used to indicate whether or not the location object represents a physical space.

## Version 2.3.1 (2017-09-15)

### New features: Charge Orders

* `Charge` endpoint can charge a specific Order.

## Version 2.3.0 (2017-09-13)

### New features: Orders API

* `CreateOrder` endpoint creates an itemized order which can be referenced in messages to the `Charge` endpoint.
* `BatchRetrieveOrders` retrieves order objects referenced in one or more transactions based on the provided `order_id`.

**Note:** at this point, `BatchRetrieveOrders only` returns information on paid orders made through Square's eCommerce APIs (Transactions, Checkout).

### Other Changes

* `order` is removed from Transaction
* `order_id` is added to Transaction
* `OrderLineItemDiscountType.UNKOWN` renamed to `OrderLineItemDiscountType.UNKOWN_DISCOUNT`
* `OrderLineItemTaxType.UNKOWN` renamed to `OrderLineItemDiscountType.UNKOWN_TAX`
* `ChargeRequest.idempotency_key` is restricted to max length 192
* `ChargeRequest.card_nonce` is restricted to max length 192
* `ChargeRequest.customer_card_id` is restricted to max length 192
* `ChargeRequest.reference_id` is restricted to max length 40
* `ChargeRequest.note` is restricted to max length 60
* `ChargeRequest.customer_id` is restricted to max length 50
* `CreateCheckoutRequest.redirect_url` is restricted to max length 800
* Added `phone_number` and `business_name` to Location

## Version 2.2.1 (2017-08-11)

* Documentation style and links fixes
* Document Getters and Setters for protected properties
* Support for pagination on V1 endpoints
* Include refund detail fields on V1 Refund model

### Version 2.0.2 (2017-01-20)

* Bug Fixes for List Locations endpiont.

### Version 2.0.1 (2017-01-19)

* Add functionality for Square Checkout.

### Version 2.0.0.1 (2016-05-19)

* Improve error messaging for API connection failures.

## Version 2.0.0 (2016-03-30)

* Initial release of the SDK
