<?php

declare(strict_types=1);
/**
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2025 Sep 18 Modified in v2.2.0 $
 */

namespace Zencart\Templates;

use Zencart\DbRepositories\PluginControlRepository;
use Zencart\PluginSupport\PluginStatus;

/**
 * A class that manages the `template_select` database table and provides "helper"
 * functions to the admin's tool of the same name.
 *
 * Notes:
 *  1) While not enforced as a database 'UNIQUE KEY', this class enforces that the
 *     template_dir/template_language is a unique pairing within the table.
 *  2) An "active" template directory might appear in the table more than once, since
 *     a template can be associated with multiple languages.
 *  3) A "base" template directory, i.e. one with a 'template_language' of -1,
 *     appears in the table only once.
 *  4) A 'template_dir' can have up to n+2 records in the `template_select` table, one
 *     for each of the store's languages (n) plus one as the default language (0) and
 *     one for its 'base' entry (template_language -1).
 *  5) A template's `template_settings` is maintained in the database **ONLY IN**
 *     its 'base' (template_language -1) record. That provides permanence for the
 *     template's settings, regardless of its current 'active' state.
 *
 * @since ZC v3.0.0
 */
class TemplateSelect
{
    public const int TEMPLATE_BASE_LANGUAGE = -1;

    /**
     * Return values from the setTemplateSettings method.
     */
    public const int SETTINGS_OK = 0;
    public const int SETTINGS_BAD_JSON = 1;
    public const int SETTINGS_UNKNOWN_DIR = 2;
    public const int SETTINGS_NO_UPDATE = 3;

    private static array $activeTemplates;  // Keyed by template_language
    private static array $dbTemplates;      // Keyed by template_id
    private static array $selectableTemplates;  // Keyed by template_dir
    private static \queryFactory $db;

    /**
     * @since ZC v3.0.0
     */
    public function __construct()
    {
        // -----
        // If the class 'copy' of the $db object is already set, the class has already
        // initialized and all its static properties can be reused.
        //
        if (isset(self::$db)) {
            return;
        }

        global $db;
        self::$db = $db;

        // -----
        // Start by gathering the current results from the
        // database's `template_select` table.
        //
        $result = self::$db->Execute(
            "SELECT *
               FROM " . TABLE_TEMPLATE_SELECT
        );
        foreach ($result as $next_template) {
            self::$dbTemplates[(int)$next_template['template_id']] = $next_template;
            if ($next_template['template_language'] !== (string)self::TEMPLATE_BASE_LANGUAGE) {
                self::$activeTemplates[$next_template['template_language']] = $next_template;
            }
        }

        // -----
        // Next, synchronize with the template-resolver since templates
        // might have been removed or added from the file-system or disabled via
        // the Plugin Manager.
        //
        $this->resolveTemplates();

        $active_template_dir = $this->getActiveTemplateDir();
        if ($active_template_dir !== null) {
            TemplateDto::getInstance()->updateTemplate($active_template_dir, ['is_active' => true]);
        }

//        $this->debug();
    }

    /**
     * @since ZC v3.0.0
     */
    private function resolveTemplates(): void
    {
        // -----
        // Determine which encapsulated plugins are currently installed,
        // whether enabled or disabled.
        //
        $installedPluginKeys = [];
        foreach ((new PluginControlRepository(self::$db))->getAll() as $plugin) {
            if (($plugin['status'] ?? PluginStatus::NOT_INSTALLED) !== PluginStatus::NOT_INSTALLED) {
                $installedPluginKeys[$plugin['unique_key']] = true;
            }
        }

        // -----
        // Retrieve the template-related information for all template
        // directories. This can include encapsulated template packages
        // that aren't currently installed.
        //
        // Then, filter out any records that are associated with template packages
        // that aren't installed.
        //
        self::$selectableTemplates = array_filter(
            \zen_get_catalog_template_directories(),
            static function (array $template) use ($installedPluginKeys): bool {
                if (empty($template['is_plugin_template'])) {
                    return true;
                }
                return isset($installedPluginKeys[$template['plugin_key'] ?? '']);
            }
        );

        // -----
        // Synchronize the database's `template_select` table with the selectable
        // templates found in the file-system, adding a record with a template_language
        // of -1 to indicate that this is the 'base' record for the template.
        //
        foreach (self::$selectableTemplates as $template_dir => $selected_info) {
            $default_entry_found = false;
            foreach (self::$dbTemplates as $id => $db_info) {
                if ($db_info['template_dir'] === $template_dir && (int)$db_info['template_language'] === self::TEMPLATE_BASE_LANGUAGE) {
                    $default_entry_found = true;
                    break;
                }
            }
            if ($default_entry_found === true) {
                continue;
            }

            $this->addTemplateToDb($template_dir, self::TEMPLATE_BASE_LANGUAGE);
        }
    }

    /**
     * @since ZC v3.0.0
     */
    public function getSelectableTemplates(): array
    {
        return self::$selectableTemplates;
    }

    /**
     * @since ZC v3.0.0
     */
    public function templateIsSelectable(string $template_dir): bool
    {
        return isset(self::$selectableTemplates[$template_dir]);
    }

    /**
     * @since ZC v3.0.0
     */
    public function getAllActiveTemplates(): array
    {
        return self::$activeTemplates;
    }

    /**
     * @since ZC v3.0.0
     */
    public function getActiveTemplateId(): int
    {
        return (int)$this->getActiveTemplateField('template_id');
    }

    /**
     * @since ZC v3.0.0
     */
    public function getActiveTemplateDir(): ?string
    {
        return $this->getActiveTemplateField('template_dir');
    }

    /**
     * Returns the currently-selected template for the specified language,
     * falling back to the default if no language-specific template is set.
     *
     * @since ZC v3.0.0
     */
    public function getTemplateDirForLanguage(string|int $language_id): string
    {
        return self::$activeTemplates[$language_id]['template_dir'] ?? self::$activeTemplates['0']['template_dir'];
    }

    /**
     * Retrieves the `template_settings` stored for a specified template
     * directory.
     *
     * @since ZC v3.0.0
     */
    public function getTemplateSettings(string $template_dir): ?string
    {
         foreach (self::$dbTemplates as $id => $info) {
            if ($info['template_dir'] === $template_dir && (int)$info['template_language'] === self::TEMPLATE_BASE_LANGUAGE) {
                return $info['template_settings'];
            }
        }
        return null;
    }

    /**
     * Sets/overwrites the `template_settings` stored for a specified template
     * directory. The value submitted for the settings must either be null or a
     * string that can be validly run through PHP's json_decode function.
     *
     * @since ZC v3.0.0
     */
    public function setTemplateSettings(string $template_dir, ?string $template_settings): int
    {
        if ($template_settings !== null && !is_array(json_decode($template_settings, true))) {
            return self::SETTINGS_BAD_JSON;
        }

        $db_id = false;
        foreach (self::$dbTemplates as $id => $info) {
            if ($info['template_dir'] === $template_dir && (int)$info['template_language'] === self::TEMPLATE_BASE_LANGUAGE) {
                $db_id = $id;
                break;
            }
        }
        if ($db_id === false) {
            return self::SETTINGS_UNKNOWN_DIR;
        }

        $sql =
            "UPDATE " . TABLE_TEMPLATE_SELECT . "
                SET template_settings = :settings:
              WHERE template_id = :id:
                AND template_language = " . self::TEMPLATE_BASE_LANGUAGE;
        $sql = self::$db->bindVars($sql, ':settings:', ($template_settings === null) ? 'NULL' : $template_settings, 'string');
        $sql = self::$db->bindVars($sql, ':id:', $db_id, 'integer');
        self::$db->Execute($sql, 1);
        if (self::$db->affectedRows() !== 1) {
            return self::SETTINGS_NO_UPDATE;
        }
        self::$dbTemplates[$db_id]['tpl_settings'] = $tpl_settings;

        return self::SETTINGS_OK;
    }

    /**
     * Adds a new template_dir/template_language association, kind of the opposite
     * of the deregisterTemplateById method.
     *
     * Neither the default (template_language = 0, since it's always present) nor base
     * (template_language = -1, since they're auto-calculated) entries can be added.
     *
     * @since ZC v3.0.0
     */
    public function registerNewTemplate(string $template_dir, int $language_id): false|int
    {
        if ($template_dir === '' || $language_id < 1 || isset(self::$activeTemplates[$language_id])) {
            return false;
        }

        return $this->addTemplateToDb($template_dir, $language_id);
    }

    /**
     * @since ZC v3.0.0
     */
    protected function addTemplateToDb(string $template_dir, int $language_id): int
    {
        $sql =
            "INSERT INTO " . TABLE_TEMPLATE_SELECT . "
                (template_dir, template_language)
             VALUES
                (:tpl:, :lang:)";
        $sql = self::$db->bindVars($sql, ':tpl:', $template_dir, 'string');
        $sql = self::$db->bindVars($sql, ':lang:', $language_id, 'integer');
        self::$db->Execute($sql);
        $template_id = (int)self::$db->insert_ID();

        $template_info = [
            'template_id' => (string)$template_id,
            'template_dir' => $template_dir,
            'template_language' => (string)$language_id,
            'template_settings' => null,
        ];
        self::$dbTemplates[$template_id] = $template_info;
        if ($language_id !== self::TEMPLATE_BASE_LANGUAGE) {
            self::$activeTemplates[$language_id] = $template_info;
        }

        return $template_id;
    }

    /**
     * Updates the template to be used for a specific language. Base
     * entries (template_language = -1) cannot be updated as they're auto-calculated.
     *
     * @since ZC v3.0.0
     */
    public function updateTemplateNameForId(int $id, string $template_dir): void
    {
        if ($template_dir === '' || $id < 0) {
            return;
        }

        $sql =
            "UPDATE " . TABLE_TEMPLATE_SELECT . "
                SET template_dir = :tpl:
              WHERE template_id = :id:
                AND template_language != " . self::TEMPLATE_BASE_LANGUAGE;
        $sql = self::$db->bindVars($sql, ':tpl:', $template_dir, 'string');
        $sql = self::$db->bindVars($sql, ':id:', $id, 'integer');
        self::$db->Execute($sql, 1);

        // -----
        // If the row wasn't updated (specifically 1 row "should be"), don't
        // perform any adjustment of the class-based arrays.
        //
        if (self::$db->affectedRows() !== 1) {
            return;
        }

        foreach (self::$activeTemplates as $language_id => $template_info) {
            if ($id === (int)$template_info['template_id']) {
                self::$activeTemplates[$language_id]['template_dir'] = $template_dir;
                self::$dbTemplates[$id]['template_dir'] = $template_dir;
                break;
            }
        }
    }

    /**
     * "De-register" a template from a specific language. Neither the
     * default (template_language = 0) nor base (template_language = -1)
     * entries can be de-registered!
     *
     * @since ZC v3.0.0
     */
    public function deregisterTemplateId(int $id): bool
    {
        if ($id <= 0) {
            return false;
        }

        foreach (self::$activeTemplates as $language_id => $template_info) {
            if ($id === (int)$template_info['template_id']) {
                if ((int)$language_id <= 0) {
                    return false;
                }

                unset(self::$activeTemplates[$language_id], self::$dbTemplates[$id]);

                $sql =
                    "DELETE FROM " . TABLE_TEMPLATE_SELECT . "
                      WHERE template_id = :id:";
                $sql = self::$db->bindVars($sql, ':id:', $id, 'integer');
                self::$db->Execute($sql, 1);

                return true;
            }
        }
        return false;
    }

    /**
     * @since ZC v3.0.0
     */
    public function getUnregisteredTemplateLanguages(): array
    {
        $templates = [];
        $sql =
            "SELECT lng.name as language_name, lng.languages_id as language_id
               FROM " . TABLE_LANGUAGES . " lng
              WHERE lng.languages_id NOT IN (SELECT template_language FROM " . TABLE_TEMPLATE_SELECT . " WHERE template_language != " . self::TEMPLATE_BASE_LANGUAGE . ")";
        $results = self::$db->Execute($sql);
        foreach ($results as $result) {
            $templates[] = $result;
        }
        return $templates;
    }

    /**
     * @since ZC v3.0.0
     */
    protected function getActiveTemplateField(string $field_name): ?string
    {
        return self::$activeTemplates[$_SESSION['languages_id']][$field_name] ?? self::$activeTemplates['0'][$field_name] ?? null;
    }

    private function debug(): void
    {
        trigger_error(var_export(self::$dbTemplates, true) . "\n" . var_export(self::$activeTemplates, true) . "\n" . var_export(self::$selectableTemplates, true));
    }
}
