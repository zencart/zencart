<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Unit\testsCategories;

use Tests\Support\zcUnitTestCase;

class CpathRedirectTest extends zcUnitTestCase
{
    protected $preserveGlobalState = false;
    protected $runTestInSeparateProcess = true;

    public function setUp(): void
    {
        parent::setUp();

        defined('SEARCH_ENGINE_FRIENDLY_URLS') || define('SEARCH_ENGINE_FRIENDLY_URLS', 'false');
        defined('ENABLE_SSL') || define('ENABLE_SSL', 'false');

        require_once DIR_FS_CATALOG . 'includes/functions/functions_categories.php';
        require_once DIR_FS_CATALOG . 'includes/functions/functions_general_shared.php';
        require_once DIR_FS_CATALOG . 'includes/functions/functions_strings.php';
        require_once DIR_FS_CATALOG . 'includes/functions/functions_urls.php';
        require_once DIR_FS_CATALOG . 'includes/functions/html_output.php';

        $_GET = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $GLOBALS['current_page_base'] = 'index';
    }

    public function testRedirectRetainsTheParametersCanonicalDeemedKeepable(): void
    {
        $_GET = [
            'main_page' => 'index',
            'cPath' => '23_22_21_3',
            'page' => '2',
            'manufacturers_id' => '5',
        ];
        $this->setCanonicalParamFilter();

        $redirect = $this->captureRedirect('1_3');

        $this->assertStringContainsString('cPath=1_3', $redirect['url']);
        $this->assertStringContainsString('page=2', $redirect['url']);
        $this->assertStringContainsString('manufacturers_id=5', $redirect['url']);
    }

    public function testRedirectDropsParametersCanonicalExcludes(): void
    {
        $_GET = [
            'main_page' => 'index',
            'cPath' => '23_3',
            'sort' => '20a',
            'filter_id' => '7',
            'utm_source' => 'somebot',
            'my_rogue_param' => 'junk',
        ];
        $this->setCanonicalParamFilter();

        $redirect = $this->captureRedirect('1_3');

        $this->assertStringNotContainsString('sort=', $redirect['url']);
        $this->assertStringNotContainsString('filter_id=', $redirect['url']);
        $this->assertStringNotContainsString('utm_source', $redirect['url']);
        $this->assertStringNotContainsString('my_rogue_param', $redirect['url']);
    }

    public function testProductRedirectIncludesProductIdAndDropsCartActions(): void
    {
        $_GET = [
            'main_page' => 'product_info',
            'cPath' => '99_5',
            'products_id' => '17',
            'action' => 'add_product',
            'notify' => '17',
        ];
        $this->setCanonicalParamFilter();
        $GLOBALS['current_page_base'] = 'product_info';

        $redirect = $this->captureRedirect('1_9', 17);

        $this->assertStringContainsString('main_page=product_info', $redirect['url']);
        $this->assertStringContainsString('cPath=1_9', $redirect['url']);
        $this->assertStringContainsString('products_id=17', $redirect['url']);
        $this->assertStringNotContainsString('action=', $redirect['url']);
        $this->assertStringNotContainsString('notify=', $redirect['url']);
    }

    /**
     * Without init_canonical's parameter filter there is no allow-list to consult, so nothing
     * beyond the path itself may be reflected into a permanently-cacheable redirect.
     */
    public function testRedirectFailsClosedWhenTheCanonicalParamFilterIsUnavailable(): void
    {
        $_GET = ['main_page' => 'index', 'cPath' => '23_3', 'page' => '2', 'my_rogue_param' => 'junk'];
        unset($GLOBALS['excludeParams']);

        $redirect = $this->captureRedirect('1_3');

        $this->assertStringContainsString('cPath=1_3', $redirect['url']);
        $this->assertStringNotContainsString('page=2', $redirect['url']);
        $this->assertStringNotContainsString('my_rogue_param', $redirect['url']);
    }

    public function testRedirectIssuesA301WithoutASessionIdAndUsesTheSanitizedPage(): void
    {
        // conditions under which zen_href_link() would append the session id to the link
        $GLOBALS['session_started'] = true;
        $GLOBALS['request_type'] = 'SSL';
        $GLOBALS['http_domain'] = 'zencart-git.local';
        $GLOBALS['https_domain'] = 'secure.zencart-git.local';
        defined('SID') || define('SID', 'zenid=1234567890');

        $_GET = ['main_page' => '../../evil', 'cPath' => '23_3'];
        $this->setCanonicalParamFilter();

        $redirect = $this->captureRedirect('1_3');

        $this->assertSame(301, $redirect['httpResponseCode']);
        $this->assertStringNotContainsString('zenid', $redirect['url']);
        $this->assertStringContainsString('main_page=index', $redirect['url']);
        $this->assertStringNotContainsString('evil', $redirect['url']);
    }

    public function testNonGetRequestsAreNotRedirected(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_GET = ['cPath' => '23_3'];
        $this->setCanonicalParamFilter();

        $this->assertNull($this->captureRedirect('1_3'));
    }

    /**
     * Stand in for init_canonical, which leaves its parameter filter in the global scope with
     * every unrecognized parameter of the request appended to it.
     */
    private function setCanonicalParamFilter(): void
    {
        $excludeParams = ['action', 'disp_order', 'filter_id', 'notify', 'sort', 'utm_source', 'zenid'];
        $keepableParams = ['cPath', 'manufacturers_id', 'page', 'products_id'];

        foreach (array_keys($_GET) as $key) {
            if (!in_array($key, $excludeParams, true) && !in_array($key, $keepableParams, true)) {
                $excludeParams[] = $key;
            }
        }

        $GLOBALS['excludeParams'] = $excludeParams;
    }

    /**
     * Capture what zen_redirect() was asked to do, by cancelling the redirect via
     * NOTIFY_ZEN_REDIRECT so that the test process isn't exited.
     */
    private function captureRedirect(string $valid_cPath, int $products_id = 0): ?array
    {
        $capture = new class {
            public ?array $redirect = null;

            public function update(&$class, $eventID, $params, &$request_handled)
            {
                $this->redirect = $params;
                $request_handled = true;
            }
        };
        $GLOBALS['zco_notifier']->attach($capture, ['NOTIFY_ZEN_REDIRECT']);

        \zen_redirect_to_valid_cpath($valid_cPath, $products_id);

        return $capture->redirect;
    }
}
