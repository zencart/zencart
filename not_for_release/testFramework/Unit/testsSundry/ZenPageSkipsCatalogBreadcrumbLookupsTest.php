<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 *
 * Regression coverage for #7924 / #7921: zen_page_skips_catalog_breadcrumb_lookups()
 * identifies pages (shopping cart, checkout steps) that never legitimately build a
 * breadcrumb from catalog-filter GET params, so init_add_crumbs.php / init_category_path.php
 * can skip the associated database lookups there. Plugins that register their own
 * checkout-flow pages (ie: FILENAME_CHECKOUT_ONE) can extend that list via the
 * NOTIFY_CATALOG_BREADCRUMB_LOOKUP_SKIP_PAGES observer, without touching core.
 */

namespace Tests\Unit\testsSundry;

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\Support\zcUnitTestCase;

#[RunTestsInSeparateProcesses]
class ZenPageSkipsCatalogBreadcrumbLookupsTest extends zcUnitTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        defined('IS_ADMIN_FLAG') || define('IS_ADMIN_FLAG', false);
        defined('FILENAME_DEFAULT') || define('FILENAME_DEFAULT', 'index');
        defined('FILENAME_SHOPPING_CART') || define('FILENAME_SHOPPING_CART', 'shopping_cart');
        defined('FILENAME_CHECKOUT_SHIPPING') || define('FILENAME_CHECKOUT_SHIPPING', 'checkout_shipping');
        defined('FILENAME_CHECKOUT_SHIPPING_ADDRESS') || define('FILENAME_CHECKOUT_SHIPPING_ADDRESS', 'checkout_shipping_address');
        defined('FILENAME_CHECKOUT_PAYMENT') || define('FILENAME_CHECKOUT_PAYMENT', 'checkout_payment');
        defined('FILENAME_CHECKOUT_PAYMENT_ADDRESS') || define('FILENAME_CHECKOUT_PAYMENT_ADDRESS', 'checkout_payment_address');
        defined('FILENAME_CHECKOUT_CONFIRMATION') || define('FILENAME_CHECKOUT_CONFIRMATION', 'checkout_confirmation');
        defined('FILENAME_CHECKOUT_PROCESS') || define('FILENAME_CHECKOUT_PROCESS', 'checkout_process');
        defined('FILENAME_CHECKOUT_SUCCESS') || define('FILENAME_CHECKOUT_SUCCESS', 'checkout_success');

        // Mirrors lat9/one_page_checkout's includes/extra_datafiles/checkout_one_filenames.php
        defined('FILENAME_CHECKOUT_ONE') || define('FILENAME_CHECKOUT_ONE', 'checkout_one');
        defined('FILENAME_CHECKOUT_ONE_CONFIRMATION') || define('FILENAME_CHECKOUT_ONE_CONFIRMATION', 'checkout_one_confirmation');

        require_once DIR_FS_CATALOG . 'includes/classes/traits/NotifierManager.php';
        require_once DIR_FS_CATALOG . 'includes/classes/traits/ObserverManager.php';
        require_once DIR_FS_CATALOG . 'includes/classes/class.base.php';
        require_once DIR_FS_CATALOG . 'includes/classes/class.notifier.php';
        require_once DIR_FS_CATALOG . 'includes/functions/functions_lookups.php';

        $GLOBALS['zco_notifier'] = new \notifier();
    }

    public function testCorePagesAreSkipped(): void
    {
        $this->assertTrue(\zen_page_skips_catalog_breadcrumb_lookups(FILENAME_SHOPPING_CART));
        $this->assertTrue(\zen_page_skips_catalog_breadcrumb_lookups(FILENAME_CHECKOUT_SHIPPING));
        $this->assertTrue(\zen_page_skips_catalog_breadcrumb_lookups(FILENAME_CHECKOUT_PAYMENT));
        $this->assertTrue(\zen_page_skips_catalog_breadcrumb_lookups(FILENAME_CHECKOUT_CONFIRMATION));
        $this->assertTrue(\zen_page_skips_catalog_breadcrumb_lookups(FILENAME_CHECKOUT_PROCESS));
        $this->assertTrue(\zen_page_skips_catalog_breadcrumb_lookups(FILENAME_CHECKOUT_SUCCESS));
    }

    public function testAnOrdinaryPageIsNotSkipped(): void
    {
        $this->assertFalse(\zen_page_skips_catalog_breadcrumb_lookups(FILENAME_DEFAULT));
    }

    public function testAPluginCanRegisterAnAdditionalCheckoutPageWithoutRemovingCoreEntries(): void
    {
        $observer = new OnePageCheckoutStubObserver();
        $GLOBALS['zco_notifier']->attach($observer, ['NOTIFY_CATALOG_BREADCRUMB_LOOKUP_SKIP_PAGES']);

        // The plugin's own page is now skipped too...
        $this->assertTrue(\zen_page_skips_catalog_breadcrumb_lookups(FILENAME_CHECKOUT_ONE));
        $this->assertTrue(\zen_page_skips_catalog_breadcrumb_lookups(FILENAME_CHECKOUT_ONE_CONFIRMATION));

        // ...and every core entry is still intact.
        $this->assertTrue(\zen_page_skips_catalog_breadcrumb_lookups(FILENAME_SHOPPING_CART));
        $this->assertTrue(\zen_page_skips_catalog_breadcrumb_lookups(FILENAME_CHECKOUT_SHIPPING));

        // An unrelated page remains not-skipped.
        $this->assertFalse(\zen_page_skips_catalog_breadcrumb_lookups(FILENAME_DEFAULT));
    }

    public function testAMisbehavingObserverCannotRemoveCoreEntries(): void
    {
        // $excludedPages (the core list) is passed to observers by value, not by
        // reference -- so even an observer that tries to clear/reassign it can only
        // affect its own local copy, never the caller's list.
        $observer = new MisbehavingStubObserver();
        $GLOBALS['zco_notifier']->attach($observer, ['NOTIFY_CATALOG_BREADCRUMB_LOOKUP_SKIP_PAGES']);

        $this->assertTrue(\zen_page_skips_catalog_breadcrumb_lookups(FILENAME_SHOPPING_CART));
        $this->assertTrue(\zen_page_skips_catalog_breadcrumb_lookups(FILENAME_CHECKOUT_PAYMENT));
    }
}

/**
 * Stands in for a plugin (e.g. lat9/one_page_checkout) extending the core skip-list via
 * the NOTIFY_CATALOG_BREADCRUMB_LOOKUP_SKIP_PAGES observer's by-reference
 * $additionalExcludedPages ($param2).
 */
class OnePageCheckoutStubObserver
{
    public function update($class, $eventID, &$param1 = null, &$param2 = null): void
    {
        if ($eventID !== 'NOTIFY_CATALOG_BREADCRUMB_LOOKUP_SKIP_PAGES') {
            return;
        }

        $param2[] = FILENAME_CHECKOUT_ONE;
        $param2[] = FILENAME_CHECKOUT_ONE_CONFIRMATION;
    }
}

/**
 * Attempts to empty out $excludedPages ($param1) -- since that parameter is passed by
 * value, this can only ever mutate the observer's local copy, not the caller's list.
 */
class MisbehavingStubObserver
{
    public function update($class, $eventID, &$param1 = null, &$param2 = null): void
    {
        if ($eventID !== 'NOTIFY_CATALOG_BREADCRUMB_LOOKUP_SKIP_PAGES') {
            return;
        }

        $param1 = [];
    }
}
