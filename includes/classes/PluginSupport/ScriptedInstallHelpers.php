<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Sep 21 Modified in v2.1.0-beta1 $
 *
 */

namespace Zencart\PluginSupport;

use queryFactory;
use queryFactoryResult;

trait ScriptedInstallHelpers
{
    protected queryFactory $dbConn;

    /**
     * Get details of current configuration record entry, false if not found.
     * Optional: when $only_check_existence is true, will simply return true/false.
     */
    protected function getConfigurationKeyDetails(string $key_name, bool $only_check_existence = false): array|bool
    {
        $sql = "SELECT * FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = '" . $this->dbConn->prepare_input($key_name) . "'";
        $result = $this->executeInstallerSelectQuery($sql, 1);

        // false if not found, or if existence-check fails
        if ($only_check_existence || $result->EOF) {
            return !$result->EOF;
        }

        return $result->fields;
    }

    protected function addConfigurationKey(string $key_name, array $properties): int
    {
        $exists = $this->getConfigurationKeyDetails($key_name, true);
        if ($exists !== false) {
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
        $sql_data_array[] = ['fieldName' => 'configuration_key', 'value' => $key_name, 'type' => 'string'];
        foreach ($fields as $field) {
            if (isset($properties[$field])) {
                $type = 'string';
                if (in_array($field, ['configuration_group_id', 'sort_order'])) {
                    $type = 'integer';
                }
                $sql_data_array[] = ['fieldName' => $field, 'value' => $properties[$field], 'type' => $type];
            }
        }

        $sql_data_array[] = ['fieldName' => 'date_added', 'value' => 'NOW()', 'type' => 'passthru'];

        $this->executeInstallerDbPerform(TABLE_CONFIGURATION, $sql_data_array);

        $insert_id = $this->dbConn->insert_ID();

        $sql_data_array[] = ['fieldName' => 'configuration_key_id', 'value' => $insert_id, 'type' => 'integer'];
        zen_record_admin_activity('Deleted admin pages for page keys: ' . print_r($sql_data_array, true), 'warning');

        return $insert_id;
    }

    protected function updateConfigurationKey(string $key_name, array $properties): int
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
                $type = 'string';
                if (in_array($field, ['configuration_group_id', 'sort_order'])) {
                    $type = 'integer';
                }
                $sql_data_array[] = ['fieldName' => $field, 'value' => $properties[$field], 'type' => $type];
            }
        }
        $sql_data_array[] = ['fieldName' => 'last_modified', 'value' => 'now()', 'type' => 'passthru'];

        $this->executeInstallerDbPerform(TABLE_CONFIGURATION, $sql_data_array, 'UPDATE', "configuration_key = '" . $this->dbConn->prepare_input($key_name) . "'");
        $rows = $this->dbConn->affectedRows();

        $sql_data_array[] = ['fieldName' => 'configuration_key', 'value' => $key_name, 'type' => 'string'];
        zen_record_admin_activity('Updated configuration record: ' . print_r($sql_data_array, true), 'warning');

        return $rows;
    }

    protected function deleteConfigurationKeys(array $key_names): int
    {
        if (empty($key_names)) {
            return 0;
        }

        $db = $this->dbConn;
        $keys_list = implode("','", array_map(static fn($val) => $db->prepare_input($val), $key_names));

        $sql = "DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key IN ('" . $keys_list . "')";
        $this->executeInstallerSelectQuery($sql);

        $rows = $this->dbConn->affectedRows();

        zen_record_admin_activity('Deleted configuration record(s): ' . $keys_list . ", $rows rows affected.", 'warning');

        return $rows;
    }


    protected function getOrCreateConfigGroupId(string $config_group_title, string $config_group_description, ?int $sort_order = 1): int
    {
        $config_group_title = $this->dbConn->prepare_input($config_group_title);
        $config_group_description = $this->dbConn->prepare_input($config_group_description);
        $sort_order = (int)($sort_order ?? 0);

        $sql =
            "SELECT configuration_group_id
               FROM " . TABLE_CONFIGURATION_GROUP . "
              WHERE configuration_group_title = '$config_group_title'
              LIMIT 1";
        $check = $this->executeInstallerSelectQuery($sql);
        if (!$check->EOF) {
            return (int)$check->fields['configuration_group_id'];
        }

        $sql =
            "INSERT INTO " . TABLE_CONFIGURATION_GROUP . "
                (configuration_group_title, configuration_group_description, sort_order, visible)
             VALUES
                ('$config_group_title', '$config_group_description', $sort_order, 1)";
        $this->executeInstallerSql($sql);

        $sql = "SELECT configuration_group_id FROM " . TABLE_CONFIGURATION_GROUP . " WHERE configuration_group_title = '$config_group_title' LIMIT 1";
        $result = $this->executeInstallerSelectQuery($sql);
        $cgi = (int)$result->fields['configuration_group_id'];

        if (empty($sort_order)) {
            $sql = "UPDATE " . TABLE_CONFIGURATION_GROUP . " SET sort_order = $cgi WHERE configuration_group_id = $cgi LIMIT 1";
            $this->executeInstallerSql($sql);
        }

        return $cgi;
    }

    protected function addConfigurationGroup(array $properties): int
    {
        $exists = $this->getConfigurationKeyDetails($this->dbConn->prepare_input($properties['configuration_group_title']));
        if ($exists !== false) {
            return (int)$exists['configuration_group_id'];
        }

        $fields = [
            'configuration_group_title', // varchar(64)
            'configuration_group_description', // varchar(255)
            'sort_order', // int (will be made to match auto-increment id if not specified)
            'visible', // 0/1 default '1'
        ];

        $sql_data_array = [];
        foreach ($fields as $field) {
            if (isset($properties[$field])) {
                $type = 'string';
                if (in_array($field, ['sort_order', 'visible'])) {
                    $type = 'integer';
                }
                $sql_data_array[] = ['fieldName' => $field, 'value' => $properties[$field], 'type' => $type];
            }
        }

        $this->executeInstallerDbPerform(TABLE_CONFIGURATION_GROUP, $sql_data_array);
        $insert_id = $this->dbConn->insert_ID();

        // update array for subsequent logging
        $sql_data_array[] = ['fieldName' => 'configuration_group_id', 'value' => $insert_id, 'type' => 'integer'];

        // manually set sort order if none was provided:
        if (empty($properties['sort_order'])) {
            $sql = "UPDATE " . TABLE_CONFIGURATION_GROUP . " SET sort_order = $insert_id WHERE configuration_group_id = $insert_id LIMIT 1";
            $this->executeInstallerSql($sql);
            $sql_data_array[] = ['fieldName' => 'sort_order', 'value' => $insert_id, 'type' => 'integer'];
        }

        zen_record_admin_activity('Configuration Group added: ' . print_r($sql_data_array, true), 'warning');

        return $insert_id;
    }

    protected function updateConfigurationGroup(int $group_id, array $properties): int
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
                $type = 'string';
                if (in_array($field, ['sort_order', 'visible'])) {
                    $type = 'integer';
                }
                $sql_data_array[] = ['fieldName' => $field, 'value' => $properties[$field], 'type' => $type];
            }
        }

        $this->executeInstallerDbPerform(TABLE_CONFIGURATION_GROUP, $sql_data_array, 'UPDATE', "configuration_group_id = " . (int)$group_id);
        $rows = $this->dbConn->affectedRows();

        $sql_data_array[] = ['fieldName' => 'configuration_group_id', 'value' => $group_id, 'type' => 'integer'];
        zen_record_admin_activity('Updated configuration group: ' . print_r($sql_data_array, true) . ", $rows rows affected.", 'warning');

        return $rows;
    }

    protected function deleteConfigurationGroup(int|string $group, bool $cascadeDeleteKeysToo = false): int
    {
        $rows = 0;

        $sql = "SELECT * FROM " . TABLE_CONFIGURATION_GROUP;
        if (is_numeric($group)) {
            $sql .= " WHERE configuration_group_id = " . (int)$group;
        } else {
            $sql .= " WHERE configuration_group_title = '" . $this->dbConn->prepare_input($group) . "'";
        }
        $result = $this->executeInstallerSelectQuery($sql);

        $cgi = (int)($result->fields['configuration_group_id'] ?? 0);

        if ($cascadeDeleteKeysToo) {
            $sql = "DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_group_id = $cgi";
            $this->executeInstallerSql($sql);
            $rows += $this->dbConn->affectedRows();
        }

        $sql = "DELETE FROM " . TABLE_CONFIGURATION_GROUP . " WHERE configuration_group_id = " . (int)$cgi;
        $this->executeInstallerSql($sql);
        $rows += $this->dbConn->affectedRows();

        zen_record_admin_activity("Deleted configuration group ID: '$group'; $rows rows affected.", 'warning');

        return $rows;
    }

    protected function getConfigurationGroupDetails(int|string $group, bool $only_check_existence = false): array|bool
    {
        $sql = "SELECT * FROM " . TABLE_CONFIGURATION_GROUP;
        if (is_numeric($group)) {
            $sql .= " WHERE configuration_group_id = " . (int)$group;
        } else {
            $sql .= " WHERE configuration_group_title = '" . $this->dbConn->prepare_input($group) . "'";
        }

        $result = $this->executeInstallerSelectQuery($sql);

        // false if not found, or if existence-check fails
        if ($only_check_existence || $result->EOF) {
            return !$result->EOF;
        }

        return $result->fields;
    }

    protected function executeInstallerSelectQuery(string $sql, ?int $limit = null): bool|queryFactoryResult
    {
        $this->dbConn->dieOnErrors = false;
        $result = $this->dbConn->Execute($sql, $limit);

        if ($this->dbConn->error_number !== 0) {
            $this->errorContainer->addError(0, $this->dbConn->error_text, true, PLUGIN_INSTALL_SQL_FAILURE);
            return false;
        }

        $this->dbConn->dieOnErrors = true;
        return $result;
    }

    protected function executeInstallerDbPerform(string $table, array $sql_data_array, $performType = 'INSERT', string $whereCondition = '', $debug = false): bool
    {
        $this->dbConn->dieOnErrors = false;
        $this->dbConn->perform($table, $sql_data_array, $performType, $whereCondition, $debug);

        if ($this->dbConn->error_number !== 0) {
            $this->errorContainer->addError(0, $this->dbConn->error_text, true, PLUGIN_INSTALL_SQL_FAILURE);
            return false;
        }
        $this->dbConn->dieOnErrors = true;
        return true;
    }
}
