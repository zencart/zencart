<?php
// -----
// Part of the "Product Options Stock" plugin by Cindy Merkin (cindy@vinosdefrutastropicales.com)
// Copyright (c) 2015-2022 Vinos de Frutas Tropicales
//
// Last updated: POSM 4.4.0
//
// Note: For POSM versions prior to v4.1.0, this class was imbedded in the products_options_stock_view_all.php script.
//
if (!defined('IS_ADMIN_FLAG') || IS_ADMIN_FLAG !== true) {
    die('Illegal Access');
}
class PosmViewAll extends base
{
    public
        $options_names = [],
        $options_values_names = [];

    protected
        $sort_order,
        $posm_stock_reorder_level,
        $pid,
        $view_all,
        $pid_options,
        $num_options,
        $product_options;

    // -----
    // When the class is constructed, build up arrays that contain all option-name and option-value-name values used
    // within the currently-managed product option-combinations.
    //
    public function __construct($posm_stock_reorder_level, $sort_order)
    {
        global $db;

        $this->sort_order = $sort_order;
        $this->posm_stock_reorder_level = $posm_stock_reorder_level;
        $names_list = $db->Execute(
            "SELECT DISTINCT posa.options_id, po.products_options_name as options_name, po.products_options_sort_order
               FROM " . TABLE_PRODUCTS_OPTIONS_STOCK_ATTRIBUTES . " posa
                    INNER JOIN " . TABLE_PRODUCTS_OPTIONS . " po
                        ON po.products_options_id = posa.options_id
                       AND po.language_id = " . (int)$_SESSION['languages_id'] . "
           ORDER BY po.products_options_sort_order ASC, po.products_options_name ASC"
        );
        foreach ($names_list as $name) {
            $this->options_names[$name['options_id']] = $name['options_name'];
        }
        unset($names_list);

        $names_list = $db->Execute(
            "SELECT DISTINCT posa.options_values_id as values_id, pov.products_options_values_name as values_name, pov.products_options_values_sort_order
               FROM " . TABLE_PRODUCTS_OPTIONS_STOCK_ATTRIBUTES . " posa
                    INNER JOIN " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov
                        ON posa.options_values_id = pov.products_options_values_id
                       AND pov.language_id = " . (int)$_SESSION['languages_id'] . "
           ORDER BY pov.products_options_values_sort_order ASC, pov.products_options_values_name ASC"
        );
        foreach ($names_list as $name) {
            $this->options_values_names[$name['values_id']] = $name['values_name'];
        }
        unset($names_list);
    }

    // -----
    // Function that gathers the to-be-output information for a specific
    // product.
    //
    public function outputProduct($pID, $view_all)
    {
        global $db;

        $this->pid = $pID = (int)$pID;
        $this->view_all = (bool)$view_all;
        $options_list = $db->Execute(
            "SELECT DISTINCT posa.options_id, po.products_options_sort_order, po.products_options_name
               FROM " . TABLE_PRODUCTS_OPTIONS_STOCK_ATTRIBUTES . " posa
                  INNER JOIN " . TABLE_PRODUCTS_OPTIONS . " po
                      ON po.products_options_id = posa.options_id
                     AND po.language_id = " . (int)$_SESSION['languages_id'] . "
              WHERE posa.products_id = $pID
           ORDER BY po.products_options_sort_order ASC, po.products_options_name ASC"
        );
        $this->pid_options = [];
        foreach ($options_list as $option) {
            $this->pid_options[] = $option['options_id'];
        }
        $this->num_options = count($this->pid_options);
        $this->product_options = [];
        if ($this->num_options !== 0) {
            $this->gatherOptions();
            $this->sortOptions();
        }
        return $this->product_options;
    }

    protected function gatherOptions($level = 0, $option_name = '', $option_values = [])
    {
        global $db, $posObserver;

        if ($level >= $this->num_options) {
            $hash = generate_pos_option_hash($this->pid, $option_values);
            $posm_record = $db->Execute(
                "SELECT *
                   FROM " . TABLE_PRODUCTS_OPTIONS_STOCK . "
                  WHERE products_id = " . $this->pid . "
                    AND pos_hash = '$hash'
                  LIMIT 1"
            );
            if ($posm_record->EOF) {
                $posObserver->debug_message("View All: Unknown product/option combination.  outputOptions ($level, $option_name, \n" . var_export($option_values, true));
            } elseif ($this->view_all === true || $posm_record->fields['products_quantity'] <= $this->posm_stock_reorder_level) {
                $this->product_options[] = [
                    'sort' => count($this->product_options),
                    'pid' => $this->pid,
                    'option_name' => $option_name,
                    'fields' => $posm_record->fields,
               ];
            }
        } else {
            $pID = $this->pid;
            $options_id = $this->pid_options[$level];
            $options_values_list = $db->Execute(
                "SELECT pa.options_values_id
                   FROM " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                      INNER JOIN " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov
                         ON pov.products_options_values_id = pa.options_values_id
                        AND pov.language_id = " . $_SESSION['languages_id'] . "
                  WHERE pa.products_id = $pID
                    AND pa.options_id = $options_id
                    AND pa.attributes_display_only = 0
               ORDER BY pa.products_options_sort_order ASC, pov.products_options_values_name ASC"
            );
            foreach ($options_values_list as $option_value) {
                $options_values_id = $option_value['options_values_id'];

                // -----
                // Only continue if the named option-value is being managed (otherwise, its values' names haven't
                // been recorded.
                //
                if (isset($this->options_values_names[$options_values_id])) {
                    $option_values[$options_id] = $options_values_id;
                    $current_option_name = $option_name . (($option_name == '') ? '' : ', ') . '<span class="option-name">' . $this->options_names[$options_id] . '</span>: <span class="value-name">' . $this->options_values_names[$options_values_id] . '</span>';

                    $this->gatherOptions($level+1, $current_option_name, $option_values);
                }
            }
        }
    }

    protected function sortOptions()
    {
        if ($this->sort_order === 'model-asc') {
            uasort($this->product_options, function($a, $b)
            {
                if (!empty($a['fields']['pos_model']) || !empty($b['fields']['pos_model'])) {
                    $result = strcasecmp($a['fields']['pos_model'], $b['fields']['pos_model']);
                } else {
                    $result = ($a['sort'] < $b['sort']) ? -1 : 1;
                }
                return ($result < 0) ? -1 : (($result === 0) ? 0 : 1);
            });
        } elseif ($this->sort_order === 'model-desc') {
            uasort($this->product_options, function($a, $b)
            {
                if (!empty($a['fields']['pos_model']) || !empty($b['fields']['pos_model'])) {
                    $result = strcasecmp($b['fields']['pos_model'], $a['fields']['pos_model']);
                } else {
                    $result = ($a['sort'] < $b['sort']) ? -1 : 1;
                }
                return ($result < 0) ? -1 : (($result === 0) ? 0 : 1);
            });
        }
    }
}
