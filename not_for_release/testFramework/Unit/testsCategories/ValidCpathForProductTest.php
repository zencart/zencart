<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Unit\testsCategories;

use Tests\Support\zcUnitTestCase;

class ValidCpathForProductTest extends zcUnitTestCase
{
    protected $preserveGlobalState = false;
    protected $runTestInSeparateProcess = true;

    public function setUp(): void
    {
        parent::setUp();

        defined('TABLE_CATEGORIES') || define('TABLE_CATEGORIES', 'categories');
        defined('TOPMOST_CATEGORY_PARENT_ID') || define('TOPMOST_CATEGORY_PARENT_ID', 0);

        require_once DIR_FS_CATALOG . 'includes/classes/db/mysql/query_factory.php';
        require_once DIR_FS_CATALOG . 'includes/functions/functions_categories.php';
        require_once DIR_FS_CATALOG . 'includes/functions/functions_products.php';

        $GLOBALS['db'] = $this->getMockBuilder(\queryFactory::class)->getMock();
    }

    public function testMasterCategoryUsesPathAlreadyLoadedByProduct(): void
    {
        $product = $this->createProduct([
            'master_categories_id' => '9',
            'linked_categories' => ['14'],
            'cPath' => '1_9',
        ]);

        $GLOBALS['db']->expects($this->never())->method('Execute');

        $this->assertSame('1_9', \zen_get_valid_cpath_for_product($product, 9));
    }

    public function testDirectlyLinkedCategoryUsesItsGeneratedPath(): void
    {
        $product = $this->createProduct([
            'master_categories_id' => '9',
            'linked_categories' => ['14'],
            'cPath' => '1_9',
        ]);

        $GLOBALS['db']->expects($this->exactly(2))
            ->method('Execute')
            ->willReturnCallback([$this, 'parentCategoryQuery']);

        $this->assertSame('3_14', \zen_get_valid_cpath_for_product($product, 14));
    }

    public function testUnrelatedCategoryFallsBackToMasterPathWithoutAQuery(): void
    {
        $product = $this->createProduct([
            'master_categories_id' => '9',
            'linked_categories' => ['14'],
            'cPath' => '1_9',
        ]);

        $GLOBALS['db']->expects($this->never())->method('Execute');

        $this->assertSame('1_9', \zen_get_valid_cpath_for_product($product, 22));
    }

    public function testInvalidTerminalCategoryFallsBackToMasterPathWithoutAQuery(): void
    {
        $product = $this->createProduct([
            'master_categories_id' => '9',
            'linked_categories' => ['14'],
            'cPath' => '1_9',
        ]);

        $GLOBALS['db']->expects($this->never())->method('Execute');

        $this->assertSame('1_9', \zen_get_valid_cpath_for_product($product, 0));
    }

    public function testProductWithoutAMasterCategoryDoesNotProduceAPath(): void
    {
        $product = $this->createProduct([
            'master_categories_id' => '0',
            'linked_categories' => [],
            'cPath' => '0',
        ]);

        $GLOBALS['db']->expects($this->never())->method('Execute');

        $this->assertSame('', \zen_get_valid_cpath_for_product($product, 0));
    }

    private function createProduct(array $data): \Product
    {
        $product = $this->createStub(\Product::class);
        $product->method('get')
            ->willReturnCallback(static fn (string $name) => $data[$name] ?? null);

        return $product;
    }

    public function parentCategoryQuery(string $sql): \queryFactoryResult
    {
        preg_match('/categories_id = (\d+)/', $sql, $matches);
        $parent_ids = [14 => 3, 3 => TOPMOST_CATEGORY_PARENT_ID];

        $result = new \queryFactoryResult(null);
        $result->is_cached = true;
        $result->result = isset($matches[1], $parent_ids[(int)$matches[1]])
            ? [['parent_id' => $parent_ids[(int)$matches[1]]]]
            : [];

        return $result;
    }
}
