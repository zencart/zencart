<?php
/**
 * functions_customer_groups
 *
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2021 Apr 26 New in v1.5.8-alpha $
 */

/**
 * @param int $customer_id
 * @param int $group_id
 * @param bool $ignore_cache
 * @return bool
 */
function zen_customer_belongs_to_group($customer_id, $group_id, $ignore_cache = false)
{
    global $db;
    $sql = "SELECT customer_id
            FROM " . TABLE_CUSTOMERS_TO_GROUPS . "
            WHERE customer_id = " . (int)$customer_id . "
            AND group_id = " . (int)$group_id;
    if (!$ignore_cache) {
        $results = $db->Execute($sql, 1, true, 60);
    } else {
        $results = $db->ExecuteNoCache($sql);
    }

    return (!$results->EOF);
}

/**
 * @param int $customer_id
 * @return array
 */
function zen_groups_customer_belongs_to($customer_id)
{
    global $db;
    $groups = [];

    $sql = "SELECT group_id, group_name
            FROM " . TABLE_CUSTOMER_GROUPS . " cg
            LEFT JOIN " . TABLE_CUSTOMERS_TO_GROUPS . " ctg USING (group_id)
            WHERE customer_id = " . (int)$customer_id . "
            ORDER BY group_name, group_id";

    $results = $db->Execute($sql);

    foreach ($results as $result) {
        $groups[$result['group_id']] = $result['group_name'];
//        $groups[] = ['id' => $result['group_id'], 'text' => $result['group_name']];
    }

    return $groups;
}

/**
 * @param int $customer_id
 * @param array $groups
 * @return bool
 */
function zen_sync_customer_group_assignments($customer_id, $groups)
{
    if (empty($customer_id)) return false;

    $current_groups = zen_groups_customer_belongs_to($customer_id);

    foreach ($groups as $key => $group_id) {
        if (!array_key_exists($group_id, $current_groups)) {
            zen_assign_customer_to_group($customer_id, $group_id);
        }
    }
    $group_values = array_values($groups);
    foreach ($current_groups as $group_id => $name) {
        if (!array_key_exists($group_id, $group_values)) {
            zen_remove_customer_from_group($customer_id, $group_id);
        }
    }
}

/**
 * @param int $customer_id
 * @param int $group_id
 * @return bool
 */
function zen_assign_customer_to_group($customer_id, $group_id)
{
    if (zen_customer_belongs_to_group($customer_id, $group_id, true)) {
        return false; // already in group
    }

    $sql_data_array = [
        'customer_id' => (int)$customer_id,
        'group_id' => (int)$group_id,
    ];
    zen_db_perform(TABLE_CUSTOMERS_TO_GROUPS, $sql_data_array);

    return true;
}

/**
 * @param int $customer_id
 * @param int $group_id
 * @return bool
 */
function zen_remove_customer_from_group($customer_id, $group_id)
{
    global $db;
    $db->Execute("DELETE FROM " . TABLE_CUSTOMERS_TO_GROUPS . " WHERE customer_id = " . (int)$customer_id . " AND group_id = " . (int)$group_id);
    return true;
}

/**
 * @param int $group_id
 * @return int
 */
function zen_count_customers_in_group($group_id)
{
    global $db;
    $sql = "SELECT count(customer_id) as customer_count
            FROM " . TABLE_CUSTOMERS_TO_GROUPS . "
            WHERE group_id = " . (int)$group_id;
    $results = $db->Execute($sql);

    if (empty($results) || $results->EOF) {
        return 0;
    }

    return (int)$results->fields['customer_count'];
}

/**
 * @param int $group_id
 * @return string
 */
function zen_get_customer_group_name($group_id)
{
    global $db;
    if (empty($group_id)) {
        return TEXT_GROUP_ALL;
    }

    $sql = "SELECT group_name FROM " . TABLE_CUSTOMER_GROUPS . " WHERE group_id = " . (int)$group_id;
    $result = $db->Execute($sql, 1);

    if ($result->EOF) return '';

    return $result->fields['group_name'];
}

/**
 * @param int $group_id
 * @param string $key
 * @return string html pulldown menu
 */
function zen_cfg_select_customer_group($group_id, $key = '', $name = '', $include_zero = true, $multiple = false)
{
    global $db;
    if (empty($name)) {
        $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');
    }

    $groups = [];
    if ($include_zero) {
        $groups[] = ['id' => '0', 'text' => TEXT_GROUP_ALL];
    }

    $available_groups = zen_get_all_customer_groups();

    $groups = array_merge($groups, $available_groups);

    $multiple_rows_size = min(count($groups), 20);

    return zen_draw_pull_down_menu($name, $groups, $group_id, 'class="form-control"' . ($multiple ? 'size="' . $multiple_rows_size . '" multiple' : ''));
}

/**
 * @return array
 */
function zen_get_all_customer_groups()
{
    global $db;
    $sql = "SELECT group_id, group_name
            FROM " . TABLE_CUSTOMER_GROUPS . "
            ORDER BY group_name, group_id";

    $results = $db->Execute($sql);

    $groups = [];
    foreach ($results as $result) {
        $groups[] = ['id' => $result['group_id'], 'text' => $result['group_name']];
    }

    return $groups;
}

/**
 * @param string $group_name
 * @param string $group_comment
 * @return int|string
 */
function zen_create_customer_group($group_name, $group_comment)
{
    global $db;
    if (empty($group_name)) {
        return 'Error: nothing to do.';
    }

    $sql_data_array = [
        'group_name' => zen_db_input($group_name),
        'group_comment' => zen_db_input($group_comment)
    ];
    zen_db_perform(TABLE_CUSTOMER_GROUPS, $sql_data_array);
    return (int)$db->insert_ID();
}

/**
 * @param int $group_id
 * @param array $data
 * @return bool|string
 */
function zen_update_customer_group($group_id, $data)
{
    global $db;

    if (empty($group_id) || empty($data)) {
        return 'Error: nothing to do.';
    }

    $valid_fields = ['group_name', 'group_comment'];
    $sql_data_array = [];

    foreach ($valid_fields as $field) {
        if (isset($data[$field])) {
            $sql_data_array[$field] = $db->prepareInput($data[$field]);
        }
    }
    zen_db_perform(TABLE_CUSTOMER_GROUPS, $sql_data_array, 'update', "group_id = " . (int)$group_id);
    return true;
}

/**
 * @param int $group_id
 * @param bool $also_unassign_customers
 * @return bool|string
 */
function zen_delete_customer_group($group_id, $also_unassign_customers = true)
{
    global $db;
    $customers_in_group = zen_count_customers_in_group((int)$group_id);

    if ($customers_in_group) {
        if ($also_unassign_customers === false) {
            return sprintf(ERROR_CANNOT_DELETE_CUSTOMER_GROUP_DUE_TO_LINKED_CUSTOMERS, $customers_in_group);
        }
        $db->Execute("DELETE FROM " . TABLE_CUSTOMERS_TO_GROUPS . " WHERE group_id = " . (int)$group_id);
    }

    $db->Execute("DELETE FROM " . TABLE_CUSTOMER_GROUPS . " WHERE group_id = " . (int)$group_id);
    return true;
}

