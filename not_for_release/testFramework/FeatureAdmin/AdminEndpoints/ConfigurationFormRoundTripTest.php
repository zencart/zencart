<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\FeatureAdmin\AdminEndpoints;

use PHPUnit\Framework\Attributes\Group;
use Tests\Support\InProcess\FeatureResponse;
use Tests\Support\zcInProcessFeatureTestCaseAdmin;

/**
 * The configuration tool decides what to write by comparing each posted `configuration[...]`
 * value against the `original[...]` hidden field rendered alongside it. Since the
 * template-settings work, that same comparison also decides whether a template-specific
 * override is *removed*, so the two fields must survive a browser round-trip identically.
 *
 * These tests submit the rendered form completely untouched: nothing may be written, for
 * any value, however awkwardly it escapes.
 */
#[Group('parallel-candidate')]
#[Group('custom-seeder')]
class ConfigurationFormRoundTripTest extends zcInProcessFeatureTestCaseAdmin
{
    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    private const GROUP_ID = 1;
    private const SENTINEL_DATE = '2001-02-03 04:05:06';
    private const TEMPLATE_DIR = 'responsive_classic';

    /**
     * Values chosen for how they behave in an HTML attribute: entity-encoding,
     * pre-encoded text, quoting, and the two strings zen_not_null() treats as absent.
     */
    private const AWKWARD_VALUES = [
        'ZCTEST_PLAIN' => 'plain value',
        'ZCTEST_AMPERSAND' => 'Tom & Jerry',
        'ZCTEST_PREENCODED' => 'A &amp; B',
        'ZCTEST_DOUBLE_QUOTE' => 'say "hi"',
        'ZCTEST_SINGLE_QUOTE' => "it's",
        'ZCTEST_MARKUP' => '<b>bold</b>',
        'ZCTEST_SPACES' => ' ',
        'ZCTEST_LITERAL_NULL' => 'NULL',
    ];

    public function testSubmittingAnUntouchedConfigurationFormWritesNothing(): void
    {
        $this->loginToAdmin();
        $db = $this->bootstrapLegacyDbConnection();
        $this->seedAwkwardConfigurationValues($db);

        $page = $this->visitAdminCommand('configuration&gID=' . self::GROUP_ID)->assertOk();
        $this->assertSee($page, 'ZCTEST_PLAIN');

        $this->submitConfigurationForm($page)->assertOk();

        $rewritten = [];
        foreach (self::AWKWARD_VALUES as $key => $value) {
            $row = $this->configurationRow($db, $key);
            if ($row['last_modified'] !== self::SENTINEL_DATE || $row['configuration_value'] !== $value) {
                $rewritten[$key] = $row['configuration_value'];
            }
        }

        $this->assertSame(
            [],
            $rewritten,
            'An untouched form re-saved these settings, so their `original` field did not round-trip.'
        );
    }

    /**
     * The same invariant on the template-scoped path, where a value that matches its
     * inherited value is deliberately dropped from the template's settings: submitting the
     * form untouched must neither add an override nor remove an existing one.
     *
     * Note: whitespace-only overrides are deliberately not exercised here. That path re-collects
     * every displayed value through zen_db_prepare_input(), which trims - so an untouched
     * override of ' ' is stored back as ''. That is a separate, pre-existing question about
     * whether saving should normalize whitespace, not a round-trip failure.
     */
    public function testSubmittingAnUntouchedTemplateScopedFormLeavesTemplateSettingsAlone(): void
    {
        $this->loginToAdmin();
        $db = $this->bootstrapLegacyDbConnection();
        $this->seedAwkwardConfigurationValues(
            $db,
            templateSettings: true,
            only: ['ZCTEST_PLAIN', 'ZCTEST_AMPERSAND', 'ZCTEST_DOUBLE_QUOTE', 'ZCTEST_MARKUP']
        );

        // Selecting a template also synchronizes template_select, so the base record exists after this.
        $groupPage = $this->visitAdminCommand('configuration&gID=' . self::GROUP_ID)->assertOk();
        $this->submitAdminForm($groupPage, 'saveto', ['saveto' => self::TEMPLATE_DIR])->assertOk();

        $storedSettings = json_encode(['ZCTEST_MARKUP' => '<i>own</i>', 'ZCTEST_AMPERSAND' => 'a & b']);
        $db->Execute(
            "UPDATE " . TABLE_TEMPLATE_SELECT . "
                SET template_settings = '" . self::esc($storedSettings) . "'
              WHERE template_dir = '" . self::esc(self::TEMPLATE_DIR) . "'
                AND template_language = -1
              LIMIT 1"
        );

        $page = $this->visitAdminCommand('configuration&gID=' . self::GROUP_ID)->assertOk();
        $this->submitConfigurationForm($page)->assertOk();

        $result = $db->Execute(
            "SELECT template_settings
               FROM " . TABLE_TEMPLATE_SELECT . "
              WHERE template_dir = '" . self::esc(self::TEMPLATE_DIR) . "'
                AND template_language = -1
              LIMIT 1"
        );

        $this->assertEqualsCanonicalizing(
            ['ZCTEST_MARKUP' => '<i>own</i>', 'ZCTEST_AMPERSAND' => 'a & b'],
            json_decode((string)$result->fields['template_settings'], true),
            'An untouched template-scoped form altered the stored template_settings.'
        );
    }

    /**
     * submitAdminForm() posts the form defaults verbatim, and the in-process runner assigns
     * them straight to $_POST - so PHP's array-notation field names ("configuration[cfg_12]")
     * would arrive as flat string keys and admin/configuration.php would reject the submit
     * as malformed. Re-expand them the way PHP's own form decoding would.
     */
    private function submitConfigurationForm(FeatureResponse $page): FeatureResponse
    {
        $action = $page->formAction('configuration');
        $this->assertNotNull($action, 'The configuration form was not rendered.');

        parse_str(http_build_query($page->formDefaults('configuration')), $data);
        $this->assertIsArray($data['configuration'] ?? null, 'Expected posted configuration values to be an array.');
        $this->assertIsArray($data['original'] ?? null, 'Expected posted original values to be an array.');

        $response = $this->postAdmin(self::pathAndQuery($action), $data);

        return $response->isRedirect() ? $this->followAdminRedirect($response) : $response;
    }

    /** The runner expects a host-relative URI; form actions carry the full store URL. */
    private static function pathAndQuery(string $uri): string
    {
        $parts = parse_url($uri);

        return ($parts['path'] ?? $uri) . (empty($parts['query']) ? '' : '?' . $parts['query']);
    }

    private function assertSee(FeatureResponse $page, string $needle): void
    {
        $this->assertStringContainsString($needle, $page->content);
    }

    private function loginToAdmin(): void
    {
        $this->runCustomSeeder('StoreWizardSeeder');

        $this->submitAdminLogin([
            'admin_name' => 'Admin',
            'admin_pass' => 'password',
        ])->assertOk()
            ->assertSee('Admin Home');
    }

    private function seedAwkwardConfigurationValues(\queryFactory $db, bool $templateSettings = false, ?array $only = null): void
    {
        $values = ($only === null) ? self::AWKWARD_VALUES : array_intersect_key(self::AWKWARD_VALUES, array_flip($only));

        $sortOrder = 900;
        foreach ($values as $key => $value) {
            $db->Execute("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = '" . self::esc($key) . "'");
            $db->Execute(
                "INSERT INTO " . TABLE_CONFIGURATION . "
                    (configuration_title, configuration_key, configuration_value, configuration_description,
                     configuration_group_id, sort_order, date_added, last_modified, is_template_setting)
                 VALUES
                    ('Round-trip $key', '" . self::esc($key) . "', '" . self::esc($value) . "', 'Round-trip fixture',
                     " . self::GROUP_ID . ", " . $sortOrder++ . ", now(), '" . self::SENTINEL_DATE . "', " . (int)$templateSettings . ")"
            );
        }
    }

    private static function esc(string $value): string
    {
        return addslashes($value);
    }

    private function configurationRow(\queryFactory $db, string $key): array
    {
        $result = $db->Execute(
            "SELECT configuration_value, last_modified
               FROM " . TABLE_CONFIGURATION . "
              WHERE configuration_key = '" . self::esc($key) . "'
              LIMIT 1"
        );

        $this->assertFalse($result->EOF, "Fixture row $key is missing.");

        return $result->fields;
    }

    private function bootstrapLegacyDbConnection(): \queryFactory
    {
        if (!class_exists('queryFactory')) {
            require_once ROOTCWD . 'includes/classes/class.base.php';
            require_once ROOTCWD . 'includes/classes/db/' . DB_TYPE . '/query_factory.php';
        }

        $db = new \queryFactory();
        if (!defined('USE_PCONNECT')) {
            define('USE_PCONNECT', 'false');
        }

        $db->connect(DB_SERVER, DB_SERVER_USERNAME, DB_SERVER_PASSWORD, DB_DATABASE, USE_PCONNECT, false);

        return $db;
    }
}
