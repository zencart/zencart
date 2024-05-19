<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:      $
 *
 */

namespace Zencart\PluginSupport;

use queryFactory;

trait ScriptedInstallHelpers
{
    protected queryFactory $dbConn;

    /**
     * Get details of current configuration record entry, false if not found.
     * Optional: when $only_check_existence is true, will simply return true/false.
     */
    public function getConfigurationKeyDetails(string $key_name, bool $only_check_existence = false): array|bool
    {
        $sql = "SELECT * FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = '" . zen_db_input($key_name) . "'";
        $result = $this->dbConn->Execute($sql, 1);

        // false if not found, or if existence-check fails
        if ($only_check_existence || $result->EOF) {
            return !$result->EOF;
        }

        return $result->fields;
    }

    public function addConfigurationKey(string $key_name, array $properties): int
    {
        $exists = $this->getConfigurationKeyDetails($key_name, true);
        if ($exists) {
            return 0;
        }

        $fields = [
            //'configuration_key', // VARCHAR(180)
            'configuration_title',
            'configuration_value',
            'configuration_description',
            'configuration_group_id',
            'sort_order', // INT(5) default NULL
            'use_function', // TEXT default NULL
            'set_function', // TEXT default NULL
            'val_function', // TEXT default NULL
            //'date_added', // DATETIME
            //'last_modified', // DATETIME default NULL
        ];

        $sql_data_array = [];
        $sql_data_array['configuration_key'] = $key_name;
        foreach ($fields as $field) {
            if (isset($properties[$field])) {
                $sql_data_array[$field] = $properties[$field];
            }
        }
        $sql_data_array['date_added'] = 'now()';

        zen_db_perform(TABLE_CONFIGURATION, $sql_data_array);

        $insert_id = $this->dbConn->insert_ID();

        $sql_data_array['configuration_key_id'] = $insert_id;
        zen_record_admin_activity('Deleted admin pages for page keys: ' . print_r($sql_data_array, true), 'warning');

        return $insert_id;
    }

    public function updateConfigurationKey(string $key_name, array $properties): int
    {
        $fields = [
            'configuration_title',
            'configuration_value',
            'configuration_description',
            'configuration_group_id',
            'sort_order', // INT(5) default NULL
            'use_function', // TEXT default NULL
            'set_function', // TEXT default NULL
            'val_function', // TEXT default NULL
            //'date_added', // DATETIME
            //'last_modified', // DATETIME default NULL
        ];

        $sql_data_array = [];
        foreach ($fields as $field) {
            if (isset($properties[$field])) {
                $sql_data_array[$field] = $properties[$field];
            }
        }
        $sql_data_array['last_modified'] = 'now()';

        zen_db_perform(TABLE_CONFIGURATION, $sql_data_array, 'UPDATE', "configuration_key = '" . zen_db_input($key_name) . "'");
        $rows = $this->dbConn->affectedRows();

        $sql_data_array['configuration_key'] = $key_name;
        zen_record_admin_activity('Updated configuration record: ' . print_r($sql_data_array, true), 'warning');

        return $rows;
    }

    public function deleteConfigurationKeys(array $key_names): int
    {
        if (empty($key_names)) {
            return 0;
        }
        $keys_list = implode("','", array_map(fn ($val) => zen_db_input($val), $key_names));
        $sql = "DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key IN ('" . $keys_list . "')";
        $this->dbConn->Execute($sql);

        $rows = $this->dbConn->affectedRows();

        zen_record_admin_activity('Deleted configuration record(s): ' . $keys_list, 'warning');

        return $rows;
    }

    public function addConfigurationGroup(string $group_title, array $properties): int
    {
        $fields = [
            'configuration_group_title', // varchar(64)
            'configuration_group_description', // varchar(255)
            'sort_order', // int(5)
            'visible', // 0/1 default '1'
        ];

        $sql_data_array = [];
        foreach ($fields as $field) {
            if (isset($properties[$field])) {
                $sql_data_array[$field] = $properties[$field];
            }
        }

        zen_db_perform(TABLE_CONFIGURATION_GROUP, $sql_data_array);

        $insert_id = $this->dbConn->insert_ID();

        // update array for subsequent logging
        $sql_data_array['configuration_group_id'] = $insert_id;

        if (empty($sql_data_array['sort_order'])) {
            // manually set sort order if none provided:
            zen_db_perform(TABLE_CONFIGURATION_GROUP, ['sort_order' => $insert_id], 'update', ' WHERE configuration_group_id = ' . $insert_id);
            $sql_data_array['sort_order'] = $insert_id;
        }

        zen_record_admin_activity('Configuration Group added: ' . print_r($sql_data_array, true), 'warning');

        return $insert_id;
    }

    public function updateConfigurationGroup(int $group_id, array $properties): int
    {
        $fields = [
            'configuration_group_title', // varchar(64) NOT NULL default ''
            'configuration_group_description', // varchar(255) NOT NULL default ''
            'sort_order', // int(5) default NULL
            'visible', // int(1) default '1'
        ];

        $sql_data_array = [];
        foreach ($fields as $field) {
            if (isset($properties[$field])) {
                $sql_data_array[$field] = $properties[$field];
            }
        }

        zen_db_perform(TABLE_CONFIGURATION_GROUP, $sql_data_array, 'UPDATE', "configuration_group_id = " . (int)$group_id);

        $rows = $this->dbConn->affectedRows();

        $sql_data_array['configuration_group_id'] = $group_id;
        zen_record_admin_activity('Updated configuration group: ' . print_r($sql_data_array, true), 'warning');

        return $rows;

    }

    public function deleteConfigurationGroup(int $group_id): int
    {
        $sql = "DELETE FROM " . TABLE_CONFIGURATION_GROUP . " WHERE configuration_group_id = " . (int)$group_id;

        $this->dbConn->Execute($sql);

        $rows = $this->dbConn->affectedRows();

        zen_record_admin_activity('Deleted configuration group ID: ' . (int)$group_id, 'warning');

        return $rows;
    }

    public function getConfigurationGroupDetails(int|string $group, bool $only_check_existence = false): array|bool
    {

        $sql = "SELECT * FROM " . TABLE_CONFIGURATION_GROUP;

        if (is_numeric($group)) {
            $sql .= " WHERE configuration_group_id = " . (int)$group;
        } else {
            $sql .= " WHERE configuration_group_name = '" . \zen_db_input($group) . "'";
        }

        $result = $this->dbConn->Execute($sql);

        // false if not found, or if existence-check fails
        if ($only_check_existence || $result->EOF) {
            return !$result->EOF;
        }

        return $result->fields;
    }



    // @TODO - WORK IN PROGRESS...
    public function getSelfDetails(): array
    {
        global $installedPlugins;
        foreach ($installedPlugins as $plugin) {
            $namespaceAdmin = 'Zencart\\Plugins\\Admin\\' . ucfirst($plugin['unique_key']);
            $namespaceCatalog = 'Zencart\\Plugins\\Catalog\\' . ucfirst($plugin['unique_key']);
            $filePath = DIR_FS_CATALOG . 'zc_plugins/' . $plugin['unique_key'] . '/' . $plugin['version'] . '/';
        }

        // installed or not
        // currently installed version
        // manifest.php contents
    }

}
