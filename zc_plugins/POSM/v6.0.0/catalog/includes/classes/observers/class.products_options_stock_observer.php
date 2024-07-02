<?php
// -----
// Part of the "Product Options Stock Manager" plugin by Cindy Merkin (cindy@vinosdefrutastropicales.com)
// Copyright (c) 2014-2024 Vinos de Frutas Tropicales
//
// Last updated: POSM 5.0.0
//
use App\Models\PluginControl;
use App\Models\PluginControlVersion;
use Zencart\PluginManager\PluginManager;

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

class products_options_stock_observer extends base
{
    // -----
    // Defining class variables; required for PHP 8.2+.
    //
    public bool $enabled;
    public bool $show_stock_messages;

    protected bool $debug;
    protected string $debug_log_file;
    protected int $name_max_length;
    protected string $stock_message;
    protected string $products_name_saved;
    protected bool $use_mb;
    protected string $zcPluginDir;

    public function __construct()
    {
        // -----
        // Ensure that the configuration settings are present before continuing; perform
        // a 'quick return' if the overall POSM enable flag isn't defined or is disabled.
        //
        // Since the debug method might be requested even without enabling, make sure that
        // the value's initialized to prevent PHP notices!
        //
        $this->debug = false;
        $this->enabled = (defined('POSM_ENABLE') && POSM_ENABLE === 'true');
        if ($this->enabled === false) {
            return;
        }

        $this->debug = (POSM_ENABLE_DEBUG === 'true');
        $this->debug_log_file = DIR_FS_LOGS . '/myDEBUG-POSM-' . time() . '-' . mt_rand(1000,999999) . '.log';

        $this->show_stock_messages = (POSM_SHOW_STOCK_MESSAGES === 'Both' || POSM_SHOW_STOCK_MESSAGES === 'Store Only');

        $this->debugMessage('STOCK_CHECK: ' . STOCK_CHECK . ', STOCK_ALLOW_CHECKOUT: ' . STOCK_ALLOW_CHECKOUT . ', STOCK_LIMITED: ' . STOCK_LIMITED);

        // -----
        // Determine this zc_plugin's installed directory for use by other of the
        // plugin's modules.
        //
        $plugin_manager = new PluginManager(new PluginControl(), new PluginControlVersion());
        $this->zcPluginDir = str_replace(
            DIR_FS_CATALOG,
            '',
            $plugin_manager->getPluginVersionDirectory('POSM', $plugin_manager->getInstalledPlugins()) . 'catalog/'
        );

        // -----
        // Load the storefront common functions.  For the previous, unencapsulted, versions of the plugin, this
        // file was loaded by the admin's extra_functions file.
        //
        require $this->getZcPluginDir() . DIR_WS_FUNCTIONS . 'products_options_stock_functions.php';

        $this->attach(
            $this,
            [
                /* From /includes/classes/order.php */
               'NOTIFY_ORDER_DURING_CREATE_ADDED_PRODUCT_LINE_ITEM',
               'NOTIFY_ORDER_PROCESSING_STOCK_DECREMENT_INIT',
               'NOTIFY_ORDER_PROCESSING_STOCK_DECREMENT_BEGIN',
               'NOTIFY_ORDER_PROCESSING_STOCK_DECREMENT_END',
               'NOTIFY_ORDER_CART_FINISHED',

               /* From /includes/functions/functions_lookups.php */
               'ZEN_GET_PRODUCTS_STOCK',

               /* From /includes/classes/class.search.php */
               'NOTIFY_SEARCH_FROM_STRING',

               /* From /includes/classes/ajax/zcAjaxBootstrapSearch.php */
               'NOTIFY_AJAX_BOOTSTRAP_SEARCH_CLAUSES',

               /* From /includes/functions/functions_search.php */
               'NOTIFY_BUILD_KEYWORD_SEARCH',

               /* From various /includes/modules/pages/{page_name}/header_php.php */
               'NOTIFY_HEADER_END_ACCOUNT_HISTORY_INFO',
               'NOTIFY_HEADER_END_CHECKOUT_CONFIRMATION',
               'NOTIFY_HEADER_END_CHECKOUT_ONE',
               'NOTIFY_HEADER_END_CHECKOUT_ONE_CONFIRMATION',
               'NOTIFY_HEADER_END_CHECKOUT_SUCCESS',
               'NOTIFY_HEADER_END_SHOPPING_CART',

               /* From /includes/templates/{template}/common/html_header.php */
               'NOTIFY_HTML_HEAD_END',
            ]
        );

        // -----
        // POSM 4.4.0+, adding a notifier prefixed with 'NOTIFY_' to meet the zc158 requirements
        // for auto-loaded observers.
        //
        $this->notify('NOTIFY_PRODUCTS_OPTIONS_STOCK_OBSERVER_INSTANTIATED');

        // -----
        // Record the current field-length for orders_products::products_name.  We'll use this when appending a
        // product's stock message to ensure that the combination of the product's name and the message
        // doesn't "overflow" that field length -- especially important for stores with MySql Strict-mode where
        // that "too big" value will result in an error.
        //
        $this->name_max_length = (int)zen_field_length(TABLE_ORDERS_PRODUCTS, 'products_name');
    }

    // -----
    // Start observing methods ...
    // -----

    protected function notify_ajax_bootstrap_search_clauses(&$class, string $e, $search_keywords, string &$select_clause, string &$from_clause, string &$where_clause, string &$order_by_clause, string &$limit_clause)
    {
        $from_clause .= $this->getSearchFromClause();
    }
    protected function notify_search_from_string(&$class, string $e, string $from_str_in, string &$from_str_out)
    {
        $from_str_out .= $this->getSearchFromClause();
    }
    protected function getSearchFromClause()
    {
        return " LEFT JOIN " . TABLE_PRODUCTS_OPTIONS_STOCK . " posm ON posm.products_id = p.products_id";
    }

    protected function notify_build_keyword_search(&$class, string $e, $not_used, array &$fields)
    {
        $fields[] = 'posm.pos_model';
    }

    // -----
    // Issued by the order-class at the completion of the 'conversion' of an order from
    // its shopping-cart format to its order format.  Gives us the chance to update a
    // product's model-number from its generic value to its option-specific value.
    //
    protected function notify_order_cart_finished(\order &$order, string $e)
    {
        foreach ($order->products as &$current_product) {
            $pos_record = $this->getOptionsStockRecord($current_product['id']);
            if ($pos_record !== false && !$pos_record->EOF && !empty($pos_record->fields['pos_model'])) {
                $current_product['model'] = $pos_record->fields['pos_model'];
            }
        }
        unset($current_product);
    }

    // -----
    // Issued during order-creation by the order-class on a product-by-product basis.  Gives us the opportunity
    // to create the in/out-of-stock message to accompany the product in the order (used by the
    // NOTIFY_ORDER_PROCESSING_STOCK_DECREMENT_END processing).
    //
    protected function notify_order_processing_stock_decrement_init(\order &$order, string $e, array $products)
    {
        $this->stock_message = $this->get_in_stock_message($order->products[$products['i']]['id'], 'text', true);
    }

    // -----
    // Issued during order-creation by the order-class on a product-by-product basis.  If the current product
    // has stock managed by POSM, update that in-stock quantity.
    //
    protected function notify_order_processing_stock_decrement_begin(\order &$order, string $e, $index, &$result)
    {
        global $db;

        $i = (int)$index;
        $stock_record = $this->getOptionsStockRecord($order->products[$i]['id']);

        // -----
        // Issue a notification, enabling a watching observer to either bypass the stock decrement
        // for **all** products or to bypass the stock decrement only for products with managed options.
        //
        $decrement_options_stock = true;
        $bypass_stock_decrement = false;
        $this->notify(
            'NOTIFY_POSM_ORDER_STOCK_DECREMENT_BEGIN',
            [
                'product' => $order->products[$i],
                'stock' => $stock_record
            ],
            $bypass_stock_decrement,
            $decrement_options_stock
        );

        // -----
        // TRICKINESS WARNING:  The base order-class provides this notification with no "proper" way of cancelling the
        // stock-related operations that follow, instead relying on the "record-count" of the SQL query passed in $result
        // evaluating to 0.
        //
        // If an observer has requested a full-bypass of the stock decrement operation, we'll fake out
        // the SQL query, running one that "should" never return any records!
        //
        if ($bypass_stock_decrement === true) {
            $result = $db->Execute(
                "SELECT currencies_id
                   FROM " . TABLE_CURRENCIES . "
                  WHERE currencies_id = 0
                  LIMIT 1"
            );
        // -----
        // Otherwise, if this is a managed-stock product and no indication that we should
        // bypass the stock-decrement, continue processing.
        //
        } elseif ($decrement_options_stock === true && $stock_record !== false) {
            $result->fields['products_quantity'] = $order->products[$i]['qty'] + STOCK_REORDER_LEVEL + 1;
        }
    }

    // -----
    // Issued during order-creation by the order-class at the very end of each product's stock-handling.  We'll
    // append any in-/out-of-stock message to the product's name (ensuring that it doesn't 'overflow' the allowable
    // database field and save the unmodified version of the product's name to be used during the low-stock email.
    //
    protected function notify_order_processing_stock_decrement_end(\order &$order, string $e, $index)
    {
        $i = (int)$index;
        $this->products_name_saved = $order->products[$i]['name'];  //-For use in options' low-stock emails

        // -----
        // Make sure that the product's name+stock-message length isn't going to overflow (and potentially
        // result in a MySQL error).  If it will, the product's name will be truncated in deference
        // to the formatted stock-message.
        //
        $products_name = $order->products[$i]['name'];
        $name_length = $this->stringLen($products_name);
        $message_length = $this->stringLen($this->stock_message);
        if ($name_length + $message_length > $this->name_max_length) {
            $excess = $name_length + $message_length - $this->name_max_length;
            $products_name = $this->subString($products_name, 0, $name_length - $excess - 3) . '...';
            $pid = $order->products[$i]['id'];
            trigger_error("Product #$pid, name truncated to '$products_name', due to database size limitation ($name_length + $message_length > {$this->name_max_length}).", E_USER_WARNING);
        }
        $order->products[$i]['name'] = $products_name . $this->stock_message;
    }

    // -----
    // Issued during order-creation by the order-class, just after recording the product as an orders_products
    // entry.
    //
    protected function notify_order_during_create_added_product_line_item(\order &$order, string $e, array $ordered_product)
    {
        global $db;

        $prid = $ordered_product['products_prid'];
        $pos_record = $this->getOptionsStockRecord($prid);

        // -----
        // Issue a notification, enabling a watching observer to **totally** override the stock-adjustment
        // or to bypass the managed-stock updates (adjusting only the overall product's stock count).
        //
        $decrement_stock = true;
        $decrement_managed_stock = true;
        $this->notify(
            'NOTIFY_POSM_ORDER_ADDED_PRODUCT_LINE_ITEM',
            [
                'product' => $ordered_product,
                'stock' => $pos_record,
            ],
            $decrement_stock,
            $decrement_managed_stock
        );

        // -----
        // If the stock-adjustment is to be totally overridden, note the condition in the log only.
        //
        if ($decrement_stock !== true) {
            $this->debug_message("$eventID: Stock-adjustment bypassed via observer request.");
        // -----
        // If the current product does not have its options' stock managed ... simply make sure that the product quantity doesn't go negative.
        //
        } elseif ($pos_record === false) {
            $quantity_record = $db->ExecuteNoCache(
                "SELECT products_quantity
                   FROM " . TABLE_PRODUCTS . "
                  WHERE products_id = " . (int)$prid . "
                  LIMIT 1"
            );
            if ($quantity_record->fields['products_quantity'] < 0) {
                $db->Execute(
                    "UPDATE " . TABLE_PRODUCTS . "
                        SET products_quantity = 0
                      WHERE products_id = " . (int)$prid . "
                      LIMIT 1"
                );
            }
        // -----
        // If the current product's option-combination is not being stock-managed or if an observer has requested
        // that the 'managed stock' quantity-update be bypassed, recalculate the product's overall quantity.
        //
        } elseif ($pos_record->EOF || $decrement_managed_stock !== true) {
            $quantity_info = $db->ExecuteNoCache(
                "SELECT SUM(products_quantity) as quantity
                   FROM " . TABLE_PRODUCTS_OPTIONS_STOCK . "
                  WHERE products_id = " . (int)$prid
            );
            $products_quantity = $quantity_info->fields['quantity'] ?? 0;
            $db->Execute(
                "UPDATE " . TABLE_PRODUCTS . "
                    SET products_quantity = $products_quantity
                  WHERE products_id = " . (int)$prid . "
                  LIMIT 1"
            );
        // -----
        // Otherwise, the current product's option-combination IS being stock-managed.  Subtract from the option-specific stock -- the overall product's stock has
        // previously been reduced by the order class' processing.  Check that the overall product's stock value hasn't gone negative and set it back to 0 if it has.
        //
        } else {
            $new_option_quantity = $pos_record->fields['products_quantity'] - $_SESSION['cart']->contents[$prid]['qty'];
            if ($new_option_quantity < 0) {
                $new_option_quantity = 0;
            }
            $db->Execute(
                "UPDATE " . TABLE_PRODUCTS_OPTIONS_STOCK . "
                    SET products_quantity = $new_option_quantity
                  WHERE pos_id = " . $pos_record->fields['pos_id'] . "
                  LIMIT 1"
            );

            $quantity_info = $db->ExecuteNoCache(
                "SELECT SUM(products_quantity) as quantity
                   FROM " . TABLE_PRODUCTS_OPTIONS_STOCK . "
                  WHERE products_id = " . (int)$prid
            );
            if ($quantity_info->fields['quantity'] !== null) {
                $db->Execute(
                    "UPDATE " . TABLE_PRODUCTS . "
                        SET products_quantity = " . $quantity_info->fields['quantity'] . "
                      WHERE products_id = " . (int)$prid . "
                      LIMIT 1"
                );
            }

            if (SEND_LOWSTOCK_EMAIL === '1' && $new_option_quantity <= POSM_STOCK_REORDER_LEVEL && $pos_record->fields['products_quantity'] > POSM_STOCK_REORDER_LEVEL) {
                $products_attributes = '';
                foreach ($order->products[$ordered_product['i']]['attributes'] as $currentAttribute) {
                    $products_attributes .= (', ' . zen_options_name($currentAttribute['option_id']) . ': ' . zen_values_name($currentAttribute['value_id']));
                }
                $products_attributes = substr($products_attributes, 2);
                $email_low_stock = sprintf(POS_LOW_STOCK_EMAIL_CONTENTS, (int)$prid, $ordered_product['products_model'], $this->products_name_saved, $products_attributes, $new_option_quantity);
                zen_mail(
                    '',
                    SEND_EXTRA_LOW_STOCK_EMAILS_TO,
                    POS_EMAIL_TEXT_SUBJECT_LOWSTOCK,
                    $email_low_stock,
                    STORE_OWNER,
                    EMAIL_FROM,
                    ['EMAIL_MESSAGE_HTML' => nl2br($email_low_stock, false)],
                    'low_stock'
                );
            }
        }
        if (POSM_SHOW_STOCK_MESSAGES !== 'Store Only' && POSM_SHOW_STOCK_MESSAGES !== 'Both') {
            $i = $ordered_product['i'];
            $order->products[$i]['name'] = str_replace($this->stock_message, '', $order->products[$i]['name']);
        }
    }

    // -----
    // Issued before "normal" stock handling
    //
    protected function updateZenGetProductsStock(&$class, string $e, $products_id, &$products_quantity, bool &$quantity_handled)
    {
        $posm_options = false;
        if (isset($_GET['action'], $_POST['products_id'], $_POST['id']) && $_GET['action'] === 'add_product' && $_POST['products_id'] == $products_id && is_array($_POST['id'])) {
            $posm_options = $_POST['id'];
        }
        $pos_record = $this->getOptionsStockRecord($products_id, $posm_options);
        if ($pos_record !== false) {
            $products_quantity = ($pos_record->EOF) ? 0 : $pos_record->fields['products_quantity'];
            $quantity_handled = true;
        }
    }

    // -----
    // Issued at the end of /includes/modules/pages/account_history_info/header_php.php, enables
    // the addition of stock-related status.  Previously contained in header_php_options_stock.php
    // for that page.
    //
    protected function notify_header_end_account_history_info(&$class, string $e)
    {
        $this->addStockMessagesToOrderedProducts();
    }

    // -----
    // Issued at the end of /includes/modules/pages/checkout_confirmation/header_php.php, enables
    // the addition of stock-related status.  Previously contained in header_php_options_stock.php
    // for that page.
    //
    protected function notify_header_end_checkout_confirmation(&$class, string $e)
    {
        $this->addStockMessagesToOrder();
    }

    // -----
    // Issued at the end of /includes/modules/pages/checkout_one/header_php.php, enables
    // the addition of stock-related status.  Previously contained in header_php_checkout_one_options_stock.php
    // for that page.
    //
    protected function notify_header_end_checkout_one(&$class, string $e)
    {
        $this->addStockMessagesToOrder();
    }

    // -----
    // Issued at the end of /includes/modules/pages/checkout_one_confirmation/header_php.php, enables
    // the addition of stock-related status.  Previously contained in header_php_checkout_one__configuration_options_stock.php
    // for that page.
    //
    protected function notify_header_end_checkout_one_confirmation(&$class, string $e)
    {
        $this->addStockMessagesToOrder();
    }

    // -----
    // Issued at the end of /includes/modules/pages/checkout_success/header_php.php, enables
    // the addition of stock-related status.  Previously contained in header_php_options_stock.php
    // for that page.
    //
    protected function notify_header_end_checkout_success(&$class, string $e)
    {
        global $flag_show_products_notification, $notificationsArray;

        $this->addStockMessagesToOrderedProducts();

        if ($flag_show_products_notification === true) {
            $previous_product_id = 0;
            foreach ($notificationsArray as &$notification) {
                if ($previous_product_id == $notification['products_id']) {
                    unset($notificationsArray[$notification['counter']]);
                } else {
                    if (preg_match ('/(.*)\[(.*)\]$/', $notification['products_name'], $matches)) {
                        $notification['products_name'] = $matches[1];
                    }
                    $previous_product_id = $notification['products_id'];
                }
            }
            unset($notification);
        }
    }

    // -----
    // Issued at the end of /includes/modules/pages/shopping_cart/header_php.php, enables
    // the addition of stock-related status.  Previously contained in header_php_options_stock.php
    // for that page.
    //
    protected function notify_header_end_shopping_cart(&$class, string $e)
    {
        global $productArray;

        if (empty($productArray) || !is_array($productArray)) {
            return;
        }

        $num_products = count($productArray);
        $posm_include_model = (POSM_CART_DISPLAY_MODEL_NUMBERS === 'true');
        for ($i = 0; $i < $num_products; $i++) {
            $pid = $productArray[$i]['id'];

            // -----
            // Starting with POSM v4.0.0, prepend the in/out-of-stock message to the product's name
            // for the shopping-cart page display.
            //
            $stock_message = $this->get_in_stock_message($pid);
            $productArray[$i]['posStockMessage'] = $stock_message;
            $productArray[$i]['productsName'] = $stock_message . $productArray[$i]['productsName'];

            // -----
            // Save the product's POSM-model number for possible template use and, if enabled,
            // append the model number to the product's name for display.
            //
            $posm_model = $this->getInCartProductsModel($pid);
            $productArray[$i]['posModel'] = $posm_model;

            if ($posm_include_model === true && $posm_model !== '') {
                $productArray[$i]['productsName'] .= " [$posm_model]";
            }
        }
    }

    // -----
    // Issued at the end of the active template's html_header.php (just before
    // the </head> tag, enables the plugin's CSS and JS files to be inserted.
    //
    protected function notify_html_head_end(&$class, string $e)
    {
        global $template, $current_page_base;

        // -----
        // Look for any plugin's stylesheet, first in the as-distributed 'default'
        // and then for any overrides present in the site's active template.
        //
        $stylesheet = 'options_stock_styles.css';
        echo '<link rel="stylesheet" href="' . $this->getZcPluginDir() . DIR_WS_TEMPLATES . "default/css/$stylesheet" . '">' . "\n";

        $stylesheet_dir = $template->get_template_dir($stylesheet, DIR_WS_TEMPLATE, $current_page_base, 'css');
        if (strpos($stylesheet_dir, $this->getZcPluginDir()) === false && file_exists($stylesheet_dir . $stylesheet)) {
            echo '<link rel="stylesheet" href="' . $stylesheet_dir . $stylesheet . '">' . "\n";
        }

        // -----
        // Next, load the plugin's dependent-attributes' handling. Note that, currently, no template-override
        // is provided!
        //
        require $this->getZcPluginDir() . DIR_WS_TEMPLATES . 'default/jscript/posm_dependencies_jscript.php';
    }

    // -----
    // ... End observing methods.
    // -----

    // -----
    // Return the plugin's currently-installed zc_plugin directory for the catalog.
    //
    public function getZcPluginDir()
    {
        return $this->zcPluginDir;
    }

    // -----
    // Add stock-related messages to an order's products.  Used on the `checkout_confirmation`,
    // `checkout_one` and `checkout_one_confirmation` pages.
    //
    protected function addStockMessagesToOrder()
    {
        global $order, $flagAnyOutOfStock;

        $num_products = count($order->products);
        for ($i = 0; $i < $num_products; $i++) {
            $stock_message = $this->get_in_stock_message($order->products[$i]['id']);
            $order->products[$i]['name'] = $stock_message . $order->products[$i]['name'];
        }
        $flagAnyOutOfStock = false;
    }

    // -----
    // Add stock-related messages to previously-placed orders.  Used in updating the ordered
    // products in the `account_history_info` and `checkout_success` pages.
    //
    protected function addStockMessagesToOrderedProducts()
    {
        global $order;

        for ($i = 0, $n = count($order->products); $i < $n; $i++) {
            if (preg_match('/(.*)\[(.*)\]$/', $order->products[$i]['name'], $matches)) {
                $order->products[$i]['name'] = $matches[1];
                if ($this->show_stock_messages === false) {
                    continue;
                }

                $msg = $matches[2];
                $extra_msg = '';
                if ($msg === PRODUCTS_OPTIONS_STOCK_IN_STOCK) {
                    $extra_class = 'in-stock';
                    if (POSM_SHOW_IN_STOCK_MESSAGE === 'false') {
                        $msg = '';
                    }
                } elseif (strpos($msg, ', ') === false) {
                    $extra_class = 'no-stock';
                } else {
                    $extra_class = 'in-stock';
                    $message_parts = explode(', ', $msg);
                    $msg = $message_parts[0];
                    $extra_msg = ' ' . sprintf(PRODUCTS_OPTIONS_STOCK_STOCK_HTML, 'no-stock', $message_parts[1]);
                }
                if ($msg !== '') {
                    $msg = sprintf(PRODUCTS_OPTIONS_STOCK_STOCK_HTML, $extra_class, $msg) . $extra_msg;
                    $order->products[$i]['name'] .= sprintf(PRODUCTS_OPTIONS_STOCK_WRAPPER, $msg);
                }
            }
        }
    }

    // -----
    // Return the in-/out-of-stock message for the specified product.
    //
    // Notes:
    // - When STOCK_CHECK is set to 'true', the in-stock message must be null ('') or a redirect back to the shopping-cart page occurs during checkout.
    //
    public function get_in_stock_message($prid, $html_or_text = 'html', $always_output = false)
    {
        $msg = '';
        $options_quantity = $cart_quantity = $pos_record = 'n/a';
        $always_output = (bool)$always_output;
        if ($this->enabled === true && ($always_output === true || $this->show_stock_messages === true)) {
            $in_stock_message = (POSM_SHOW_IN_STOCK_MESSAGE === 'true' || $always_output === true) ? PRODUCTS_OPTIONS_STOCK_IN_STOCK : '';
            $no_stock_message = PRODUCTS_OPTIONS_STOCK_NOT_IN_STOCK;

            $msg_html = '';
            $msg_text = '';
            $extra_class = '';
            $msg_extra = '';
            $pos_record = $this->getOptionsStockRecord($prid);

            // -----
            // Issue a notification, enabling store-specific customizations to override the in-/out-of-stock
            // message for the specified product.  An observer can indicate that:
            //
            // 1. The overall in-/out-of-stock message handling should be bypassed, using the in- or out-of-stock message as
            //    indicated.  The observer, in this case, sets the $message_override ($p2) to (bool)true and the $use_in_stock_message ($p3)
            //    to identify whether the in-stock (true) or out-of-stock (otherwise) messaging should be used.
            // 2. No mixed-stock messages should be displayed.  If the ordered quantity of a managed product is mixed, i.e. some
            //    in-stock and some out, and the observer sets $show_mixed_stock_messages ($p4) to something _other than_ (bool)true
            //    then the message for the product shows the out-of-stock message **only**.
            //
            $message_override = false;
            $use_in_stock_message = true;
            $show_mixed_stock_messages = true;
            $this->notify(
                'NOTIFY_POSM_GET_IN_STOCK_MESSAGE',
                [
                    'prid' => $prid,
                    'pos_record' => $pos_record,
                ],
                $message_override,
                $use_in_stock_message,
                $show_mixed_stock_messages
            );

            // -----
            // If an observer has indicated that the stock message should be overridden, set the to-be-displayed message
            // to either the in- or out-of-stock version, based on the observer's return.
            //
            if ($message_override === true) {
                $this->debugMessage("Stock message overridden by observer ($use_in_stock_message)." . json_encode($pos_record));
                if ($use_in_stock_message === true) {
                    $extra_class = 'in-stock';
                    $msg_html = $msg_text = $in_stock_message;
                } else {
                    $extra_class = 'no-stock';
                    $msg_html = $msg_text = $no_stock_message;
                }
            } elseif ($pos_record === false) {
                if (POSM_SHOW_UNMANAGED_OPTIONS_STATUS === 'true') {
                    $quantity = zen_get_products_stock($prid);
                    if ($quantity >= $_SESSION['cart']->contents[$prid]['qty']) {
                        $extra_class = 'in-stock';
                        $msg_html = $msg_text = $in_stock_message;

                    } elseif ($quantity == 0 || $show_mixed_stock_messages !== true) {
                        $extra_class = 'no-stock';
                        $msg_html = $msg_text = $no_stock_message;

                    } else {
                        $msg_text = sprintf(PRODUCTS_OPTIONS_STOCK_MIXED, $quantity, PRODUCTS_OPTIONS_STOCK_IN_STOCK, $_SESSION['cart']->contents[$prid]['qty'] - $quantity, $no_stock_message);
                        $msg_html = $quantity . ' ' . PRODUCTS_OPTIONS_STOCK_IN_STOCK;
                        $no_stock_style = 'no-stock stock-mixed';
                        $extra_class = 'in-stock stock-mixed';
                        $msg_extra .= sprintf(PRODUCTS_OPTIONS_STOCK_STOCK_HTML, $no_stock_style, $_SESSION['cart']->contents[$prid]['qty'] - $quantity . ' ' . $no_stock_message);
                    }
                }
            } else {
                if ($pos_record->EOF) {
                    if (POSM_SHOW_UNMANAGED_OPTIONS_STATUS === 'true') {
                        $msg_html = $msg_text = $no_stock_message;
                        $extra_class = 'no-stock';
                    }
                } else {
                    $no_stock_message = str_replace('[date]', $pos_record->fields['pos_date'], get_pos_oos_name($pos_record->fields['pos_name_id'], $_SESSION['languages_id']));
                    $options_quantity = $pos_record->fields['products_quantity'];
                    $cart_quantity = $_SESSION['cart']->contents[$prid]['qty'];
                    if ($options_quantity >= $cart_quantity) {
                        $msg_html = $in_stock_message;
                        $msg_text = $in_stock_message;
                        $extra_class = 'in-stock';

                    } elseif ($options_quantity == 0 || $show_mixed_stock_messages !== true) {
                        $msg_html = $no_stock_message;
                        $msg_text = $no_stock_message;
                        $extra_class = 'no-stock';

                    } else {
                        $msg_text = sprintf(PRODUCTS_OPTIONS_STOCK_MIXED, $options_quantity, PRODUCTS_OPTIONS_STOCK_IN_STOCK, $_SESSION['cart']->contents[$prid]['qty'] - $options_quantity, $no_stock_message);
                        $msg_html = $options_quantity . ' ' . PRODUCTS_OPTIONS_STOCK_IN_STOCK;
                        $no_stock_style = 'no-stock stock-mixed';
                        $extra_class = 'in-stock stock-mixed';
                        $msg_extra .= sprintf(PRODUCTS_OPTIONS_STOCK_STOCK_HTML, $no_stock_style, $_SESSION['cart']->contents[$prid]['qty'] - $options_quantity . ' ' . $no_stock_message);
                    }
                }
            }

            if ($msg_html === '' && $msg_extra === '') {
                $msg = '';

            } elseif ($html_or_text !== 'html') {
                $msg = ($msg_text !== '') ? sprintf(PRODUCTS_OPTIONS_STOCK_STOCK_TEXT, $msg_text) : '';

            } else {
                $msg = (($msg_html !== '') ? sprintf(PRODUCTS_OPTIONS_STOCK_STOCK_HTML, $extra_class, $msg_html) : '') . $msg_extra;
                $msg = sprintf(PRODUCTS_OPTIONS_STOCK_WRAPPER, $msg);
            }
        }
        $this->debugMessage("($msg) = get_in_stock_message($prid, $html_or_text)), $options_quantity, $cart_quantity, pos_record = " . json_encode($pos_record));
        return $msg;
    }

    // -----
    // Return the in-stock/out-of-stock message for a product/attribute combination that is not yet placed in the cart.  If the
    // product doesn't have any POSM definitions, an empty string is returned.
    //
    public function get_product_in_stock_message($pid, $attributes): string
    {
        global $db;
        $msg = '';
        if ($this->enabled === true) {
            if (is_pos_product($pid)) {
                $check = $db->ExecuteNoCache(
                    "SELECT pos_id, products_quantity, pos_model, pos_name_id, pos_date
                       FROM " . TABLE_PRODUCTS_OPTIONS_STOCK . "
                      WHERE products_id = $pid
                        AND pos_hash = '" . generate_pos_option_hash($pid, $attributes) . "'
                      LIMIT 1"
                );
                if ($check->EOF) {
                    $msg = PRODUCTS_OPTIONS_STOCK_NOT_IN_STOCK;
                } else {
                    $option_quantity = $this->get_product_option_quantity($pid, $attributes);
                    if ($option_quantity == 0) {
                        $msg = str_replace('[date]', $check->fields['pos_date'], get_pos_oos_name($check->fields['pos_name_id'], $_SESSION['languages_id']));
                    } else {
                        $msg = PRODUCTS_OPTIONS_STOCK_IN_STOCK;
                    }
                }
            }
        }
        return $msg;
    }

    // -----
    // Return the quantity available for the specified product/attribute combination, taking into account any of the combination that
    // might currently be in the customer's cart.
    //
    public function get_product_option_quantity($pid, $attributes)
    {
        global $db;

        $quantity = false;
        if ($this->enabled === true) {
            if (is_pos_product($pid)) {
                $hash = generate_pos_option_hash($pid, $attributes);
                $check = $db->ExecuteNoCache(
                    "SELECT pos_id, products_quantity, pos_model, pos_name_id, pos_date
                       FROM " . TABLE_PRODUCTS_OPTIONS_STOCK . "
                      WHERE products_id = $pid
                        AND pos_hash = '$hash'
                      LIMIT 1"
                );
                if ($check->EOF) {
                    $quantity = 0;
                } else {
                    $quantity = $check->fields['products_quantity'] - $_SESSION['cart']->get_quantity(zen_get_uprid($pid, $attributes));
                    if ($quantity < 0) {
                        $quantity = 0;
                    }
                }
            }
        }
        return $quantity;
    }

    // -----
    // Returns an in-cart product's model number, possibly option-specific, for use
    // in various product-display (and the shopping_cart) pages.
    //
    // If the product is not POSM-managed or its option-specific model number is
    // empty, returns the overall/base product's model number.  Otherwise, returns
    // the option-specific model number.
    //
    public function getInCartProductsModel($pid): string
    {
        global $db;

        $base = $db->Execute(
            "SELECT products_model
               FROM " . TABLE_PRODUCTS . "
              WHERE products_id = " . (int)$pid . "
              LIMIT 1"
        );
        $products_model = ($base->EOF) ? '' : $base->fields['products_model'];
        if ($this->enabled) {
            $pos_record = $this->getOptionsStockRecord($pid);
            if ($pos_record !== false && !$pos_record->EOF && !empty($pos_record->fields['pos_model'])) {
                $products_model = $pos_record->fields['pos_model'];
            }
        }
        return $products_model;
    }

    // -----
    // Return the products_options_stock record associated with the specified prid (assumed to be in the current cart).
    //
    protected function getOptionsStockRecord($prid, $posm_options = false)
    {
        global $db;

        $pid = (int)$prid;
        if ($posm_options === false && isset($_SESSION['cart']->contents[$prid], $_SESSION['cart']->contents[$prid]['attributes'])) {
            $posm_options = $_SESSION['cart']->contents[$prid]['attributes'];
        }
        if (!(is_pos_product($pid) && is_array($posm_options))) {
            $check = false;
        } else {
            $hash = generate_pos_option_hash($pid, $posm_options);
            $check = $db->ExecuteNoCache(
                "SELECT *
                   FROM " . TABLE_PRODUCTS_OPTIONS_STOCK . "
                  WHERE products_id = $pid
                    AND pos_hash = '$hash'
                  LIMIT 1"
            );
        }
        return $check;
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

    // -----
    // Write a debug-output to the current session's myDEBUG-POSM-*.log file, if enabled.
    //
    protected function debugMessage($msg)
    {
        $this->debug_message("products_options_stock_observer: $msg");
    }

    public function debug_message($msg)
    {
        if ($this->debug === true) {
            error_log("$msg\n", 3, $this->debug_log_file);
        }
    }
}
