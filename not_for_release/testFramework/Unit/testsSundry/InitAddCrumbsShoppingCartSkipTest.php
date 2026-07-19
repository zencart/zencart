<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 *
 * Regression coverage for #7924 / #7921: init_add_crumbs.php used to look up every
 * registered "get term" (manufacturer, music genre, etc.) filter GET-param against the
 * database on every page, even pages like the shopping cart that never use them -- letting
 * bots trigger wasted queries by spoofing arbitrary query-string params. It now skips that
 * lookup entirely when $current_page is the shopping cart page.
 */

namespace Tests\Unit\testsSundry;

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\Support\zcUnitTestCase;

#[RunTestsInSeparateProcesses]
class InitAddCrumbsShoppingCartSkipTest extends zcUnitTestCase
{
    private string $initAddCrumbsFile;

    public function setUp(): void
    {
        parent::setUp();

        defined('IS_ADMIN_FLAG') || define('IS_ADMIN_FLAG', false);
        defined('HEADER_TITLE_CATALOG') || define('HEADER_TITLE_CATALOG', 'Catalog');
        defined('FILENAME_DEFAULT') || define('FILENAME_DEFAULT', 'index');
        defined('FILENAME_SHOPPING_CART') || define('FILENAME_SHOPPING_CART', 'shopping_cart');
        defined('TABLE_GET_TERMS_TO_FILTER') || define('TABLE_GET_TERMS_TO_FILTER', 'get_terms_to_filter');
        defined('HTTP_SERVER') || define('HTTP_SERVER', 'https://example.test');
        defined('HTTPS_SERVER') || define('HTTPS_SERVER', 'https://example.test');
        defined('ENABLE_SSL') || define('ENABLE_SSL', 'false');
        defined('DIR_WS_CATALOG') || define('DIR_WS_CATALOG', '/');
        defined('DIR_WS_HTTPS_CATALOG') || define('DIR_WS_HTTPS_CATALOG', '/');
        defined('SESSION_FORCE_COOKIE_USE') || define('SESSION_FORCE_COOKIE_USE', 'False');
        defined('SEARCH_ENGINE_FRIENDLY_URLS') || define('SEARCH_ENGINE_FRIENDLY_URLS', 'false');

        $GLOBALS['request_type'] = 'NONSSL';
        $GLOBALS['session_started'] = false;
        $GLOBALS['http_domain'] = 'example.test';
        $GLOBALS['https_domain'] = 'example.test';

        require_once DIR_FS_CATALOG . 'includes/functions/functions_strings.php';
        require_once DIR_FS_CATALOG . 'includes/functions/html_output.php';
        require_once DIR_FS_CATALOG . 'includes/classes/breadcrumb.php';

        $GLOBALS['zco_notifier'] = new \notifier();

        $this->initAddCrumbsFile = DIR_FS_CATALOG . 'includes/init_includes/init_add_crumbs.php';

        $GLOBALS['db'] = $this->getMockBuilder(\queryFactory::class)->getMock();

        $GLOBALS['breadcrumb'] = $this->getMockBuilder(\Breadcrumb::class)->getMock();
    }

    public function testGetTermsLookupIsSkippedOnTheShoppingCartPage(): void
    {
        $GLOBALS['current_page'] = FILENAME_SHOPPING_CART;
        $_GET['manufacturers_id'] = 8;

        $GLOBALS['db']->expects($this->never())->method('Execute');

        $this->requireInitAddCrumbs();
    }

    public function testGetTermsLookupStillRunsOnAnOrdinaryPage(): void
    {
        $GLOBALS['current_page'] = FILENAME_DEFAULT;
        $_GET['manufacturers_id'] = 8;

        // foreach($get_terms as ...) needs an Iterator-shaped return value.
        $emptyResult = $this->getMockBuilder(\queryFactoryResult::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['current', 'key', 'next', 'rewind', 'valid'])
            ->getMock();
        $emptyResult->method('valid')->willReturn(false);

        $GLOBALS['db']->expects($this->atLeastOnce())->method('Execute')->willReturn($emptyResult);

        $this->requireInitAddCrumbs();
    }

    /**
     * A plain require() from inside a method only has access to that method's local
     * scope, but init_add_crumbs.php (like all init files) is written to run in the
     * including script's scope directly and references $breadcrumb/$db/$current_page as
     * bare globals -- so those need pulling into local scope here before requiring it.
     */
    private function requireInitAddCrumbs(): void
    {
        global $breadcrumb, $db, $current_page, $zco_notifier;

        require $this->initAddCrumbsFile;
    }
}
