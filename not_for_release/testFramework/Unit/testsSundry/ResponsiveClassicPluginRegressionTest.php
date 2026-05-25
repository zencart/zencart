<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace {
if (!function_exists('zen_href_link')) {
    function zen_href_link(string $filename, string $parameters = ''): string
    {
        return $filename . ($parameters === '' ? '' : '?' . $parameters);
    }
}
}

namespace Tests\Unit\testsSundry {

use Tests\Support\zcUnitTestCase;

class ResponsiveClassicPluginRegressionTest extends zcUnitTestCase
{
    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    public function testCategoriesSideboxShowsSpecialsLinkWhenSpecialsExist(): void
    {
        defined('SHOW_CATEGORIES_SEPARATOR_LINK') || define('SHOW_CATEGORIES_SEPARATOR_LINK', '0');
        defined('SHOW_CATEGORIES_BOX_SPECIALS') || define('SHOW_CATEGORIES_BOX_SPECIALS', 'true');
        defined('SHOW_CATEGORIES_BOX_PRODUCTS_NEW') || define('SHOW_CATEGORIES_BOX_PRODUCTS_NEW', 'false');
        defined('SHOW_CATEGORIES_BOX_FEATURED_PRODUCTS') || define('SHOW_CATEGORIES_BOX_FEATURED_PRODUCTS', 'false');
        defined('SHOW_CATEGORIES_BOX_FEATURED_CATEGORIES') || define('SHOW_CATEGORIES_BOX_FEATURED_CATEGORIES', 'false');
        defined('SHOW_CATEGORIES_BOX_PRODUCTS_ALL') || define('SHOW_CATEGORIES_BOX_PRODUCTS_ALL', 'false');
        defined('TABLE_SPECIALS') || define('TABLE_SPECIALS', 'specials');
        defined('FILENAME_SPECIALS') || define('FILENAME_SPECIALS', 'specials');
        defined('CATEGORIES_BOX_HEADING_SPECIALS') || define('CATEGORIES_BOX_HEADING_SPECIALS', 'Specials');

        $GLOBALS['db'] = new class {
            public function Execute(string $sql): object
            {
                return (object) ['EOF' => false];
            }
        };
        $db = $GLOBALS['db'];
        $box_id = 'categories';
        $box_categories_array = [];

        ob_start();
        require DIR_FS_CATALOG . 'zc_plugins/ResponsiveClassicPlugin/v1.0.0/catalog/includes/templates/responsive_classic_plugin/sideboxes/tpl_categories.php';
        ob_end_clean();

        $this->assertStringContainsString('href="specials"', $content);
        $this->assertStringContainsString('Specials', $content);
    }
}
}
