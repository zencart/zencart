<?php

declare(strict_types=1);

namespace Tests\Unit\testsTemplateResolver;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\Support\zcUnitTestCase;
use Zencart\Templates\TemplateSelect;

/**
 * Coverage for the "child template" settings support added to TemplateSelect:
 *  - getInheritedSetting(), which walks a template's parent chain (excluding both the
 *    template itself and 'template_default') looking for a setting-key's value;
 *  - updateTemplateSettingsForKeys(), which the admin's configuration tool uses to
 *    write a group's template-specific values;
 *  - getTemplateSettings()'s JSON decoding/normalizing and its static cache;
 *  - the lazy resolveTemplates() trigger now built into getSelectableTemplates()
 *    and templateIsSelectable().
 *
 * A three-deep on-disk template chain is created for the duration of each test:
 *
 *      zz_test_grandchild -> zz_test_child -> zz_test_parent -> (template_default)
 */
#[AllowMockObjectsWithoutExpectations]
#[RunTestsInSeparateProcesses]
class TemplateSelectInheritedSettingsTest extends zcUnitTestCase
{
    private const PARENT_DIR = 'zz_test_parent';
    private const CHILD_DIR = 'zz_test_child';
    private const GRANDCHILD_DIR = 'zz_test_grandchild';

    /** @var array<int, array{template_id: string, template_dir: string, template_language: string, template_settings: ?string}> */
    private array $rows = [];
    private int $nextId = 0;
    private int $lastAffectedRows = 0;
    private int $lastInsertId = 0;
    private array $createdTemplatePaths = [];

    public function setUp(): void
    {
        parent::setUp();

        require_once DIR_FS_CATALOG . 'includes/classes/class.base.php';
        require_once DIR_FS_CATALOG . 'includes/classes/db/mysql/query_factory.php';
        require_once DIR_FS_CATALOG . 'includes/classes/TemplateDto.php';
        require_once DIR_FS_CATALOG . 'includes/classes/ResourceLoaders/TemplateResolver.php';
        require_once DIR_FS_CATALOG . 'includes/functions/functions_templates.php';
        require_once DIR_FS_CATALOG . 'includes/classes/TemplateSelect.php';

        // Keeps TemplateResolver from constructing a real PluginManager; the fixture
        // templates below are all core (non-plugin) templates.
        $GLOBALS['installedPlugins'] = [];

        $_SESSION['languages_id'] = 0;

        $this->createTemplateFixture(self::PARENT_DIR, null);
        $this->createTemplateFixture(self::CHILD_DIR, self::PARENT_DIR);
        $this->createTemplateFixture(self::GRANDCHILD_DIR, self::CHILD_DIR);

        $this->seedRows([
            ['template_dir' => self::GRANDCHILD_DIR, 'template_language' => '0', 'template_settings' => null],
            ['template_dir' => self::PARENT_DIR, 'template_language' => '-1', 'template_settings' => null],
            ['template_dir' => self::CHILD_DIR, 'template_language' => '-1', 'template_settings' => null],
            ['template_dir' => self::GRANDCHILD_DIR, 'template_language' => '-1', 'template_settings' => null],
        ]);

        $GLOBALS['db'] = $this->makeMockDb();
    }

    public function tearDown(): void
    {
        foreach ($this->createdTemplatePaths as $path) {
            @unlink($path . '/template_info.php');
            @rmdir($path);
        }
        $this->createdTemplatePaths = [];

        parent::tearDown();
    }

    public function testInheritedSettingComesFromTheNearestAncestor(): void
    {
        $this->setStoredSettings(self::CHILD_DIR, ['FOO' => 'from-child']);
        $this->setStoredSettings(self::PARENT_DIR, ['FOO' => 'from-parent']);

        $templateSelect = new TemplateSelect();

        $this->assertSame(
            'from-child',
            $templateSelect->getInheritedSetting(self::GRANDCHILD_DIR, 'FOO', 'from-configuration-table')
        );
    }

    public function testInheritedSettingSkipsAncestorsThatDoNotDefineTheKey(): void
    {
        $this->setStoredSettings(self::CHILD_DIR, ['OTHER' => 'from-child']);
        $this->setStoredSettings(self::PARENT_DIR, ['FOO' => 'from-parent']);

        $templateSelect = new TemplateSelect();

        $this->assertSame(
            'from-parent',
            $templateSelect->getInheritedSetting(self::GRANDCHILD_DIR, 'FOO', 'from-configuration-table')
        );
    }

    public function testInheritedSettingFallsBackToTheSuppliedDefault(): void
    {
        $templateSelect = new TemplateSelect();

        $this->assertSame(
            'from-configuration-table',
            $templateSelect->getInheritedSetting(self::GRANDCHILD_DIR, 'FOO', 'from-configuration-table'),
            'With no ancestor value present, the supplied (configuration-table) default is the inherited value.'
        );
    }

    /**
     * The template's own settings are its overrides, not something it inherits;
     * getInheritedSetting() discards the first entry of the inheritance chain for
     * exactly this reason.
     */
    public function testInheritedSettingIgnoresTheTemplatesOwnSettings(): void
    {
        $this->setStoredSettings(self::GRANDCHILD_DIR, ['FOO' => 'my-own-value']);

        $templateSelect = new TemplateSelect();

        $this->assertSame(
            'from-configuration-table',
            $templateSelect->getInheritedSetting(self::GRANDCHILD_DIR, 'FOO', 'from-configuration-table')
        );
    }

    public function testInheritedSettingForATemplateWithNoParentsReturnsTheDefault(): void
    {
        $templateSelect = new TemplateSelect();

        $this->assertSame(
            'from-configuration-table',
            $templateSelect->getInheritedSetting(self::PARENT_DIR, 'FOO', 'from-configuration-table')
        );
    }

    public function testGetTemplateSettingsNormalizesNonStringScalarsToStrings(): void
    {
        $this->setRawStoredSettings(self::CHILD_DIR, '{"NUM":5,"YES":true,"NO":false,"NOTHING":null}');

        $templateSelect = new TemplateSelect();

        $this->assertSame(
            ['NUM' => '5', 'YES' => 'true', 'NO' => 'false', 'NOTHING' => null],
            $templateSelect->getTemplateSettings(self::CHILD_DIR),
            'Stored settings must reach callers as the strings that configuration values always are.'
        );
    }

    public function testGetTemplateSettingsReturnsNullForStoredJsonThatIsNotAnArray(): void
    {
        $this->setRawStoredSettings(self::CHILD_DIR, '"not-an-array"');

        $templateSelect = new TemplateSelect();

        $this->assertNull($templateSelect->getTemplateSettings(self::CHILD_DIR));
    }

    public function testGetTemplateSettingsReturnsNullForUndecodableJson(): void
    {
        $this->setRawStoredSettings(self::CHILD_DIR, '{not valid json');

        $templateSelect = new TemplateSelect();

        $this->assertNull($templateSelect->getTemplateSettings(self::CHILD_DIR));
    }

    /**
     * updateTemplateSettingsForKeys() is destructive by design: every key named in
     * $settings_keys is first removed from the template's stored settings, and only
     * the keys present in $template_settings are written back.
     *
     * Callers must therefore submit the full set of values they want retained for the
     * keys they name - submitting only the values that changed removes the rest.
     */
    public function testUpdateTemplateSettingsForKeysDropsNamedKeysThatAreNotResubmitted(): void
    {
        $templateSelect = new TemplateSelect();
        $templateSelect->setTemplateSettings(self::CHILD_DIR, ['A' => '1', 'B' => '2', 'C' => '3']);

        $status = $templateSelect->updateTemplateSettingsForKeys(
            self::CHILD_DIR,
            ['B' => '20'],
            ['A', 'B', 'C']
        );

        $this->assertSame(TemplateSelect::SETTINGS_OK, $status);
        $this->assertSame(
            ['B' => '20'],
            $templateSelect->getTemplateSettings(self::CHILD_DIR)
        );
    }

    public function testUpdateTemplateSettingsForKeysLeavesUnnamedKeysIntact(): void
    {
        $templateSelect = new TemplateSelect();
        $templateSelect->setTemplateSettings(self::CHILD_DIR, ['A' => '1', 'OTHER_GROUP' => 'keep-me']);

        $status = $templateSelect->updateTemplateSettingsForKeys(
            self::CHILD_DIR,
            ['A' => '10'],
            ['A']
        );

        $this->assertSame(TemplateSelect::SETTINGS_OK, $status);
        $this->assertSame(
            ['OTHER_GROUP' => 'keep-me', 'A' => '10'],
            $templateSelect->getTemplateSettings(self::CHILD_DIR)
        );
    }

    public function testUpdateTemplateSettingsForKeysRemovingEveryKeyStoresNull(): void
    {
        $templateSelect = new TemplateSelect();
        $templateSelect->setTemplateSettings(self::CHILD_DIR, ['A' => '1', 'B' => '2']);

        $status = $templateSelect->updateTemplateSettingsForKeys(self::CHILD_DIR, [], ['A', 'B']);

        $this->assertSame(TemplateSelect::SETTINGS_OK, $status);
        $this->assertNull(
            $templateSelect->getTemplateSettings(self::CHILD_DIR),
            'An emptied settings array is stored as SQL NULL, not as an empty JSON object.'
        );
        $this->assertNull($this->storedSettingsFor(self::CHILD_DIR));
    }

    public function testUpdateTemplateSettingsForKeysReturnsUnknownDirForATemplateWithNoBaseRecord(): void
    {
        $templateSelect = new TemplateSelect();

        $this->assertSame(
            TemplateSelect::SETTINGS_UNKNOWN_DIR,
            $templateSelect->updateTemplateSettingsForKeys('not_a_real_template', ['A' => '1'], ['A'])
        );
    }

    /**
     * resolveTemplates() is no longer a public call the admin pages have to remember to
     * make; the accessors that need $selectableTemplates trigger it themselves - including
     * the synchronization that adds a missing 'base' (template_language = -1) record.
     */
    public function testGetSelectableTemplatesLazilyResolvesAndAddsAMissingBaseRecord(): void
    {
        // Only the language-0 row survives, so the fixture templates have no base records.
        $this->seedRows([
            ['template_dir' => self::GRANDCHILD_DIR, 'template_language' => '0', 'template_settings' => null],
        ]);
        $GLOBALS['db'] = $this->makeMockDb();

        $templateSelect = new TemplateSelect();

        $this->assertNull(
            $this->baseRowFor(self::CHILD_DIR),
            'Precondition: no base record exists before the first accessor call.'
        );

        $selectable = $templateSelect->getSelectableTemplates();

        $this->assertArrayHasKey(self::CHILD_DIR, $selectable);
        $this->assertNotNull(
            $this->baseRowFor(self::CHILD_DIR),
            'getSelectableTemplates() must synchronize the `template_select` table.'
        );
        $this->assertSame(
            TemplateSelect::SETTINGS_OK,
            $templateSelect->setTemplateSettings(self::CHILD_DIR, ['A' => '1']),
            'The freshly-created base record must be usable for settings storage.'
        );
    }

    public function testTemplateIsSelectableLazilyResolvesTemplates(): void
    {
        $this->seedRows([
            ['template_dir' => self::GRANDCHILD_DIR, 'template_language' => '0', 'template_settings' => null],
        ]);
        $GLOBALS['db'] = $this->makeMockDb();

        $templateSelect = new TemplateSelect();

        $this->assertTrue($templateSelect->templateIsSelectable(self::CHILD_DIR));
        $this->assertFalse($templateSelect->templateIsSelectable('not_a_real_template'));
    }

    public function testIsActiveTemplateReportsOnlyLanguageAssignedDirectories(): void
    {
        $templateSelect = new TemplateSelect();

        $this->assertTrue($templateSelect->isActiveTemplate(self::GRANDCHILD_DIR));
        $this->assertFalse($templateSelect->isActiveTemplate(self::CHILD_DIR));
        $this->assertFalse($templateSelect->isActiveTemplate('not_a_real_template'));
    }

    private function createTemplateFixture(string $templateDir, ?string $baseTemplate): void
    {
        $path = DIR_FS_CATALOG . 'includes/templates/' . $templateDir;
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        $this->createdTemplatePaths[] = $path;

        $base = ($baseTemplate === null) ? 'null' : "'" . $baseTemplate . "'";
        file_put_contents(
            $path . '/template_info.php',
            "<?php\n"
            . "\$template_name = '" . $templateDir . "';\n"
            . "\$template_version = '1.0.0';\n"
            . "\$template_base = " . $base . ";\n"
        );
    }

    /**
     * @param array<int, array{template_dir: string, template_language: string, template_settings: ?string}> $rows
     */
    private function seedRows(array $rows): void
    {
        $this->rows = [];
        $this->nextId = 0;
        foreach ($rows as $row) {
            $id = ++$this->nextId;
            $this->rows[$id] = array_merge(['template_id' => (string)$id], $row);
        }
    }

    private function setStoredSettings(string $templateDir, array $settings): void
    {
        $this->setRawStoredSettings($templateDir, json_encode($settings));
    }

    private function setRawStoredSettings(string $templateDir, ?string $json): void
    {
        foreach ($this->rows as $id => $row) {
            if ($row['template_dir'] === $templateDir && (int)$row['template_language'] === TemplateSelect::TEMPLATE_BASE_LANGUAGE) {
                $this->rows[$id]['template_settings'] = $json;
                return;
            }
        }
        self::fail('No base record seeded for ' . $templateDir);
    }

    private function storedSettingsFor(string $templateDir): ?string
    {
        return $this->baseRowFor($templateDir)['template_settings'] ?? null;
    }

    private function baseRowFor(string $templateDir): ?array
    {
        foreach ($this->rows as $row) {
            if ($row['template_dir'] === $templateDir && (int)$row['template_language'] === TemplateSelect::TEMPLATE_BASE_LANGUAGE) {
                return $row;
            }
        }
        return null;
    }

    private function makeMockDb(): \queryFactory
    {
        $db = $this->getMockBuilder(\queryFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $db->method('bindVars')->willReturnCallback(
            function (string $sql, string $token, $value, string $rule): string {
                if ($rule === 'integer') {
                    $replacement = (string)(int)$value;
                } elseif ($rule === 'passthru') {
                    $replacement = (string)$value;
                } else {
                    $replacement = "'" . addslashes((string)$value) . "'";
                }
                return str_replace($token, $replacement, $sql);
            }
        );

        $db->method('insert_ID')->willReturnCallback(fn (): int => $this->lastInsertId);
        $db->method('affectedRows')->willReturnCallback(fn (): int => $this->lastAffectedRows);
        $db->method('Execute')->willReturnCallback(fn (string $sql): \queryFactoryResult => $this->handleQuery($sql));

        return $db;
    }

    private function handleQuery(string $sql): \queryFactoryResult
    {
        if (stripos($sql, 'plugin_control') !== false) {
            return $this->makeQueryResult([]);
        }

        if (stripos($sql, 'INSERT INTO') !== false) {
            preg_match("/VALUES\s*\(\s*'((?:[^'\\\\]|\\\\.)*)'\s*,\s*(-?\d+)\s*\)/is", $sql, $matches);
            $id = ++$this->nextId;
            $this->rows[$id] = [
                'template_id' => (string)$id,
                'template_dir' => stripslashes($matches[1]),
                'template_language' => (string)(int)$matches[2],
                'template_settings' => null,
            ];
            $this->lastInsertId = $id;
            $this->lastAffectedRows = 1;
            return $this->makeQueryResult([]);
        }

        if (stripos($sql, 'SET template_settings') !== false) {
            preg_match("/SET template_settings = (NULL|'.*?')\s+WHERE template_id = (\d+)/s", $sql, $matches);
            $id = (int)$matches[2];
            $newValue = ($matches[1] === 'NULL') ? null : stripslashes(trim($matches[1], "'"));
            $this->lastAffectedRows = 0;
            if (isset($this->rows[$id])) {
                if ($this->rows[$id]['template_settings'] !== $newValue) {
                    $this->lastAffectedRows = 1;
                }
                $this->rows[$id]['template_settings'] = $newValue;
            }
            return $this->makeQueryResult([]);
        }

        // The initial "SELECT * FROM template_select" issued by the constructor.
        return $this->makeQueryResult(array_values($this->rows));
    }

    private function makeQueryResult(array $rows): \queryFactoryResult
    {
        $result = new \queryFactoryResult(null);
        $result->result = $rows;
        $result->is_cached = true;
        $result->cursor = 0;
        $result->fields = $rows[0] ?? [];
        $result->EOF = ($rows === []);

        return $result;
    }
}
