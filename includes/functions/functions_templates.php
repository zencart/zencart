<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license https://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2024 Aug 18 Modified in v2.1.0-alpha2 $
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
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
                'uses_mobile_sidebox_settings' => !isset($uses_mobile_sidebox_settings) || !empty($uses_mobile_sidebox_settings),
                'template_path' => $path,
                'has_template_settings' => file_exists($path . '/template_settings.php'),
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
