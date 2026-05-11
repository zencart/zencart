<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Unit\testsCategories;

use Tests\Support\zcUnitTestCase;

/**
 * Unit Tests for password hashing rules
 */
class CategoriesTreeTest extends zcUnitTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        require_once DIR_FS_CATALOG . 'includes/functions/functions_general.php';
        require_once DIR_FS_CATALOG . DIR_WS_CLASSES . 'category_tree.php';
    }

    public function testBuildsACategoryTreeForTheLegacyCategorySidebox()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
}
