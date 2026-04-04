<?php
// -----
// Part of the "Products' Options' Stock Manager" plugin by Cindy Merkin (lat9)
// Copyright (c) 2014-2024, Vinos de Frutas Tropicales
//
// Last updated: POSM v5.0.0
//
// Uses the AJAX infrastructure provided initially by Zen Cart v1.5.4 and updated in Zen Cart 1.5.5b
//
class zcAjaxOptionsStockDependencies extends base
{
    // -----
    // This class variable, normally set to false, can be set to (boolean) true to enable interface errors to be logged/reported.
    //
    protected $log_interface_errors = false;

    // -----
    // Additional protected class variables.
    //
    protected bool $debug;
    protected string $debug_log_file;

    // ----
    // Return the available option values for a specified options_id, given a products_id, an array of already-selected option name/value pairs.
    //
    public function availableOptionValues()
    {
        global $db;

        $this->debug = (POSM_ENABLE_DEBUG === 'true');
        $this->debug_log_file = DIR_FS_LOGS . '/myDEBUG-ajaxPOSM-' . date('Ymd-His') . '.log';

        $this->debug_message('On entry: ' . json_encode($_POST));

        $error = false;
        $error_message = '';
        $option_values = [];
        $last_selection = false;
        $extra_functions = false;

        if (!isset($_POST['products_id']) && isset($_POST['calling_pid'])) {
            $_POST['products_id'] = $_POST['calling_pid'];
        }

        if (!isset($_POST['products_id']) || !isset($_POST['options_id']) || !isset($_POST['selected_values'])) {
            $error = true;
            $error_message = sprintf(ERROR_INVALID_VARIABLES, 1);
            if ($this->log_interface_errors === true) {
                trigger_error($error_message . "\n" . json_encode($_POST), E_USER_WARNING);
            }
        } else {
            $products_id = (int)$_POST['products_id'];
            $options_id = (int)$_POST['options_id'];

            global $language_page_directory, $template_dir, $current_page, $current_page_base, $template, $languageLoader;

            $_GET['main_page'] = $current_page_base = $current_page = zen_get_info_page($products_id);
            require DIR_WS_MODULES . zen_get_module_directory('require_languages.php');

            $selected_values = str_replace(["'", '\\'], '', $_POST['selected_values']);
            $values_array = ($selected_values === '') ? [] : explode(',', $selected_values);
            $and_clause = '';
            $join_clause = '';
            $num_selected = 0;
            foreach ($values_array as $current_value) {
                $num_selected++;
                $sa = "sa$num_selected";
                $temp = explode(':', $current_value);
                $join_clause .= (" INNER JOIN " . TABLE_PRODUCTS_OPTIONS_STOCK_ATTRIBUTES . " $sa ON $sa.products_id = $products_id AND $sa.options_id = {$temp[0]} AND $sa.options_values_id = {$temp[1]}");
                $and_clause .= " AND sa0.pos_id = $sa.pos_id";
            }
            unset($temp, $values_array);

            $sql_query =
                "SELECT DISTINCT sa0.pos_id, sa0.options_values_id
                   FROM " . TABLE_PRODUCTS_OPTIONS_STOCK_ATTRIBUTES . " sa0$join_clause
                  WHERE sa0.products_id = $products_id
                    AND sa0.options_id = $options_id$and_clause";
            $this->debug_message("join_clause: $join_clause, and_clause: $and_clause, sql: $sql_query");
            $pos_info = $db->Execute($sql_query);

            if ($pos_info->EOF) {
                $unmanaged_option_info = $db->Execute(
                    "SELECT options_values_id 
                       FROM " . TABLE_PRODUCTS_ATTRIBUTES . "
                      WHERE products_id = $products_id 
                        AND options_id = $options_id"
                );
                if ($unmanaged_option_info->EOF) {
                    $error = true;
                    $error_message = sprintf(ERROR_INVALID_VARIABLES, 2);
                    if ($this->log_interface_errors === true) {
                        trigger_error($error_message . "\n" . json_encode($_POST), E_USER_WARNING);
                    }
                } else {
                    $this->debug_message("Processing unmanaged option ($options_id) ...");
                    $unmanaged_option = [
                        'quantity' => 0, 
                        'oos_message' => (POSM_SHOW_UNMANAGED_OPTIONS_STATUS === 'true') ? PRODUCTS_OPTIONS_STOCK_NOT_IN_STOCK : '', 
                        'model' => ''
                    ];
                    foreach ($unmanaged_option_info as $next_unmanaged) {
                        $unmanaged_option['options_values_id'] = $next_unmanaged['options_values_id'];
                        $option_values[] = $unmanaged_option;
                    }
                }
            } else {
                $this->debug_message("Processing managed option ($options_id) ...");
                $select_clause = "SELECT posa.options_id, posa.options_values_id, pos.products_quantity AS quantity, pos.pos_name_id AS oos_msg_id, pos.pos_model AS model, pos.pos_date AS oos_date";
                $this->notify('NOTIFY_AJAX_POSM_DEPENDENCIES_SELECT_CLAUSE', '', $select_clause);
                foreach ($pos_info as $next_pos) {
                    $attr_info = $db->Execute(
                        "$select_clause
                           FROM " . TABLE_PRODUCTS_OPTIONS_STOCK_ATTRIBUTES . " posa, " . TABLE_PRODUCTS_OPTIONS_STOCK . " pos
                          WHERE pos.pos_id = " . $next_pos['pos_id'] . "
                            AND pos.pos_id = posa.pos_id
                            AND posa.options_id = $options_id
                          LIMIT 1"
                    );
                    if (!$attr_info->EOF) {
                        $extra_info = '';
                        $this->notify('NOTIFY_AJAX_POSM_DEPENDENCIES_EXTRA_INFO', $attr_info->fields, $extra_info);
                        $attr_info->fields['extra_info'] = $extra_info;

                        if ($attr_info->fields['oos_date'] === '0001-01-01') {
                            $oos_date = '';
                        } else {
                            $oos_date = zen_date_short($attr_info->fields['oos_date']);
                        }
                        $attr_info->fields['oos_message'] = str_replace(
                            '[date]',
                            $oos_date,
                            get_pos_oos_name($attr_info->fields['oos_msg_id'], $_SESSION['languages_id'])
                        );
                        $option_values[] = $attr_info->fields;
                    }
                }
            }
            $select_info = $db->Execute(
                "SELECT COUNT(pos_id) AS total, COUNT(DISTINCT pos_id) AS unique_ids
                   FROM " . TABLE_PRODUCTS_OPTIONS_STOCK_ATTRIBUTES . "
                  WHERE products_id = $products_id"
            );
            $last_selection = ($num_selected == (($select_info->fields['unique_ids'] == 0) ? 1 : (($select_info->fields['total'] / $select_info->fields['unique_ids']) - 1)));

            $this->notify('NOTIFY_AJAX_POSM_DEPENDENCIES_EXTENSION_INFO', $option_values, $extra_functions);
            $this->debug_message(
                "option_values -- " . json_encode($option_values, JSON_PRETTY_PRINT) .
                "\nselect_info: " . json_encode($select_info->fields, JSON_PRETTY_PRINT) .
                "\nlast_selection [$last_selection]" .
                "\nextension_info: " . json_encode($extra_functions, JSON_PRETTY_PRINT)
            );
        }

        $return_array = [
            'error' => $error, 
            'error_message' => $error_message,
            'option_values' => $option_values,
            'last_selection' => $last_selection
        ];
        $return_array['extra_functions'] = ($extra_functions !== false) ? $extra_functions : [];
        return $return_array;
    }

    function debug_message($message)
    {
        if ($this->debug) {
            error_log("$message\n", 3, $this->debug_log_file);
        }
    }
}
