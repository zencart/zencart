<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license https://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  Modified in v2.0.0-alpha2 $
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

/**
 * Template settings access helper function.
 * This will first look to the $template_settings array for the setting key (the name of the constant being overridden)
 * Lookup order:
 * - $template_settings array key
 * - global CONSTANT
 * - global $var by same name, if requested
 *
 * All returned values will be passed through zen_cast() to allow casting to desired type, if specified.
 *
 * If nothing is found, the supplied default will be returned. Else null.
 */
function tpl(string $setting, string $cast_to = null, $default = null, bool $check_globals = false): mixed
{
    // Check whether the $tpl_settings array contains the $setting
    // It could be populated from the template_settings.php file or the template_settings JSON field in the db
    global $tpl_settings;
    if (isset($tpl_settings[$setting])) {
        return zen_cast($tpl_settings[$setting], $cast_to);
    }

    // Fallback to a globally-defined constant, if it exists
    if (defined($setting)) {
        return zen_cast(constant($setting), $cast_to);
    }

    // Else fall back to a global variable
    if ($check_globals && isset($GLOBALS[$setting])) {
        return zen_cast($GLOBALS[$setting], $cast_to);
    }

    // Else return the provided default, if any
    if ($default !== null) {
        return zen_cast($default, $cast_to);
    }

    return null;
}

/**
 * Cast an input to a desired type; Frequently used by the tpl() template-settings helper function.
 * (Note: does not operate recursively on arrays)
 */
function zen_cast($input, ?string $cast_to): mixed
{
    // null case is listed first because it's likely to be the most common when called from the tpl() function
    // null treats it as a passthrough, doing no casting.
    if ($cast_to === null) {
        return $input;
    }

    switch ($cast_to) {
        case 'string':
            return (string)$input;
        case 'boolean':
        case 'bool':
            return (bool)$input;
        case 'int':
        case 'integer':
            return (int)$input;
        case 'double':
        case 'float':
            return (float)$input;
        case 'array':
            if (is_array($input)) {
                return $input;
            }
            return [$input];
        case 'passthru':
        default:
            return $input;
    }
}

/**
 * Get all template directories found in catalog folder structure
 *
 * @return array
 */
function zen_get_catalog_template_directories($include_template_default = false)
{
    if (!defined('DIR_FS_CATALOG_TEMPLATES')) {
        die('Fatal error: DIR_FS_CATALOG_TEMPLATES not defined.');
    }
    $dir = @dir(DIR_FS_CATALOG_TEMPLATES);
    if (!$dir) {
        die('Fatal error: DIR_FS_CATALOG_TEMPLATES not defined.');
    }
    $template_info = [];
    while ($tpl_dir_name = $dir->read()) {
        $path = DIR_FS_CATALOG_TEMPLATES . $tpl_dir_name;
        if (!is_dir($path)) {
            continue;
        }
        if ($include_template_default !== true && $tpl_dir_name == 'template_default') {
            continue;
        }
        if (file_exists($path . '/template_info.php')) {
            unset($uses_single_column_layout_settings);
            require $path . '/template_info.php';
            // expects the following variables to be set inside each respective template_info.php file
            $template_info[$tpl_dir_name] = [
                'name' => $template_name,
                'version' => $template_version,
                'author' => $template_author,
                'description' => $template_description,
                'screenshot' => $template_screenshot,
                'uses_single_column_layout_settings' => !empty($uses_single_column_layout_settings),
            ];
        }
    }
    $dir->close();
    return $template_info;
}

function zen_register_new_template($template_dir, $language_id)
{
    // @TODO: add duplicate-detection and empty-submission detection
    global $db;
    $sql = "SELECT *
            FROM " . TABLE_TEMPLATE_SELECT . "
            WHERE template_language = :lang:";
    $sql = $db->bindVars($sql, ':lang:', $language_id, 'integer');
    $check_query = $db->Execute($sql);
    if ($check_query->RecordCount() < 1) {
        $sql = "INSERT INTO " . TABLE_TEMPLATE_SELECT . " (template_dir, template_language)
                VALUES (:tpl:, :lang:)";
        $sql = $db->bindVars($sql, ':tpl:', $template_dir, 'string');
        $sql = $db->bindVars($sql, ':lang:', $language_id, 'integer');
        $db->Execute($sql);
        return $db->insert_ID();
    }
    return false;
}

/**
 * @return array of language_name and language_id entries
 */
function zen_get_template_languages_not_registered()
{
    global $db;
    $templates = [];
    $sql = "SELECT lng.name as language_name, lng.languages_id as language_id
            FROM " . TABLE_LANGUAGES . " lng
            WHERE lng.languages_id NOT IN (SELECT template_language FROM " . TABLE_TEMPLATE_SELECT . ")";
    $results = $db->Execute($sql);
    foreach ($results as $result) {
        $templates[] = $result;
    }
    return $templates;
}

/**
 * @param int $id
 * @param string $template_dir
 */
function zen_update_template_name_for_id($id, $template_dir)
{
    global $db;
    $sql = "UPDATE " . TABLE_TEMPLATE_SELECT . "
            SET template_dir = :tpl:
            WHERE template_id = :id:";
    $sql = $db->bindVars($sql, ':tpl:', $template_dir, 'string');
    $sql = $db->bindVars($sql, ':id:', $id, 'integer');
    $db->Execute($sql);
}

/**
 * @param int $id
 * @return bool whether template existed before delete
 */
function zen_deregister_template_id($id)
{
    global $db;
    $check_query = $db->Execute("SELECT template_language
                                 FROM " . TABLE_TEMPLATE_SELECT . "
                                 WHERE template_id = " . (int)$id);
    if ($check_query->RecordCount() && $check_query->fields['template_language'] != '0') {
        $db->Execute("DELETE FROM " . TABLE_TEMPLATE_SELECT . "
                      WHERE template_id = " . (int)$id);
        return true;
    }
    return false;
}
