<?php
// -----
// Part of the "Product Options Stock" plugin by Cindy Merkin (cindy@vinosdefrutastropicales.com)
// Copyright (c) 2014-2024 Vinos de Frutas Tropicales
//
// Last Updated:  POSM v6.0.0
//
use App\Models\PluginControl;
use App\Models\PluginControlVersion;
use Zencart\PluginManager\PluginManager;

if (!defined('IS_ADMIN_FLAG') || IS_ADMIN_FLAG !== true) {
    die('Illegal Access');
}

class products_options_stock_observer extends base
{
    public bool $enabled = false;
    protected bool $debug = false;
    protected string $debug_log_file;
    protected bool $show_stock_messages;
    protected bool $extract_stock_messages;
    protected int $name_max_length;
    protected bool $use_mb;
    protected string $zcPluginDir;

    public function __construct()
    {
        // -----
        // If no admin's currently signed-in, nothing to do here.
        //
        if (empty($_SESSION['admin_id'])) {
            return;
        }

        // -----
        // Determine this zc_plugin's installed directory for use by other of the
        // plugin's modules.
        //
        $plugin_manager = new PluginManager(new PluginControl(), new PluginControlVersion());
        $this->zcPluginDir = $plugin_manager->getPluginVersionDirectory('POSM', $plugin_manager->getInstalledPlugins());

        // -----
        // Load the storefront common functions.  For the previous, unencapsulated, versions of the plugin, this
        // file was loaded by the admin's extra_functions file.
        //
        require $this->zcPluginDir('catalog') . 'includes/functions/products_options_stock_functions.php';

        // -----
        // Load the storefront common language file.
        //
        global $languageLoader;
        $languageLoader->makeCatalogArrayConstants(FILENAME_CATALOG_POS_EXTRA_DEFINITIONS, '/extra_definitions');

        $this->enabled = true;
        $this->debug = (POSM_ENABLE_DEBUG === 'true');
        $this->debug_log_file = DIR_FS_LOGS . '/posm-adm-' . $_SESSION['admin_id'] . date('-Ymd') . '.log';
        $this->show_stock_messages = (POSM_SHOW_STOCK_MESSAGES === 'Both' || POSM_SHOW_STOCK_MESSAGES === 'Admin Only');

        // -----
        // Set the indicator, used when an order is queried, to identify whether/not a product's stock-level
        // information is to be 'extracted' (with checkboxes inserted) for the current page.
        //
        zen_define_default('POSM_EXTRACT_STOCK_PAGES', FILENAME_ORDERS_INVOICE . ', ' . FILENAME_ORDERS_PACKINGSLIP);

        $extract_stock_pages = explode(',', str_replace(' ', '', POSM_EXTRACT_STOCK_PAGES));
        $current_admin_page = pathinfo($_SERVER['SCRIPT_FILENAME'], PATHINFO_FILENAME);
        $this->extract_stock_messages = in_array($current_admin_page, $extract_stock_pages);

        // -----
        // Record the current field-length for orders_products::products_name.  We'll use this when appending a
        // product's stock message to ensure that the combination of the product's name and the message
        // doesn't "overflow" that field length -- especially important for stores with MySql Strict-mode where
        // that "too big" value will result in an error.
        //
        $this->name_max_length = (int)zen_field_length(TABLE_ORDERS_PRODUCTS, 'products_name');

        $this->attach(
            $this,
            [
                /* Issued by /admin/includes/functions/general.php */
                'NOTIFIER_ADMIN_ZEN_REMOVE_ORDER',
                'NOTIFIER_ADMIN_ZEN_REMOVE_PRODUCT',

                /* Issued by the storefront /includes/classes/order.php; used by Edit Orders/zc156+ */
                'NOTIFY_ORDER_AFTER_QUERY',

                /* Issued by /options_values_manager.php */
                'OPTIONS_VALUES_MANAGER_DELETE_VALUE',

                /* Issued by /attributes_controller.php */
                'NOTIFY_ATTRIBUTE_CONTROLLER_DELETE_ATTRIBUTE',
                'NOTIFY_ATTRIBUTE_CONTROLLER_DELETE_ALL',
                'NOTIFY_ATTRIBUTE_CONTROLLER_DELETE_OPTION_NAME_VALUES',
                'NOTIFY_ATTRIBUTE_CONTROLLER_ADDITIONAL_ACTIONS_DROPDOWN_SUBMENU',

                /* Issued by /includes/functions/extra_functions/edit_orders_functions.php */
                'EDIT_ORDERS_REMOVE_PRODUCT',
                'EDIT_ORDERS_ADD_PRODUCT',

                /* Issued by /admin/category_product_listing.php */
                'NOTIFY_ADMIN_PROD_LISTING_ADD_ICON',

                /* Issued by functions_attributes.php */
                'ZEN_COPY_PRODUCTS_ATTRIBUTES_DELETE',
                'ZEN_COPY_PRODUCTS_ATTRIBUTES_COMPLETE',
                'NOTIFIER_ADMIN_ZEN_DELETE_PRODUCTS_ATTRIBUTES',

                /* Issued by /admin/options_name_manager.php */
                'OPTIONS_NAME_MANAGER_UPDATE_OPTIONS_VALUES_DELETE',

                /* Issued by /admin/category_product_listing.php */
                'NOTIFY_ADMIN_PROD_LISTING_PRODUCTS_QUERY',

                /* Issued by /admin/languages.php */
                'NOTIFY_ADMIN_LANGUAGE_INSERT',
                'NOTIFY_ADMIN_LANGUAGE_DELETE',
            ]
        );
    }

    // -----
    // Start notification handlers.
    // -----

    // -----
    // Issued by zen_remove_order just prior to its "restock" check.  Gives us the chance to manage any
    // POSM-managed products' stock levels.
    //
    // Note: Since the order could be a mixture of POSM-managed and non-POSM-managed products, we'll
    // "restock" only the POSM-managed stock levels and let the base processing update the overall
    // products' quantities.
    //
    protected function notifier_admin_zen_remove_order(&$class, string $e, array $unused, &$orders_id, &$restock)
    {
        global $db;

        if ($restock === 'on') {
            $products = $db->Execute(
                "SELECT orders_products_id
                   FROM " . TABLE_ORDERS_PRODUCTS . "
                  WHERE orders_id = " . (int)$orders_id
            );
            foreach ($products as $next_product) {
                $this->removeProductUpdateQuantity($next_product['orders_products_id'], false);
            }
         }
    }

    // -----
    // If a product is removed, make sure that its options-stock records are removed as well.
    //
    protected function notifier_admin_zen_remove_product(&$class, string $e, array $unused, &$products_id)
    {
        global $db;

        $pid = (int)$products_id;
        $db->Execute(
            "DELETE FROM " . TABLE_PRODUCTS_OPTIONS_STOCK . "
              WHERE products_id = $pid"
        );
        $db->Execute(
            "DELETE FROM " . TABLE_PRODUCTS_OPTIONS_STOCK_ATTRIBUTES . "
              WHERE products_id = $pid"
        );
    }

    // -----
    // Issued by Edit Orders when a product is being removed from the order.  We'll update any
    // POSM-managed product's stock levels.
    //
    protected function updateEditOrdersRemoveProduct(&$class, string $e, array $product)
    {
        $this->removeProductUpdateQuantity((int)$product['orders_products_id']);
    }

    // -----
    // Issued by Edit Orders when a product is being added to the order.
    //
    protected function updateEditOrdersAddProduct(&$class, string $e, array $info)
    {
        global $db;

        // -----
        // Give an observer the opportunity to indicate that the product 'addition' shouldn't be performed.
        //
        $bypass_add = false;
        $this->notify(
            'NOTIFY_POSM_EO_ADD_PRODUCT_BYPASS',
            $info,
            $bypass_add
        );
        if ($bypass_add === true) {
            $this->debug('Product addition bypassed via observer: ' . json_encode($info, JSON_PRETTY_PRINT));
            return;
        }

        $pid = (int)zen_get_prid($info['product']['id']);

        $prod_info = $db->Execute(
            "SELECT pd.products_name, p.products_model
               FROM " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS . " p
              WHERE p.products_id = $pid
                AND pd.products_id = p.products_id
                AND pd.language_id = " . $_SESSION['languages_id'] . "
              LIMIT 1"
        );

        // -----
        // Make sure that the product's name+stock-message length isn't going to overflow (and potentially
        // result in a MySQL error).  If it will, the product's name will be truncated in deference
        // to the formatted stock-message.
        //
        $products_name = zen_db_prepare_input($prod_info->fields['products_name']);
        $name_length = $this->stringLen($products_name);
        $stock_message = $this->getInStockMessage($info['orders_products_id']);
        $message_length = $this->stringLen($stock_message);
        if ($name_length + $message_length > $this->name_max_length) {
            $excess = $name_length + $message_length - $this->name_max_length;
            $products_name = $this->subString($products_name, 0, $name_length - $excess - 3) . '...';
            trigger_error("Product #$pid, name truncated to '$products_name', due to database size limitation ($name_length + $message_length > {$this->name_max_length}).", E_USER_WARNING);
        }
        $products_name = $db->prepareInput($products_name . $stock_message);
        $db->Execute(
            "UPDATE " . TABLE_ORDERS_PRODUCTS . "
                SET products_name = '$products_name'
              WHERE orders_products_id = " . $info['orders_products_id'] . "
              LIMIT 1"
        );

        if (!(is_pos_product($pid) && isset($info['product']['attributes']))) {
            $pos_record = false;
        } else {
            $attributes_array = $this->ordersProductsAttributesArray((int)$info['orders_products_id']);
            $hash = generate_pos_option_hash($pid, $attributes_array);
            $pos_record = $db->Execute(
                "SELECT pos_id, products_quantity, pos_model
                   FROM " . TABLE_PRODUCTS_OPTIONS_STOCK . "
                  WHERE products_id = $pid
                    AND pos_hash = '$hash'
                  LIMIT 1"
            );
        }

        // -----
        // If the current product does not have its options' stock managed ... simply make sure that the product quantity doesn't go negative.
        //
        if ($pos_record === false) {
            $quantity_record = $db->Execute(
                "SELECT products_quantity
                   FROM " . TABLE_PRODUCTS . "
                  WHERE products_id = $pid
                  LIMIT 1"
            );
            if ($quantity_record->fields['products_quantity'] < 0) {
                $db->Execute(
                    "UPDATE " . TABLE_PRODUCTS . "
                        SET products_quantity = 0
                      WHERE products_id = $pid
                      LIMIT 1"
                );
            }
            $this->debug("edit_orders_add_product:  No POSM record ($pid), quantity: " . $quantity_record->fields['products_quantity']);
            return;
        }

        // -----
        // Give an observer the chance to 'opt-out' of the product's "managed" quantity update.
        //
        $bypass_managed_stock_update = false;
        $this->notify(
            'NOTIFY_POSM_EO_PRODUCT_ADD_STOCK_UPDATE',
            [
                'pos_record' => $pos_record,
                'product' => $info
            ],
            $bypass_managed_stock_update
        );

        // -----
        // If the current product's option-combination is not being stock-managed or an observer has indicated that the managed
        // stock quantities shouldn't be updated, add its quantity back into the product's overall quantity and ensure that
        // the product's model is set to the base-product's value.
        //
        if ($pos_record->EOF || $bypass_managed_stock_update === true) {
            $db->Execute(
                "UPDATE " . TABLE_PRODUCTS . "
                    SET products_quantity = products_quantity + " . $info['product']['qty'] . "
                  WHERE products_id = $pid
                  LIMIT 1"
            );
            $this->debug("edit_orders_add_product ($pid): Unmanaged variant, attributes:\n" . json_encode($attributes_array, JSON_PRETTY_PRINT));

            $db->Execute(
                "UPDATE " . TABLE_ORDERS_PRODUCTS . "
                    SET products_model = '" . zen_db_input($prod_info->fields['products_model']) . "'
                  WHERE orders_products_id = " . (int)$info['orders_products_id'] . "
                  LIMIT 1"
            );
            return;
        }

        // -----
        // Otherwise, the current product's option-combination IS being stock-managed.  Subtract from the option-specific stock -- the overall product's stock has
        // previously been reduced by the order class' processing.  Check that the overall product's stock value hasn't gone negative and set it back to 0 if it has.
        //
        // If there's a non-blank POSM model-number defined for the current option-combination, update the product's model number in the order.
        //
        $new_option_quantity = $pos_record->fields['products_quantity'] - $info['product']['qty'];
        if ($new_option_quantity < 0) {
            $new_option_quantity = 0;
        }
        $db->Execute(
            "UPDATE " . TABLE_PRODUCTS_OPTIONS_STOCK . "
                SET products_quantity = $new_option_quantity
              WHERE pos_id = " . $pos_record->fields['pos_id'] . "
              LIMIT 1"
        );
        $this->adjustOverallProductQuantity($pid);
        $this->debug("edit_orders_add_product ($pid): Managed variant, setting quantity to $new_option_quantity, attributes_array: \n" . json_encode($attributes_array, JSON_PRETTY_PRINT));

        if ($pos_record->fields['pos_model'] != '') {
            $options_model_num = $pos_record->fields['pos_model'];
        } else {
            $options_model_num = $prod_info->fields['products_model'];
        }
        $options_model_num = zen_db_input($options_model_num);
        $db->Execute(
            "UPDATE " . TABLE_ORDERS_PRODUCTS . "
                SET products_model = '$options_model_num'
              WHERE orders_products_id = " . (int)$info['orders_products_id'] . "
              LIMIT 1"
        );
    }

    // -----
    // If an option value has been removed, remove all options-stock records associated with that value.
    //
    protected function updateOptionsValuesManagerDeleteValue(&$class, string $e, array $info)
    {
        global $db, $messageStack;

        $option_values = $db->Execute(
            "SELECT pos_id, products_id
               FROM " . TABLE_PRODUCTS_OPTIONS_STOCK_ATTRIBUTES . "
              WHERE options_values_id = " . (int)$info['value_id']
        );
        if (!$option_values->EOF) {
            $messageStack->add_session(sprintf(CAUTION_REMOVING_OPTIONS_STOCK, $option_values->RecordCount()), 'caution');
            $affected_products = [];
            foreach ($option_values as $next_option) {
                $affected_products[] = $next_option['products_id'];
                $db->Execute(
                    "DELETE FROM " . TABLE_PRODUCTS_OPTIONS_STOCK . "
                      WHERE pos_id = " . $next_option['pos_id']
                );
                $db->Execute(
                    "DELETE FROM " . TABLE_PRODUCTS_OPTIONS_STOCK_ATTRIBUTES . "
                      WHERE pos_id = " . $next_option['pos_id']
                );
            }
            $affected_products = array_unique($affected_products);
            foreach ($affected_products as $next_products_id) {
                posm_update_base_product_quantity($next_products_id);
            }
        }
    }

    // -----
    // If a single attribute has been removed, make sure that any options-stock records associated with that attribute are removed also.
    //
    protected function notify_attribute_controller_delete_attribute(&$class, string $e, array $info)
    {
        global $db, $messageStack;

        $attribute_info = $db->Execute(
            "SELECT products_id, options_id, options_values_id
               FROM " . TABLE_PRODUCTS_ATTRIBUTES . "
              WHERE products_attributes_id = " . (int)$info['attribute_id'] . "
              LIMIT 1"
        );
        if (!$attribute_info->EOF) {
            $pos_attribute_info = $db->Execute(
                "SELECT pos_id FROM " . TABLE_PRODUCTS_OPTIONS_STOCK_ATTRIBUTES . "
                  WHERE products_id = " . $attribute_info->fields['products_id'] . "
                    AND options_id = " . $attribute_info->fields['options_id'] . "
                    AND options_values_id = " . $attribute_info->fields['options_values_id']
            );
            if (!$pos_attribute_info->EOF) {
                $messageStack->add_session(sprintf(CAUTION_REMOVING_OPTIONS_STOCK, $pos_attribute_info->RecordCount()), 'caution');
                foreach ($pos_attribute_info as $next_attr) {
                    $db->Execute(
                        "DELETE FROM " . TABLE_PRODUCTS_OPTIONS_STOCK . "
                          WHERE pos_id = " . $next_attr['pos_id'] . "
                          LIMIT 1"
                    );
                    $db->Execute(
                        "DELETE FROM " . TABLE_PRODUCTS_OPTIONS_STOCK_ATTRIBUTES . "
                          WHERE pos_id = " . $next_attr['pos_id']
                    );
                }
                posm_update_base_product_quantity($attribute_info->fields['products_id']);
            }
        }
    }

    // -----
    // If all attributes for a product have been removed, make sure that any options-stock record associated with those attributes are removed also.
    //
    protected function notify_attribute_controller_delete_all(&$class, string $e, array $info)
    {
        $this->removeProductPosmOptions((int)$info['pID']);
    }

    // -----
    // If all option values for a specific option for a specific product are removed, make sure that the options-stock records are removed as well.
    //
    protected function notify_attribute_controller_delete_option_name_values(&$class, string $e, array $info)
    {
        global $db, $messageStack;

        $pos_info = $db->Execute(
            "SELECT pos_id
               FROM " . TABLE_PRODUCTS_OPTIONS_STOCK_ATTRIBUTES . "
              WHERE products_id = " . (int)$info['pID'] . "
                AND options_id = " . (int)$info['options_id']
         );
        if (!$pos_info->EOF) {
            $messageStack->add_session(sprintf(CAUTION_REMOVING_OPTIONS_STOCK, $pos_info->RecordCount()), 'caution');
            foreach ($pos_info as $next_option) {
                $db->Execute(
                    "DELETE FROM " . TABLE_PRODUCTS_OPTIONS_STOCK . "
                      WHERE pos_id = " . $next_option['pos_id'] . "
                      LIMIT 1"
                );
                $db->Execute(
                    "DELETE FROM " . TABLE_PRODUCTS_OPTIONS_STOCK_ATTRIBUTES . "
                      WHERE pos_id = " . $next_option['pos_id']
                );
            }
            posm_update_base_product_quantity((int)$info['pID']);
        }
    }

    // -----
    // From the "Attributes Controller", to insert more actions into the upper "Additional
    // Actions" dropdown.  If the selected product currently has attributes (non-readonly), then
    // add a link to the Options' Stock Manager.
    //
    protected function notify_attribute_controller_additional_actions_dropdown_submenu(&$class, string $e, $unused1, &$unused2, &$products_filter, &$current_category_id, array &$additional_actions)
    {
        // -----
        // Using 'false' value to exclude read-only attributes from the check!
        //
        if (zen_has_product_attributes($products_filter, false) === true) {
            $additional_actions[] = [
                'text' => BOX_CONFIGURATION_PRODUCTS_OPTIONS_STOCK,
                'link' => zen_href_link(FILENAME_PRODUCTS_OPTIONS_STOCK, 'pID=' . $products_filter . '&category_id=' . $current_category_id),
            ];
        }
    }

    // -----
    // Issued at the end of the admin order class' query function.  If in-/out-of-stock messages aren't to
    // be displayed in the admin, strip the information.
    //
    // Otherwise, check to see which admin-page has created the order-object.  If created for either
    // the invoice or packingslip pages, modify the product's stock comments to include a checkbox
    // allowing the admin processing to indicate that the product is/isn't included.
    //
    protected function notify_order_after_query(\order &$order, string $e)
    {
        foreach ($order->products as &$current_product) {
            if (!$this->show_stock_messages) {
                $current_product['name'] = $this->stripStockMessage($current_product['name']);
            } elseif ($this->extract_stock_messages) {
                $current_product['name'] = $this->extractStockMessage($current_product['name']);
            }
        }
    }

    // -----
    // Issued by /admin/category_product_listing.php.
    //
    protected function notify_admin_prod_listing_add_icon(&$class, string $e, array $product, string &$additional_icons)
    {
        // -----
        // If the product isn't POSM-managed, add a transparent icon to preserve spacing, in case
        // another observer is also adding icons.
        //
        if (is_pos_product($product['products_id']) === false) {
            $additional_icons .= '<i class="fa fa-square fa-lg text-hide"></i>';
            return;
        }

        // -----
        // Otherwise, add an icon with a link to the product's options-stock configuration.  Note that
        // for a product-listing search, the cPath isn't present ... so need to grab the product's
        // master-category id for use in the link.
        //
        if (empty($_GET['cPath'])) {
            $category_id = zen_get_products_category_id($product['products_id']);
        } else {
            $categories = explode('_', $_GET['cPath']);
            $category_id = end($categories);
        }
        $additional_icons .=
            '<a href="' . zen_href_link(FILENAME_PRODUCTS_OPTIONS_STOCK, 'pID=' . $product['products_id']) . '&category_id=' . $category_id . '">' .
                '<i class="fa fa-square fa-lg align-middle text-primary" aria-hidden="true" title="' . POS_ALT_PRODUCT_HAS_OPTIONS_STOCK . '"></i>' .
            '</a>';
    }

    // -----
    // Issued by /includes/modules/copy_product_confirm.php during a product duplication. Note that the
    // posted values need to be checked to ensure that the admin wants to copy the attribute-related information, too.
    //
    protected function notify_modules_copy_to_confirm_duplicate(&$class, string $e, array $info)
    {
        if (!empty($_POST['copy_attributes']) && $_POST['copy_attributes'] === 'copy_attributes_yes') {
            $this->duplicatePosmProduct($info['products_id'], $info['dup_products_id']);
        }
    }

    // -----
    // Issued by zen_copy_products_attributes (in functions_attributes.php) when an attribute-copy operation should
    // begin by removing all existing attributes from the target product.  We'll remove all managed options
    // for that product, too.
    //
    protected function updateZenCopyProductsAttributesDelete(&$class, string $e, $products_id)
    {
        $this->removeProductPosmOptions((int)$products_id);
    }

    // -----
    // Issued by zen_copy_products_attributes (in functions_attributes.php) when an attribute-copying operation is
    // complete.  If the 'source' product was POSM-managed, copy the managed options to the 'target'
    // product, too.
    //
    protected function updateZenCopyProductsAttributesComplete(&$class, string $e, array $info)
    {
        $this->duplicatePosmProduct((int)$info['from'], (int)$info['to']);
    }

    // -----
    // Issued by zen_delete_products_attributes (in functions_attributes.php), indicating that all attributes
    // are being removed for the specified product.  We'll remove all managed options for the product, too.
    //
    protected function notifier_admin_zen_delete_products_attributes(&$class, string $e, $unused, &$products_id)
    {
        $this->removeProductPosmOptions((int)$products_id);
    }

    // -----
    // Issued by /admin/options_name_manager.php during the loop deleting all attributes
    // from products where a specific option_id is requested.
    //
    protected function updateOptionsNameManagerUpdateOptionsValuesDelete(&$class, string $e, array $info)
    {
        $this->removeProductPosmOptionsValues((int)$info['products_id'], $_POST['options_id']);
    }

    // -----
    // Issued by /admin/category_product_listing.php; modified for use in zc158a and later.  Enables POSM to
    // add variants' models to the products-listing's search.
    //
    protected function notify_admin_prod_listing_products_query(&$class, string $e, $unused, string &$extra_select, string &$extra_from, string &$extra_joins, string &$extra_ands, string &$order_by, array &$extra_search_fields)
    {
        $extra_joins .= ' LEFT JOIN ' . TABLE_PRODUCTS_OPTIONS_STOCK . ' posm ON posm.products_id = p.products_id';
        $extra_search_fields[] = 'posm.pos_model';
    }

    // -----
    // Issued by /admin/languages.php; when a new language is added.
    // Enables POSM to add Back-ordered label for this language in table products_options_stock_names.
    //
    protected function notify_admin_language_insert(&$class, string $e, int &$insert_id)
    {
        global $db;

        // create additional products option stock names records
        $products_option_stock_names = $db->Execute(
            "SELECT pos_name_id, pos_name
               FROM " . TABLE_PRODUCTS_OPTIONS_STOCK_NAMES . "
              WHERE language_id = " . (int)$_SESSION['languages_id']
        );

        foreach ($products_option_stock_names as $option_stock_name) {
          $db->Execute(
              "INSERT IGNORE INTO " . TABLE_PRODUCTS_OPTIONS_STOCK_NAMES . "
                  (pos_name_id, language_id, pos_name)
               VALUES
                  (" . $option_stock_name['pos_name_id'] . ", $insert_id, '" . zen_db_input($option_stock_name['pos_name']) . "')"
          );
        }
    }

    // -----
    // Issued by /admin/languages.php; when a language is deleted.
    // Enables POSM to delete Back-ordered label for this language in table products_options_stock_names.
    //
    protected function notify_admin_language_delete(&$class, string $e, int &$lID)
    {
        global $db;

        $db->Execute("DELETE FROM " . TABLE_PRODUCTS_OPTIONS_STOCK_NAMES . " WHERE language_id = $lID");
    }




    // -----
    // End notification handlers.
    // -----

    // -----
    // Return the plugin's currently-installed zc_plugin directory name, either the 'admin' (default)
    // or 'catalog'.
    //
    public function zcPluginDir($location = 'admin')
    {
        return $this->zcPluginDir . $location . '/';
    }

    // -----
    // This method duplicates the managed options from one product to another.  This can be
    // invoked through two paths:
    //
    // 1) A click of the (C) icon on a products' listing, where the attributes are also to be copied.
    // 2) A click of the (A) icon on a products' listing, with further click on the option to copy
    //    the product's attributes to another product.
    //
    // Note: If the target product already has a managed option copied from the source, the details
    // of the target product's options-stock record is not modified (i.e. no change to model-number
    // or quantity).
    //
    protected function duplicatePosmProduct(int $source_pid, int $target_pid)
    {
        global $db,
               $messageStack;

        // -----
        // Gather the managed-option 'base' information for the original/source product.
        //
        $source = $db->Execute(
            "SELECT *
               FROM " . TABLE_PRODUCTS_OPTIONS_STOCK . "
              WHERE products_id = $source_pid
              ORDER BY pos_id ASC"
        );

        // -----
        // Loop through the 'source' product's managed options, copying each to the 'target' and
        // keeping track of the number copied (used to report via message-stack upon completion).
        //
        $options_copied = 0;
        foreach ($source as $base_sql) {
            // -----
            // Prepare the 'basics' of the base POSM record for the copy.
            //
            $pos_id = $base_sql['pos_id'];
            unset($base_sql['pos_id']);
            $base_sql['products_id'] = $target_pid;
            $base_sql['last_modified'] = 'now()';

            // -----
            // Gather the 'source' product's managed options for the copy operation.
            //
            $source_options = $db->Execute(
                "SELECT *
                   FROM " . TABLE_PRODUCTS_OPTIONS_STOCK_ATTRIBUTES . "
                  WHERE products_id = $source_pid
                    AND pos_id = $pos_id
                  ORDER BY pos_attribute_id ASC"
            );
            $options = [];
            foreach ($source_options as $next_option) {
                $options[$next_option['options_id']] = $next_option['options_values_id'];
            }

            // -----
            // If the source product's managed option returned information, copy that option.
            //
            if (!empty($options)) {
                $base_sql['pos_hash'] = generate_pos_option_hash($target_pid, $options);
                $check = $db->Execute(
                    "SELECT *
                       FROM " . TABLE_PRODUCTS_OPTIONS_STOCK . "
                      WHERE products_id = $target_pid
                        AND pos_hash = '{$base_sql['pos_hash']}'
                      LIMIT 1"
                );
                if ($check->EOF) {
                    $options_copied++;

                    zen_db_perform(TABLE_PRODUCTS_OPTIONS_STOCK, $base_sql);

                    $option_sql = [
                        'pos_id' => zen_db_insert_id(),
                        'products_id' => $target_pid,
                    ];

                    foreach ($options as $key => $value) {
                        $option_sql['options_id'] = $key;
                        $option_sql['options_values_id'] = $value;
                        zen_db_perform(TABLE_PRODUCTS_OPTIONS_STOCK_ATTRIBUTES, $option_sql);
                    }
                }
            }
        }

        // -----
        // If at least one option was copied, inform the admin via message and re-calculate the
        // product's base quantity-available.
        //
        if ($options_copied > 0) {
            $messageStack->add_session(sprintf(SUCCESS_COPYING_OPTIONS_STOCK, $options_copied), 'success');
            $db->Execute(
                "UPDATE " . TABLE_PRODUCTS . " p
                    INNER JOIN (
                        SELECT products_id, SUM(products_quantity) AS total
                          FROM " . TABLE_PRODUCTS_OPTIONS_STOCK . "
                         GROUP BY products_id
                    ) posm ON posm.products_id = p.products_id
                    SET p.products_quantity = posm.total
                  WHERE p.products_id = $target_pid"
            );
        }
    }

    // -----
    // An internal method that removes (with message) **all** POSM-managed options from a
    // given product.
    //
    protected function removeProductPosmOptions(int $pid)
    {
        global $db, $messageStack;

        $this->debug("Attempting to remove all POSM options from product ID#$pid");
        $pos_info = $db->Execute(
            "SELECT count(*) as total
               FROM " . TABLE_PRODUCTS_OPTIONS_STOCK . "
              WHERE products_id = $pid"
        );
        if ($pos_info->fields['total'] != 0) {
            $messageStack->add_session(sprintf(CAUTION_REMOVING_OPTIONS_STOCK, $pos_info->fields['total']), 'caution');
            $db->Execute(
                "DELETE FROM " . TABLE_PRODUCTS_OPTIONS_STOCK . "
                  WHERE products_id = $pid"
            );
            $db->Execute(
                "DELETE FROM " . TABLE_PRODUCTS_OPTIONS_STOCK_ATTRIBUTES . "
                  WHERE products_id = $pid"
            );
            posm_update_base_product_quantity($pid);

            $this->debug('Deleted ' . $pos_info->fields['total'] . ' records for pID#' . $pid . '.');
        }
    }

    // -----
    // Called during the Options' Names' Manager's handling when options' values are being removed
    // from one or more products.  Remove any POSM products' values and, if there are no remaining
    // options for the product, remove the base product's POSM entry as well.
    //
    protected function removeProductPosmOptionsValues(int $pid, int $options_id)
    {
        global $db;

        // -----
        // First, see if the specified product currently has managed options associated with the
        // specified options_id.
        //
        $pos_entries = $db->Execute(
            "SELECT pos_id
               FROM " . TABLE_PRODUCTS_OPTIONS_STOCK_ATTRIBUTES . "
              WHERE products_id = $pid
                AND options_id = $options_id
              LIMIT 1"
        );

        // -----
        // The option's not currently being managed for the given product; nothing else to do.
        //
        if ($pos_entries->EOF) {
            return;
        }

        // -----
        // Remove all POSM-managed entries for the specified product and option.
        //
        $db->Execute(
            "DELETE FROM " . TABLE_PRODUCTS_OPTIONS_STOCK_ATTRIBUTES . "
              WHERE products_id = $pid
                AND options_id = $options_id"
        );

        // -----
        // If the product no longer has any managed options, the product itself is no
        // longer managed either.
        //
        // Otherwise, the product is still managed, but with reduced options.  Each option-stock
        // pid's hash needs to be recalculated.
        //
        $product_check = $db->Execute(
            "SELECT pos_id, options_id, options_values_id
               FROM " . TABLE_PRODUCTS_OPTIONS_STOCK_ATTRIBUTES . "
              WHERE products_id = $pid
              ORDER BY pos_id ASC"
        );
        if ($product_check->EOF) {
            $db->Execute(
                "DELETE FROM " . TABLE_PRODUCTS_OPTIONS_STOCK . "
                  WHERE products_id = $pid"
            );
        } else {
            $products_options_array = [];
            foreach ($product_check as $next_entry) {
                $pos_id = $next_entry['pos_id'];
                if (!isset($products_options_array[$pos_id])) {
                    $products_options_array[$pos_id] = [];
                }
                $products_options_array[$pos_id][] = [$next_entry['options_id'] => $next_entry['options_values_id']];
            }
            unset($product_check, $next_entry);
            foreach ($products_options_array as $pos_id => $pos_attributes) {
                $new_hash = generate_pos_option_hash($pid, $pos_attributes);
                $db->Execute(
                    "UPDATE " . TABLE_PRODUCTS_OPTIONS_STOCK . "
                        SET pos_hash = '$new_hash'
                      WHERE pos_id = $pos_id
                      LIMIT 1"
                );
            }
        }
        posm_update_base_product_quantity($pid);
    }

    protected function adjustOverallProductQuantity(int $products_id)
    {
        $products_quantity = posm_update_base_product_quantity($products_id);
        if ($products_quantity === null) {
            $products_quantity = '-not managed-';
        }
        return $products_quantity;
    }

    // -----
    // Called when a product is being removed from an order (either via Customers->Orders or Customers->Edit Orders).
    // When called from the built-in processing, the option exists to conditionally update the products' quantities
    // and the base processing will handle the update of the overall product's quantities.
    //
    protected function removeProductUpdateQuantity($orders_products_id, bool $update_overall_product = true)
    {
        global $db;

        // -----
        // Give an observer the opportunity to bypass the stock-quantity modifications on a product's removal
        // from an order.
        //
        $bypass_quantity_updates = false;
        $this->notify(
            'NOTIFY_POSM_EO_REMOVE_PRODUCT_QUANTITY_BYPASS',
            $orders_products_id,
            $bypass_quantity_updates
        );
        if ($bypass_quantity_updates !== false) {
            $this->debug('removeProductUpdateQuantity, bypassed by observer request.');
            return;
        }

        $product_info = $db->Execute(
            "SELECT products_id, products_name, products_quantity
               FROM " . TABLE_ORDERS_PRODUCTS . "
              WHERE orders_products_id = $orders_products_id
              LIMIT 1"
        );
        $products_id = $product_info->fields['products_id'];
        $trace_info = '';
        if (is_pos_product($products_id)) {
            $trace_info = ', is_pos_product';
            $attributes_array = $this->ordersProductsAttributesArray($orders_products_id);
            if (count($attributes_array) > 0) {
                $trace_info .= ', has attributes';
                if (preg_match('/.*\[(\d*).*, (\d*).*\]$/', $product_info->fields['products_name'], $matches)) {
                    $options_stock = $matches[1];  // Mixed in stock/to be made, gather quantity in stock
                    $trace_info .= ', mixed';
                } elseif (strpos($product_info->fields['products_name'], PRODUCTS_OPTIONS_STOCK_IN_STOCK) !== false) {
                    $options_stock = $product_info->fields['products_quantity'];  // All products in stock
                    $trace_info .= ", in stock, add back ($options_stock)";
                } else {
                    $options_stock = 0;  // All products to be made
                    $trace_info .= ', to be made';
                }
                $trace_info .= " ($options_stock)";

                $option_hash = generate_pos_option_hash($products_id, $attributes_array);
                $db->Execute(
                    "UPDATE " . TABLE_PRODUCTS_OPTIONS_STOCK . "
                        SET products_quantity = products_quantity + $options_stock, last_modified = now()
                      WHERE products_id = $products_id
                        AND pos_hash = '$option_hash'
                      LIMIT 1"
                );

                if ($update_overall_product === true) {
                    $trace_info .= ', product quantity updated to ' . $this->adjustOverallProductQuantity($products_id);
                }
            }
            posm_update_base_product_quantity($products_id);
        }
        $this->debug("removeProductUpdateQuantity, products ID $products_id" . $trace_info);
    }

    protected function ordersProductsAttributesArray($orders_products_id): array
    {
        global $db;

        $attributes_array = [];
        $attributes = $db->Execute(
            "SELECT products_options_id, products_options_values_id
               FROM " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . "
              WHERE orders_products_id = $orders_products_id"
        );
        foreach ($attributes as $next_attr) {
            $attributes_array[$next_attr['products_options_id']] = $next_attr['products_options_values_id'];
        }
        return $attributes_array;
    }

    // -----
    // Return the in-/out-of-stock message for the specified product; used by edit_orders processing.
    //
    // Note: As of v4.1.4, the method is available publically, supporting store-specific integrations.
    //
    public function getInStockMessage($orders_products_id): string
    {
        global $db;

        $op_info = $db->Execute(
            "SELECT products_id, products_quantity
               FROM " . TABLE_ORDERS_PRODUCTS . "
              WHERE orders_products_id = $orders_products_id
              LIMIT 1"
        );
        $pid = (int)$op_info->fields['products_id'];
        $ordered_quantity = $op_info->fields['products_quantity'];

        $prod_info = $db->Execute(
            "SELECT products_type, products_quantity
               FROM " . TABLE_PRODUCTS . "
              WHERE products_id = $pid
              LIMIT 1"
        );

        $attributes_array = $this->ordersProductsAttributesArray($orders_products_id);

        if (!(is_pos_product($pid) && count($attributes_array) > 0)) {
            $check = false;
            if ($prod_info->fields['products_quantity'] < 0) {
                $prod_info->fields['products_quantity'] = 0;
            }
        } else {
            $hash = generate_pos_option_hash($op_info->fields['products_id'], $attributes_array);
            $check = $db->Execute(
                "SELECT pos_id, products_quantity, pos_date, pos_name_id
                   FROM " . TABLE_PRODUCTS_OPTIONS_STOCK . "
                  WHERE products_id = $pid
                    AND pos_hash = '$hash'
                  LIMIT 1"
            );
        }

        // -----
        // Give a watching observer the opportunity to inject its own 'stock message' or, for stock-managed
        // products, to force the base POSM's out-of-stock message for the current product.
        //
        $msg_text = '';
        $msg_text_override = '';
        $force_out_of_stock_message = false;
        $this->notify(
            'NOTIFY_POSM_GET_IN_STOCK_MESSAGE_BYPASS',
            [
                'op_info' => $op_info,
                'prod_info' => $prod_info,
                'pos_info' => $check,
            ],
            $msg_text_override,
            $force_out_of_stock_message
        );

        if (!$prod_info->EOF && $msg_text_override !== '') {
            $msg_text = $msg_text_override;
            $this->debug("getInStockMessage($pid), stock message overridden ($msg_text_override).");
        } elseif ($check === false) {
            $quantity = $prod_info->fields['products_quantity'];
            if (POSM_SHOW_UNMANAGED_OPTIONS_STATUS === 'true') {
                if ($quantity >= $ordered_quantity) {
                    $msg_text = PRODUCTS_OPTIONS_STOCK_IN_STOCK;
                } elseif ($quantity == 0) {
                    $msg_text = PRODUCTS_OPTIONS_STOCK_NOT_IN_STOCK;
                } else {
                    $msg_text = sprintf(PRODUCTS_OPTIONS_STOCK_MIXED, $quantity, PRODUCTS_OPTIONS_STOCK_IN_STOCK, $ordered_quantity - $quantity, PRODUCTS_OPTIONS_STOCK_NOT_IN_STOCK);
                }
          }
          $this->debug("getInStockMessage ($orders_products_id), not POSM product. quantity = $quantity, message = $msg_text");
        } else {
            if ($check->EOF || $force_out_of_stock_message) {
                $this->debug("getInStockMessage ($orders_products_id), not POSM variant or overridden ($force_out_of_stock_message).");
                if (POSM_SHOW_UNMANAGED_OPTIONS_STATUS === 'true' || $force_out_of_stock_message !== false) {
                    $msg_text = PRODUCTS_OPTIONS_STOCK_NOT_IN_STOCK;
                }
            } else {
                $options_quantity = $check->fields['products_quantity'];
                if ($options_quantity >= $ordered_quantity) {
                    $msg_text = PRODUCTS_OPTIONS_STOCK_IN_STOCK;
                } else {
                    $msg_text = str_replace('[date]', $check->fields['pos_date'], get_pos_oos_name($check->fields['pos_name_id'], $_SESSION['languages_id']));
                    if ($options_quantity != 0) {
                        $msg_text = sprintf(PRODUCTS_OPTIONS_STOCK_MIXED, $options_quantity, PRODUCTS_OPTIONS_STOCK_IN_STOCK, $ordered_quantity - $options_quantity, $msg_text);
                    }
                }
                $this->debug("getInStockMessage ($orders_products_id), is POSM product. ordered quantity = $ordered_quantity, quantity = $options_quantity, message = $msg_text");
            }
        }
        return ($msg_text == '') ? '' : sprintf(PRODUCTS_OPTIONS_STOCK_STOCK_TEXT, $msg_text);
    }

    // -----
    // Strips the stock message, presumed to be in the form '[stock message]', from
    // the end of a product's name.
    //
    public function stripStockMessage($products_name): string
    {
        return preg_replace('/\[.*\]/', '', (string)$products_name);
    }

    // -----
    // "Extracts" a product's stock message, adding checkbox fields to the name for display
    // in the (by default) admin's invoice and packingslip pages' display.
    //
    // Note: For POSM versions prior to 4.0.0, this processing was provided by the "extra"
    // function pos_extract_stock_type.  Keeping this function in the public namespace to allow
    // that function to continue to operate, just in case someone's been using it for other
    // pages in their admin.
    //
    public function extractStockMessage($products_name): string
    {
        if ($this->show_stock_messages) {
            if (preg_match('/(.*)\[(.*)\]$/', $products_name, $matches)) {
                $products_name = $matches[1];
                $products_name .= '<br>' . zen_draw_checkbox_field('check');
                if (!empty($matches[2])) {
                    $products_name .= (' ' . str_replace(',', ' ' . zen_draw_checkbox_field('check2'), $matches[2]));
                }
            }
        }
        return $products_name;
    }

    // -----
    // Implements a call to either the PHP strlen or mb_strlen function, using mb_strlen
    // if available and the site's current CHARSET is valid.
    //
    public function stringLen($string)
    {
        // -----
        // Return the length of the supplied string, using either strlen or mb_strlen, as determined
        // by the protected method.
        //
        $this->initMbStrings();
        return ($this->use_mb === true) ? mb_strlen((string)$string, CHARSET) : strlen((string)$string);
    }
    public function subString($string, $start, $length = null)
    {
        // -----
        // Return the substring requested, using either substr or mb_substr, as determined by the
        // protected method.
        //
        $this->initMbStrings();
        return ($this->use_mb === true) ? mb_substr((string)$string, $start, $length, CHARSET) : substr((string)$string, $start, $length);
    }
    public function stringPos($string, $needle, $offset = 0)
    {
        // -----
        // Return the position requested, using either strpos or mb_strpos, as determined by the
        // protected method.
        //
        $this->initMbStrings();
        return ($this->use_mb === true) ? mb_strpos((string)$string, (string)$needle, (int)$offset, CHARSET) : strpos((string)$string, (string)$needle, (int)$offset);
    }
    protected function initMbStrings()
    {
        // -----
        // If haven't yet determined whether to use mb_strlen, check to see that the
        // PHP function mb_encoding_aliases exists and, if so, whether the site's defined
        // CHARSET is valid.  If both are true, then we'll use mb_strlen instead of strlen.
        //
        // Note:  Overriding the error-output on mb_encoding_aliases since it will throw a PHP
        // warning if the CHARSET supplied is not supported.
        //
        if (!isset($this->use_mb)) {
            $this->use_mb = false;
            if (function_exists('mb_encoding_aliases')) {
                if (@mb_encoding_aliases(CHARSET) !== false) {
                    $this->use_mb = true;
                }
            }
        }
    }

    protected function debug($message)
    {
        $this->debug_message("posmAdminObserver: $message");
    }

    public function debug_message($message)
    {
        if ($this->debug) {
            error_log(date('Y-m-d H:i:s') . ": $message\n", 3, $this->debug_log_file);
        }
    }
}
