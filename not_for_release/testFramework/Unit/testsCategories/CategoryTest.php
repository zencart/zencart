<?php

declare(strict_types=1);

namespace Tests\Unit\testsCategories;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\Support\ScriptedDatabase;
use Tests\Support\zcUnitTestCase;

/**
 * Unit tests for the Category value object: loading, language handling and accessors.
 */
#[AllowMockObjectsWithoutExpectations]
#[RunTestsInSeparateProcesses]
class CategoryTest extends zcUnitTestCase
{
    use ScriptedDatabase;

    private const CATEGORY_SQL = '/FROM \S*categories c\s/';
    private const DESCRIPTION_SQL = '/FROM \S*categories_description/';

    public function setUp(): void
    {
        parent::setUp();
        require_once DIR_FS_CATALOG . 'includes/classes/class.base.php';
        require_once DIR_FS_CATALOG . 'includes/classes/Category.php';

        $GLOBALS['lng'] = new class {
            public function get_language_list(): array
            {
                return [1 => 'en', 2 => 'fr'];
            }
        };
        $_SESSION['languages_id'] = 1;

        $this->useScriptedDatabase();
        $this->respondTo(self::DESCRIPTION_SQL, [
            ['categories_id' => 5, 'language_id' => 1, 'categories_name' => 'Hardware', 'categories_description' => 'Hard things'],
            ['categories_id' => 5, 'language_id' => 2, 'categories_name' => 'Materiel', 'categories_description' => 'Choses dures'],
        ]);
        $this->respondTo(self::CATEGORY_SQL, [
            ['categories_id' => 5, 'parent_id' => 0, 'categories_status' => '1', 'product_type_id' => 1],
        ]);
    }

    public function testCategoryWithoutAnIdDoesNotQueryTheDatabase(): void
    {
        $category = new \Category();

        $this->assertNull($category->getId());
        $this->assertFalse($category->exists());
        $this->assertTrue($category->EOF);
        $this->assertSame([], $this->executedQueries);
    }

    public function testLoadingACategoryMergesBaseRowAndPerLanguageDescriptions(): void
    {
        $category = new \Category(5);

        $this->assertSame(5, $category->getId());
        $this->assertTrue($category->exists());
        $this->assertTrue($category->isValid());
        $this->assertFalse($category->EOF);
        $this->assertSame($category->getData(), $category->fields);
        $this->assertSame(
            [
                'categories_id' => 5,
                'parent_id' => 0,
                'categories_status' => '1',
                'product_type_id' => 1,
                'id' => 5,
                'category_id' => 5,
                'lang' => [
                    'en' => ['language_id' => 1, 'categories_name' => 'Hardware', 'categories_description' => 'Hard things'],
                    'fr' => ['language_id' => 2, 'categories_name' => 'Materiel', 'categories_description' => 'Choses dures'],
                ],
            ],
            $category->getData()
        );
    }

    public function testLoadingIssuesCachedQueriesForTheRequestedCategory(): void
    {
        new \Category(5);

        $categorySql = $this->sqlMatching(self::CATEGORY_SQL);
        $descriptionSql = $this->sqlMatching(self::DESCRIPTION_SQL);
        $this->assertCount(1, $categorySql);
        $this->assertStringContainsString('WHERE categories_id = 5', $categorySql[0]);
        $this->assertStringContainsString('LEFT JOIN ' . TABLE_PRODUCT_TYPES_TO_CATEGORY, $categorySql[0]);
        $this->assertCount(1, $descriptionSql);
        $this->assertStringContainsString('WHERE categories_id = 5', $descriptionSql[0]);
        $this->assertStringContainsString('ORDER BY language_id', $descriptionSql[0]);
        $this->assertSame(
            [1, true, 900],
            [$this->executedQueries[0]['limit'], $this->executedQueries[0]['cache'], $this->executedQueries[0]['ttl']]
        );
        $this->assertSame(
            [null, true, 900],
            [$this->executedQueries[1]['limit'], $this->executedQueries[1]['cache'], $this->executedQueries[1]['ttl']]
        );
    }

    public function testAnUnknownCategoryIsEmpty(): void
    {
        $this->queryResponses = [];

        $category = new \Category(404);

        $this->assertSame([], $category->getData());
        $this->assertTrue($category->EOF);
        $this->assertFalse($category->exists());
        $this->assertFalse($category->isValid());
        $this->assertSame(0, $category->status());
        $this->assertCount(1, $this->executedQueries, 'descriptions are not queried for a missing category');
    }

    public function testCategoryIdZeroIsLookedUpButNeverExists(): void
    {
        $this->queryResponses = [];

        $category = new \Category(0);

        $this->assertCount(1, $this->executedQueries);
        $this->assertStringContainsString('WHERE categories_id = 0', $this->executedQueries[0]['sql']);
        $this->assertFalse($category->exists());
    }

    public function testStatusIsReadFromTheCategoryRow(): void
    {
        $this->assertSame(1, (new \Category(5))->status());

        $this->queryResponses = [];
        $this->respondTo(self::CATEGORY_SQL, [['categories_id' => 6, 'categories_status' => '0']]);
        $this->assertSame(0, (new \Category(6))->status());

        $this->queryResponses = [];
        $this->respondTo(self::CATEGORY_SQL, [['categories_id' => 7]]);
        $this->assertSame(0, (new \Category(7))->status());
    }

    public function testGetFallsBackToTheSessionLanguageDescription(): void
    {
        $category = new \Category(5);

        $this->assertSame('1', $category->get('categories_status'));
        $this->assertSame('Hardware', $category->get('categories_name'));
        $this->assertNull($category->get('no_such_field'));
        $this->assertSame('Hardware', $category->categories_name);

        $_SESSION['languages_id'] = '2';
        $this->assertSame('Materiel', $category->get('categories_name'));
        $this->assertSame('Choses dures', $category->categories_description);
    }

    public function testDataForLanguageFlattensTheRequestedLanguage(): void
    {
        $category = new \Category(5);

        $french = $category->getDataForLanguage(2);

        $this->assertArrayNotHasKey('lang', $french);
        $this->assertSame('Materiel', $french['categories_name']);
        $this->assertSame(2, $french['language_id']);
        $this->assertSame(5, $french['categories_id']);
        $this->assertSame('Hardware', $category->getDataForLanguage(1)['categories_name']);
        $this->assertArrayHasKey('lang', $category->getData(), 'the stored data is not modified');
    }

    public function testDataForLanguageDefaultsToTheSessionLanguage(): void
    {
        $category = new \Category(5);

        $this->assertSame('Hardware', $category->getDataForLanguage()['categories_name']);
        $this->assertSame('Hardware', $category->getDataForLanguage(null)['categories_name']);
        $this->assertSame('Hardware', $category->getDataForLanguage(0)['categories_name']);

        $_SESSION['languages_id'] = 2;
        $this->assertSame('Materiel', $category->getDataForLanguage()['categories_name']);
        $this->assertSame('Materiel', $category->getDataForLanguage(0)['categories_name']);
    }

    public function testDataForLanguageReturnsBaseDataWhenThereAreNoDescriptions(): void
    {
        $this->queryResponses = [];
        $this->respondTo(self::CATEGORY_SQL, [['categories_id' => 9, 'categories_status' => '1']]);

        $category = new \Category(9);

        $this->assertSame($category->getData(), $category->getDataForLanguage(2));
        $this->assertArrayNotHasKey('lang', $category->getDataForLanguage());
    }

    public function testForLanguageAndWithDefaultLanguageReplaceTheStoredData(): void
    {
        $category = new \Category(5);

        $this->assertSame($category, $category->forLanguage(2));
        $this->assertSame('Materiel', $category->getData()['categories_name']);
        $this->assertSame($category->getData(), $category->fields);
        $this->assertArrayNotHasKey('lang', $category->fields);

        $category = new \Category(5);
        $_SESSION['languages_id'] = 2;
        $this->assertSame($category, $category->withDefaultLanguage());
        $this->assertSame('Materiel', $category->getData()['categories_name']);
        $this->assertSame($category->getData(), $category->fields);
    }

    public function testSubclassesCanExtendTheLoadingHooks(): void
    {
        $subclass = new class (5) extends \Category {
            public array $hooksCalled = [];

            protected function initLanguages(): void
            {
                $this->hooksCalled[] = 'initLanguages';
                parent::initLanguages();
            }

            protected function loadCategoryDetails(int $category_id, ?int $language_id = null): array
            {
                $this->hooksCalled[] = 'loadCategoryDetails';
                return parent::loadCategoryDetails($category_id, $language_id);
            }
        };

        $this->assertSame(['initLanguages', 'loadCategoryDetails'], $subclass->hooksCalled);
        $this->assertSame('Hardware', $subclass->get('categories_name'));
    }

    public function testObserversCanModifyTheLoadedDetails(): void
    {
        new CategoryDetailsObserver();

        $category = new \Category(5);

        $this->assertSame('observed', $category->get('observer_flag'));
        $this->assertSame(5, $category->get('observed_category_id'));
        $this->assertSame('observed', $category->fields['observer_flag']);
    }
}

class CategoryDetailsObserver extends \base
{
    public function __construct()
    {
        $this->attach($this, ['NOTIFY_GET_CATEGORY_OBJECT_DETAILS']);
    }

    public function updateNotifyGetCategoryObjectDetails(&$class, $eventID, $categoryId, &$details): void
    {
        $details['observer_flag'] = 'observed';
        $details['observed_category_id'] = $categoryId;
    }
}
