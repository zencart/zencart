<?php

if (!defined('IS_ADMIN_FLAG') || IS_ADMIN_FLAG !== true) {
    die('Illegal Access');
}

class zcObserverAdminSubmenus extends base
{
    public function __construct()
    {
        $this->attach($this, ['NOTIFY_LANGUAGE_CHANGE_REQUESTED_BY_ADMIN_VISITOR']);
    }

    public function update(&$class, $eventID, $lang, &$olan)
    {
        $admin_submenus = [];
        $table_type = [];

        $conn = new mysqli(constant('DB_SERVER'), DB_SERVER_USERNAME, DB_SERVER_PASSWORD, constant('DB_DATABASE'));
        
        $lang_dir_name = DIR_WS_LANGUAGES . $olan->language['directory'] . '/admin_submenus';
        if (is_dir($lang_dir_name)) {
            $dir_content = array_diff(scandir($lang_dir_name), array('..', '.'));
            foreach($dir_content as $key => $filename) {
                if (is_file($lang_dir_name . '/' . $filename) && strpos($filename, 'admin_menus') === 0) { // checking for files starting by 'admin_menus' to include them as they should contain '$admin_submenus' table data.
                    include($lang_dir_name . '/' . $filename);
                }
            }
        }
        
        $plugin_infos = $conn->query('SELECT unique_key, version FROM plugin_control WHERE 1'); // check fo installed encapsulated plugins
        while ($plugin = $plugin_infos->fetch_assoc()) {
            $plugin_lang_dir_name = DIR_FS_CATALOG . 'zc_plugins/' . $plugin['unique_key'] . '/' . $plugin['version'] . '/admin/includes/languages/' . $olan->language['directory'] . '/admin_submenus';
            if (is_dir($plugin_lang_dir_name)) {
                $plugin_dir_content = array_diff(scandir($plugin_lang_dir_name), array('..', '.'));
                foreach($plugin_dir_content as $key => $filename) {
                    if (is_file($plugin_lang_dir_name . '/' . $filename) && strpos($filename, 'admin_menus') === 0) { // checking for files starting by 'admin_menus' to include them as they should contain '$admin_submenus' table data.
                        include($plugin_lang_dir_name . '/' . $filename);
                    }
                }
            }
        }
        if (!empty($admin_submenus)) {
            foreach($admin_submenus as $table_name => $menus) { // Extract translation data for each table that needs translation.
                if (empty($table_type[$table_name])) continue;
                switch ($table_type[$table_name]) { // Different queries are needed because tables have differents fields and unique index keys.
                    case 1: // Changes configuration table or product_type_layout table columns configuration_title and configuration_description to new language
                        $query_configuration = $conn->prepare("UPDATE " . $table_name . " SET configuration_title = ?, configuration_description = ? WHERE configuration_key = ?");
                        foreach($menus as $configuration_key => $translation) {
                            $query_configuration->bind_param("sss", $translation['title'], $translation['description'], $configuration_key);
                            $query_configuration->Execute();
                        }
                        $query_configuration->close();
                        break;
                    case 2: // Changes configuration_group table columns configuration_group_title and configuration_group_description to new language
                        $query_conf_group = $conn->prepare("UPDATE " . $table_name . " SET configuration_group_title = ?, configuration_group_description = ? WHERE configuration_group_id = ?");
                        foreach($menus as $configuration_group_id => $translation) {
                            $query_conf_group->bind_param("ssi", $translation['group_title'], $translation['group_description'], $configuration_group_id);
                            $query_conf_group->Execute();
                        }
                        $query_conf_group->close();
                        break;
                    case 3; // Changes product_types table column type_name to new language
                        $query_types = $conn->prepare("UPDATE " . $table_name . " SET type_name = ? WHERE type_id = ?");
                        foreach($menus as $type_id => $type_name) {
                            $query_types->bind_param("si", $type_name, $type_id);
                            $query_types->Execute();
                        }
                        $query_types->close();
                        break;
                    case 4; // Changes plugin_control table column description to new language
                        $query_plugin = $conn->prepare("UPDATE " . $table_name . " SET description = ? WHERE unique_key = ?");
                        foreach($menus as $type_id => $type_name) {
                            $query_plugin->bind_param("ss", $type_name, $type_id);
                            $query_plugin->Execute();
                        }
                        $query_plugin->close();
                        break;
                }
            }
        }
        $conn->close();
    }
}
