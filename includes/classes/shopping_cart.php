<?php
/**
 * Class for managing the Shopping Cart
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Jun 16 Modified in v1.5.7 $
 */

if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
class shoppingCart extends base {
  /**
   * shopping cart contents
   * @var array
   */
  var $contents;
  /**
   * shopping cart total price
   * @var decimal
   */
  var $total;
  /**
   * shopping cart total weight
   * @var decimal
   */
  var $weight;
  /**
   * cart identifier
   * @var integer
   */
  var $cartID;
  /**
   * overall content type of shopping cart
   * @var string
   */
  var $content_type;
  /**
   * number of free shipping items in cart
   * @var decimal
   */
  var $free_shipping_item;
  /**
   * total price of free shipping items in cart
   * @var decimal
   */
  var $free_shipping_weight;
  /**
   * total weight of free shipping items in cart
   * @var decimal
   */
  var $free_shipping_price;
  /**
   * total downloads in cart
   * @var decimal
   */
  var $download_count;
  /**
   * shopping cart total price before Specials, Sales and Discounts
   * @var decimal
   */
  var $total_before_discounts;
  /**
   * set to TRUE to see debug messages for developer use when troubleshooting add/update cart
   * Then, Logout/Login to reset cart for change
   * @var string
   */
  var $display_debug_messages = FALSE;
  var $flag_duplicate_msgs_set = FALSE;
  var $flag_duplicate_quantity_msgs_set = array();

  /**
   * Instantiate a new shopping cart object
   */
  function __construct() {
    $this->notify('NOTIFIER_CART_INSTANTIATE_START');
    $this->reset();
    $this->notify('NOTIFIER_CART_INSTANTIATE_END');
  }
  /**
   * Restore cart contents
   *
   * For customers who login, cart contents are also stored in the database.
   * {TABLE_CUSTOMER_BASKET et al}. This allows the system to remember the
   * contents of their cart over multiple sessions.
   * This method simply retrieves the content of the database stored cart
   * for a given customer. Note also that if the customer already has
   * some items in their cart before they login, these are merged with
   * the stored contents.
   *
   * @return void
   */
  function restore_contents() {
    global $db;
    if (!zen_is_logged_in() || zen_in_guest_checkout()) {
        return false;
    }
    $this->notify('NOTIFIER_CART_RESTORE_CONTENTS_START');
    // insert current cart contents in database
    if (is_array($this->contents)) {
      foreach($this->contents as $products_id => $data) {
        //          $products_id = urldecode($products_id);
        $qty = $this->contents[$products_id]['qty'];
        $product_query = "SELECT products_id
                            FROM " . TABLE_CUSTOMERS_BASKET . "
                            WHERE customers_id = " . (int)$_SESSION['customer_id'] . "
                            AND products_id = '" . zen_db_input($products_id) . "'";

        $product = $db->Execute($product_query);

        if ($product->RecordCount()<=0) {
          $sql = "INSERT INTO " . TABLE_CUSTOMERS_BASKET . "
                                (customers_id, products_id, customers_basket_quantity,
                                 customers_basket_date_added)
                                 VALUES (" . (int)$_SESSION['customer_id'] . ", '" . zen_db_input($products_id) . "', '" .
          $qty . "', '" . date('Ymd') . "')";

          $db->Execute($sql);

          if (isset($this->contents[$products_id]['attributes'])) {
            foreach($this->contents[$products_id]['attributes'] as $option => $value) {

              //clr 031714 udate query to include attribute value. This is needed for text attributes.
              $attr_value = isset($this->contents[$products_id]['attributes_values'][$option]) ? $this->contents[$products_id]['attributes_values'][$option] : '';

              $products_options_sort_order= zen_get_attributes_options_sort_order(zen_get_prid($products_id), $option, $value);
              if ($attr_value) {
                $attr_value = zen_db_input($attr_value);
              }
              $sql = "INSERT INTO " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . "
                      (customers_id, products_id, products_options_id,
                       products_options_value_id, products_options_value_text, products_options_sort_order)
                       VALUES (" . (int)$_SESSION['customer_id'] . ", '" . zen_db_input($products_id) . "', '" .
                       $option . "', '" . $value . "', '" . $attr_value . "', '" . $products_options_sort_order . "')";

              $db->Execute($sql);
            }
          }
        } else {
          $sql = "UPDATE " . TABLE_CUSTOMERS_BASKET . "
                  SET customers_basket_quantity = '" . $qty . "'
                  WHERE customers_id = " . (int)$_SESSION['customer_id'] . "
                  AND products_id = '" . zen_db_input($products_id) . "'";
          $db->Execute($sql);

        }
      }
    }

    // reset per-session cart contents, but not the database contents
    $this->reset(false);

    $products_query = "SELECT products_id, customers_basket_quantity
                       FROM " . TABLE_CUSTOMERS_BASKET . "
                       WHERE customers_id = " . (int)$_SESSION['customer_id'] . "
                       ORDER BY customers_basket_id";
    $products = $db->Execute($products_query);

    while (!$products->EOF) {
      $this->contents[$products->fields['products_id']] = array('qty' => $products->fields['customers_basket_quantity']);

      // set contents in sort order
      $order_by = ' order by LPAD(products_options_sort_order,11,"0")';

      $attributes = $db->Execute("SELECT products_options_id, products_options_value_id, products_options_value_text
                             FROM " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . "
                             WHERE customers_id = " . (int)$_SESSION['customer_id'] . "
                             AND products_id = '" . zen_db_input($products->fields['products_id']) . "' " . $order_by);

      while (!$attributes->EOF) {
        $this->contents[$products->fields['products_id']]['attributes'][$attributes->fields['products_options_id']] = $attributes->fields['products_options_value_id'];
        //CLR 020606 if text attribute, then set additional information
        if ($attributes->fields['products_options_value_id'] == PRODUCTS_OPTIONS_VALUES_TEXT_ID) {
          $this->contents[$products->fields['products_id']]['attributes_values'][$attributes->fields['products_options_id']] = $attributes->fields['products_options_value_text'];
        }
        $attributes->MoveNext();
      }
      $products->MoveNext();
    }
    $this->cartID = $this->generate_cart_id();
    $this->notify('NOTIFIER_CART_RESTORE_CONTENTS_END');
    $this->cleanup();
  }
  /**
   * Reset cart contents
   *
   * Resets the contents of the session cart(e,g, empties it)
   * Depending on the setting of the $reset_database parameter will
   * also empty the contents of the database stored cart. (Only relevant
   * if the customer is logged in)
   *
   * @param boolean whether to reset customers db basket
   * @return void
   */
  function reset($reset_database = false) {
    global $db;
    $this->notify('NOTIFIER_CART_RESET_START', array(), $reset_database);
    $this->contents = array();
    $this->total = 0;
    $this->weight = 0;
    $this->download_count = 0;
    $this->total_before_discounts = 0;
    $this->content_type = false;

    // shipping adjustment
    $this->free_shipping_item = 0;
    $this->free_shipping_price = 0;
    $this->free_shipping_weight = 0;

    if (zen_is_logged_in() && $reset_database == true) {
      $sql = "DELETE FROM " . TABLE_CUSTOMERS_BASKET . " WHERE customers_id = " . (int)$_SESSION['customer_id'];
      $db->Execute($sql);
      $sql = "DELETE FROM " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " WHERE customers_id = " . (int)$_SESSION['customer_id'];
      $db->Execute($sql);
    }

    unset($this->cartID);
    $_SESSION['cartID'] = '';
    $this->notify('NOTIFIER_CART_RESET_END');
  }
  /**
   * Add an item to the cart
   *
   * This method is usually called as the result of a user action.
   * As the method name applies it adds an item to the users current cart in memory
   * and if the customer is logged in, also adds to the database stored cart.
   *
   * @param integer the product ID of the item to be added
   * @param decimal the quantity of the item to be added
   * @param array any attributes that are attached to the product
   * @param boolean whether to add the product to the notify list
   * @return void
   */
  function add_cart($products_id, $qty = '1', $attributes = '', $notify = true) {
    global $db, $messageStack;
    if ($this->display_debug_messages) $messageStack->add_session('header', 'FUNCTION ' . __FUNCTION__, 'caution');

    if (!is_numeric($qty) || $qty < 0) {
      // adjust quantity when not a value
      $chk_link = '<a href="' . zen_href_link(zen_get_info_page($products_id), 'cPath=' . (zen_get_generated_category_path_rev(zen_get_products_category_id($products_id))) . '&products_id=' . $products_id) . '">' . zen_get_products_name($products_id) . '</a>';
      $messageStack->add_session('header', ERROR_CORRECTIONS_HEADING . ERROR_PRODUCT_QUANTITY_UNITS_SHOPPING_CART . $chk_link . ' ' . PRODUCTS_ORDER_QTY_TEXT . zen_output_string_protected($qty), 'caution');
      $qty = 0;
    }
    $this->notify('NOTIFIER_CART_ADD_CART_START', array(), $products_id, $qty, $attributes, $notify);
    $products_id = zen_get_uprid($products_id, $attributes);
    if ($notify == true) {
      $_SESSION['new_products_id_in_cart'] = $products_id;
    }

    $qty = $this->adjust_quantity($qty, $products_id, 'shopping_cart');

    if ($this->in_cart($products_id)) {
      $this->update_quantity($products_id, $qty, $attributes);
    } else {
      $this->contents[] = array($products_id);  // @TODO - why is this line here? Appears to be removed in the call to cleanup(), so doesn't really serve any purpose here.
      $this->contents[$products_id] = array('qty' => (float)$qty);
      // insert into database
      if (zen_is_logged_in() && !zen_in_guest_checkout()) {
        $sql = "INSERT INTO " . TABLE_CUSTOMERS_BASKET . "
                (customers_id, products_id, customers_basket_quantity,
                customers_basket_date_added)
                VALUES (" . (int)$_SESSION['customer_id'] . ", '" . zen_db_input($products_id) . "', '" .
                $qty . "', '" . date('Ymd') . "')";
        $db->Execute($sql);
      }

      if (is_array($attributes)) {
        foreach($attributes as $option => $value) {
          //CLR 020606 check if input was from text box.  If so, store additional attribute information
          //CLR 020708 check if text input is blank, if so do not add to attribute lists
          //CLR 030228 add htmlspecialchars processing.  This handles quotes and other special chars in the user input.
          $attr_value = NULL;
          $blank_value = FALSE;
          if (strstr($option, TEXT_PREFIX)) {
            if (trim($value) == NULL) {
              $blank_value = TRUE;
            } else {
              $option = substr($option, strlen(TEXT_PREFIX));
              $attr_value = stripslashes($value);
              $value = PRODUCTS_OPTIONS_VALUES_TEXT_ID;

              // Validate max-length of TEXT attribute
              $check = $db->Execute ("SELECT products_options_length FROM " . TABLE_PRODUCTS_OPTIONS . " WHERE products_options_id = " . (int)$option . " LIMIT 1");
              if (!$check->EOF) {
                if (strlen ($attr_value) > $check->fields['products_options_length']) {
                  $attr_value = zen_trunc_string ($attr_value, $check->fields['products_options_length'], '');
                }
                $this->contents[$products_id]['attributes_values'][$option] = $attr_value;
              }
            }
          }

          if (!$blank_value) {
            if (is_array($value) ) {
              foreach($value as $opt => $val) {
                $this->contents[$products_id]['attributes'][$option.'_chk'.$val] = $val;
              }
            } else {
              $this->contents[$products_id]['attributes'][$option] = $value;
            }

            if (zen_is_logged_in() && !zen_in_guest_checkout()) {
              if (is_array($value) ) {
                foreach($value as $opt => $val) {
                  $products_options_sort_order= zen_get_attributes_options_sort_order(zen_get_prid($products_id), $option, $opt);
                  $sql = "INSERT INTO " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . "
                          (customers_id, products_id, products_options_id, products_options_value_id, products_options_sort_order)
                          VALUES (" . (int)$_SESSION['customer_id'] . ", '" . zen_db_input($products_id) . "', 
                          '" . (int)$option.'_chk'. (int)$val . "', '" . (int)$val . "',  '" . $products_options_sort_order . "')";
                  $db->Execute($sql);
                }
              } else {
                if ($attr_value) {
                  $attr_value = zen_db_input($attr_value);
                }
                $products_options_sort_order= zen_get_attributes_options_sort_order(zen_get_prid($products_id), $option, $value);
                $sql = "INSERT INTO " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . "
                        (customers_id, products_id, products_options_id, products_options_value_id, products_options_value_text, products_options_sort_order)
                        VALUES (" . (int)$_SESSION['customer_id'] . ", '" . zen_db_input($products_id) . "', 
                        '" . (int)$option . "', '" . (int)$value . "', '" . $attr_value . "', '" . $products_options_sort_order . "')";
                $db->Execute($sql);
              }
            }
          }
        }
      }
    }
    $this->cleanup();

    // assign a temporary unique ID to the order contents to prevent hack attempts during the checkout procedure
    $this->cartID = $this->generate_cart_id();
    $this->notify('NOTIFIER_CART_ADD_CART_END', array(), $products_id, $qty, $attributes, $notify);
  }
  /**
   * Update the quantity of an item already in the cart
   *
   * Changes the current quantity of a certain item in the cart to
   * a new value. Also updates the database stored cart if customer is
   * logged in.
   *
   * @param mixed product ID of item to update
   * @param decimal the quantity to update the item to
   * @param array product atributes attached to the item
   * @return void
   */
  function update_quantity($products_id, $quantity = '', $attributes = '') {
    global $db, $messageStack;
    if ($this->display_debug_messages) $messageStack->add_session('header', 'FUNCTION ' . __FUNCTION__ . ' $products_id: ' . $products_id . ' $quantity: ' . $quantity, 'caution');

    if (!is_numeric($quantity) || $quantity < 0) {
      // adjust quantity when not a value
      $chk_link = '<a href="' . zen_href_link(zen_get_info_page($products_id), 'cPath=' . (zen_get_generated_category_path_rev(zen_get_products_category_id($products_id))) . '&products_id=' . $products_id) . '">' . zen_get_products_name($products_id) . '</a>';
      $messageStack->add_session('header', ERROR_CORRECTIONS_HEADING . ERROR_PRODUCT_QUANTITY_UNITS_SHOPPING_CART . $chk_link . ' ' . PRODUCTS_ORDER_QTY_TEXT . zen_output_string_protected($quantity), 'caution');
      $quantity = 0;
    }
    $this->notify('NOTIFIER_CART_UPDATE_QUANTITY_START', array(), $products_id, $quantity, $attributes);
    if (empty($quantity)) return true; // nothing needs to be updated if theres no quantity, so we return true..

    // ensure quantity added to cart is never more than what is in-stock
    $chk_current_qty = zen_get_products_stock($products_id);
    if (STOCK_ALLOW_CHECKOUT == 'false' && ($quantity > $chk_current_qty)) {
      $quantity = $chk_current_qty;
      if (!$this->flag_duplicate_msgs_set) {
        $messageStack->add_session('shopping_cart', ($this->display_debug_messages ? '$_GET[main_page]: ' . $_GET['main_page'] . ' FUNCTION ' . __FUNCTION__ . ': ' : '') . WARNING_PRODUCT_QUANTITY_ADJUSTED . zen_get_products_name($products_id), 'caution');
      }
    }

    $this->contents[$products_id] = array('qty' => (float)$quantity);

    if (zen_is_logged_in() && !zen_in_guest_checkout()) {
      $sql = "UPDATE " . TABLE_CUSTOMERS_BASKET . "
              SET customers_basket_quantity = '" . (float)$quantity . "'
              WHERE customers_id = " . (int)$_SESSION['customer_id'] . "
              AND products_id = '" . zen_db_input($products_id) . "'";
      $db->Execute($sql);
    }

    if (is_array($attributes)) {
      foreach($attributes as $option => $value) {
        //CLR 020606 check if input was from text box.  If so, store additional attribute information
        //CLR 030108 check if text input is blank, if so do not update attribute lists
        //CLR 030228 add htmlspecialchars processing.  This handles quotes and other special chars in the user input.
        $attr_value = NULL;
        $blank_value = FALSE;
        if (strstr($option, TEXT_PREFIX)) {
          if (trim($value) == NULL) {
            $blank_value = TRUE;
          } else {
            $option = substr($option, strlen(TEXT_PREFIX));
            $attr_value = stripslashes($value);
            $value = PRODUCTS_OPTIONS_VALUES_TEXT_ID;
            $this->contents[$products_id]['attributes_values'][$option] = $attr_value;
          }
        }

        if (!$blank_value) {
          if (is_array($value) ) {
            foreach($value as $opt => $val) {
              $this->contents[$products_id]['attributes'][$option.'_chk'.$val] = $val;
            }
          } else {
            $this->contents[$products_id]['attributes'][$option] = $value;
          }

          if (zen_is_logged_in() && !zen_in_guest_checkout()) {
              if ($attr_value) {
                $attr_value = zen_db_input($attr_value);
              }
              if (is_array($value) ) {
                foreach($value as $opt => $val) {
                  $products_options_sort_order= zen_get_attributes_options_sort_order(zen_get_prid($products_id), $option, $opt);
                  $sql = "UPDATE " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . "
                          SET products_options_value_id = '" . (int)$val . "'
                          WHERE customers_id = " . (int)$_SESSION['customer_id'] . "
                          AND products_id = '" . zen_db_input($products_id) . "'
                          AND products_options_id = '" . (int)$option.'_chk'.(int)$val . "'";
                  $db->Execute($sql);
                }
              } else {
                  $sql = "UPDATE " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . "
                          SET products_options_value_id = " . (int)$value . ", products_options_value_text = '" . $attr_value . "'
                          WHERE customers_id = " . (int)$_SESSION['customer_id'] . "
                          AND products_id = '" . zen_db_input($products_id) . "'
                          AND products_options_id = '" . (int)$option . "'"; // intentionally passing a string
                  $db->Execute($sql);
              }
          }
        }
      }
    }
    $this->cartID = $this->generate_cart_id();
    $this->notify('NOTIFIER_CART_UPDATE_QUANTITY_END');
  }
  /**
   * Clean up cart contents - removes zero-qty items
   *
   * For various reasons, the quantity of an item in the cart can
   * fall to zero. This method removes from the cart
   * all items that have reached this state. The database-stored cart
   * is also updated where necessary
   *
   * @return void
   */
  function cleanup() {
    global $db;
    $this->notify('NOTIFIER_CART_CLEANUP_START');
    foreach($this->contents as $key => $data) {
      if (!isset($this->contents[$key]['qty']) || $this->contents[$key]['qty'] <= 0) {
        unset($this->contents[$key]);

        if (zen_is_logged_in() && !zen_in_guest_checkout()) {
          $sql = "DELETE FROM " . TABLE_CUSTOMERS_BASKET . "
                  WHERE customers_id = " . (int)$_SESSION['customer_id'] . "
                  AND products_id = '" . $key . "'";
          $db->Execute($sql);

          $sql = "DELETE FROM " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . "
                  WHERE customers_id = " . (int)$_SESSION['customer_id'] . "
                  AND products_id = '" . $key . "'";
          $db->Execute($sql);
        }
      }
    }
    $this->notify('NOTIFIER_CART_CLEANUP_END');
  }
  /**
   * Count total number of items in cart
   *
   * Note this is not just the number of distinct items in the cart,
   * but the number of items adjusted for the quantity of each item
   * in the cart.
   * Example: if we have 2 items in the cart, one with a quantity
   * of 3 and the other with a quantity of 4 our total number of items
   * would be 7
   *
   * @return total number of items in cart
   */
  function count_contents() {
    $this->notify('NOTIFIER_CART_COUNT_CONTENTS_START');
    $total_items = 0;
    if (is_array($this->contents)) {
      foreach($this->contents as $products_id => $data) {
        $total_items += $this->get_quantity($products_id);
      }
    }
    $this->notify('NOTIFIER_CART_COUNT_CONTENTS_END');
    return $total_items;
  }
  /**
   * Get the quantity of an item in the cart
   * NOTE: This accepts attribute hash as $products_id, such as: 12:a35de52391fcb3134
   * ... and treats 12 as unique from 12:a35de52391fcb3134
   * To lookup based only on prid (ie: 12 here) regardless of the attribute hash, use another method: in_cart_product_total_quantity()
   *
   * @param integer|string product ID of item to check
   * @return decimal the quantity of the item
   */
  function get_quantity($products_id) {
    $this->notify('NOTIFIER_CART_GET_QUANTITY_START', array(), $products_id);
    if (isset($this->contents[$products_id])) {
      $this->notify('NOTIFIER_CART_GET_QUANTITY_END_QTY', array(), $products_id);
      return $this->contents[$products_id]['qty'];
    } else {
      $this->notify('NOTIFIER_CART_GET_QUANTITY_END_FALSE', $products_id);
      return 0;
    }
  }
  /**
   * Check whether a product exists in the cart
   *
   * @param mixed product ID of item to check
   * @return boolean
   */
  function in_cart($products_id) {
    $this->notify('NOTIFIER_CART_IN_CART_START', array(), $products_id);
    if (isset($this->contents[$products_id])) {
      $this->notify('NOTIFIER_CART_IN_CART_END_TRUE', array(), $products_id);
      return true;
    } else {
      $this->notify('NOTIFIER_CART_IN_CART_END_FALSE', $products_id);
      return false;
    }
  }
  /**
   * Remove an item from the cart
   *
   * @param string|int product ID of item to remove
   * @return void
   */
  function remove($products_id) {
    global $db;
    $this->notify('NOTIFIER_CART_REMOVE_START', array(), $products_id);
    unset($this->contents[$products_id]);
    // remove from database
    if (zen_is_logged_in() && !zen_in_guest_checkout()) {
      $sql = "DELETE FROM " . TABLE_CUSTOMERS_BASKET . "
              WHERE customers_id = " . (int)$_SESSION['customer_id'] . "
              AND products_id = '" . zen_db_input($products_id) . "'";
      $db->Execute($sql);

      $sql = "DELETE FROM " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . "
              WHERE customers_id = " . (int)$_SESSION['customer_id'] . "
              AND products_id = '" . zen_db_input($products_id) . "'";
      $db->Execute($sql);
    }

    // assign a temporary unique ID to the order contents to prevent hack attempts during the checkout procedure
    $this->cartID = $this->generate_cart_id();
    $this->notify('NOTIFIER_CART_REMOVE_END');
  }
  /**
   * Remove all products from the cart
   *
   * @return void
   */
  function remove_all() {
    $this->notify('NOTIFIER_CART_REMOVE_ALL_START');
    $this->reset();
    $this->notify('NOTIFIER_CART_REMOVE_ALL_END');
  }
  /**
   * Return a comma separated list of all products in the cart
   * NOTE: Not used in core ZC, but some plugins and shipping modules make use of it as a helper function
   *
   * @return string
   */
  function get_product_id_list() {
    if (!is_array($this->contents)) {
      return '';
    }
    $product_id_list = array();
    foreach($this->contents as $products_id => $data) {
      $product_id_list[] = $products_id;
    }
    return implode(',', $product_id_list);
  }
  /**
   * Calculate cart totals(price and weight)
   *
   * @return void
   */
  function calculate() {
    global $db, $currencies;
    $this->total = 0;
    $this->weight = 0;
    $this->total_before_discounts = 0;
    $decimalPlaces = $currencies->get_decimal_places($_SESSION['currency']);
    // shipping adjustment
    $this->free_shipping_item = 0;
    $this->free_shipping_price = 0;
    $this->free_shipping_weight = 0;
    $this->download_count = 0;
    if (!is_array($this->contents)) return 0;

// By default, Price Factor is based on Price and is called from function zen_get_attributes_price_factor
// Setting a define for ATTRIBUTES_PRICE_FACTOR_FROM_SPECIAL to 1 to calculate the Price Factor from Special rather than Price switches this to be based on Special, if it exists
    if (!defined('ATTRIBUTES_PRICE_FACTOR_FROM_SPECIAL')) define('ATTRIBUTES_PRICE_FACTOR_FROM_SPECIAL', 1);
    foreach($this->contents as $products_id => $data) {
      $total_before_discounts = 0;
      $freeShippingTotal = $productTotal = $totalOnetimeCharge = $totalOnetimeChargeNoDiscount = 0;
      $qty = $this->contents[$products_id]['qty'];

      // products price
      $product_query = "SELECT products_id, products_price, products_tax_class_id, products_weight,
                          products_priced_by_attribute, product_is_always_free_shipping, products_discount_type, products_discount_type_from,
                          products_virtual, products_model
                          FROM " . TABLE_PRODUCTS . "
                          WHERE products_id = " . (int)$products_id;

      if ($product = $db->Execute($product_query)) {
        $prid = $product->fields['products_id'];

        $this->notify('NOTIFY_CART_CALCULATE_PRODUCT_PRICE', $products_id, $product->fields);

        $products_tax = zen_get_tax_rate($product->fields['products_tax_class_id']);
        $products_price = $product->fields['products_price'];

        // adjusted count for free shipping
        if ($product->fields['product_is_always_free_shipping'] != 1 and $product->fields['products_virtual'] != 1) {
          $products_weight = $product->fields['products_weight'];
        } else {
          $products_weight = 0;
        }

        $special_price = zen_get_products_special_price($prid);
        if ($special_price and $product->fields['products_priced_by_attribute'] == 0) {
          $products_price = $special_price;
        } else {
          $special_price = 0;
        }

        if (zen_get_products_price_is_free($product->fields['products_id'])) {
          // no charge
          $products_price = 0;
        }

        // adjust price for discounts when priced by attribute
        if ($product->fields['products_priced_by_attribute'] == '1' and zen_has_product_attributes($product->fields['products_id'], 'false')) {
          if ($special_price) {
            $products_price = $special_price;
          } else {
            $products_price = $product->fields['products_price'];
          }
        } else {
          // discount qty pricing
          if ($product->fields['products_discount_type'] != '0') {
            $products_price = zen_get_products_discount_price_qty($product->fields['products_id'], $qty);
          }
        }

        // shipping adjustments for Product
        if (($product->fields['product_is_always_free_shipping'] == 1) or ($product->fields['products_virtual'] == 1) or (preg_match('/^GIFT/', addslashes($product->fields['products_model'])))) {
          $this->free_shipping_item += $qty;
          $freeShippingTotal += $products_price;
          $this->free_shipping_weight += ($qty * $product->fields['products_weight']);
        }

//        $this->total += zen_round(zen_add_tax($products_price, $products_tax),$currencies->get_decimal_places($_SESSION['currency'])) * $qty;
        $productTotal += $products_price;
        $this->weight += ($qty * $products_weight);

// ****** WARNING NEED TO ADD ATTRIBUTES AND QTY
        // calculate Product Price without Specials, Sales or Discounts
        $total_before_discounts += $product->fields['products_price'];
      }

      $adjust_downloads = 0;
      // attributes price
      $savedProductTotal = $productTotal;
      $attributesTotal = 0;
      if (isset($this->contents[$products_id]['attributes'])) {
        foreach($this->contents[$products_id]['attributes'] as $option => $value) {
          $productTotal = 0;
          $adjust_downloads ++;
          /*
          products_attributes_id, options_values_price, price_prefix,
          attributes_display_only, product_attribute_is_free,
          attributes_discounted
          */

          $attribute_price_query = "SELECT *
                                      FROM " . TABLE_PRODUCTS_ATTRIBUTES . "
                                      WHERE products_id = " . (int)$prid . "
                                      AND options_id = " . (int)$option . "
                                      AND options_values_id = " . (int)$value;

          $attribute_price = $db->Execute($attribute_price_query);

          if ($attribute_price->EOF) continue;

          $this->notify('NOTIFY_CART_CALCULATE_ATTRIBUTE_PRICE', $products_id, $attribute_price->fields);

          $new_attributes_price = 0;
        // calculate Product Price without Specials, Sales or Discounts
//          $new_attributes_price_before_discounts = 0;

          $discount_type_id = '';
          $sale_maker_discount = '';

          // bottom total
          if ($attribute_price->fields['product_attribute_is_free'] == '1' and zen_get_products_price_is_free((int)$prid)) {
            // no charge for attribute
          } else {
            // + or blank adds
            if ($attribute_price->fields['price_prefix'] == '-') {
// appears to confuse products priced by attributes
                if ($product->fields['product_is_always_free_shipping'] == '1' or $product->fields['products_virtual'] == '1') {
                  $shipping_attributes_price = zen_get_discount_calc($product->fields['products_id'], $attribute_price->fields['products_attributes_id'], $attribute_price->fields['options_values_price'], $qty);
                  $freeShippingTotal -= $shipping_attributes_price;
                }
              if ($attribute_price->fields['attributes_discounted'] == '1') {
                // calculate proper discount for attributes
                $new_attributes_price = zen_get_discount_calc($product->fields['products_id'], $attribute_price->fields['products_attributes_id'], $attribute_price->fields['options_values_price'], $qty);
                $productTotal -= $new_attributes_price;
              } else {
                $productTotal -= $attribute_price->fields['options_values_price'];
              }
        // calculate Product Price without Specials, Sales or Discounts
//            $this->total_before_discounts -= $attribute_price->fields['options_values_price'];
              $total_before_discounts -= $attribute_price->fields['options_values_price'];
            } else {
// appears to confuse products priced by attributes
                if ($product->fields['product_is_always_free_shipping'] == '1' or $product->fields['products_virtual'] == '1') {
                  $shipping_attributes_price = zen_get_discount_calc($product->fields['products_id'], $attribute_price->fields['products_attributes_id'], $attribute_price->fields['options_values_price'], $qty);
                  $freeShippingTotal += $shipping_attributes_price;
                }
              if ($attribute_price->fields['attributes_discounted'] == '1') {
                // calculate proper discount for attributes
                $new_attributes_price = zen_get_discount_calc($product->fields['products_id'], $attribute_price->fields['products_attributes_id'], $attribute_price->fields['options_values_price'] + (zen_get_products_price_is_priced_by_attributes($attribute_price->fields['products_id']) ? zen_products_lookup($attribute_price->fields['products_id'], 'products_price') : 0), $qty);
                $new_attributes_price = $new_attributes_price - (zen_get_products_price_is_priced_by_attributes($attribute_price->fields['products_id']) ? zen_products_lookup($attribute_price->fields['products_id'], 'products_price') : 0);
                $productTotal += $new_attributes_price;
              } else {
                $productTotal += $attribute_price->fields['options_values_price'];
              }
        // calculate Product Price without Specials, Sales or Discounts
              $total_before_discounts += $attribute_price->fields['options_values_price'];
            } // eof: attribute price
        // adjust for downloads
            // adjust products price
              $check_attribute = $attribute_price->fields['products_attributes_id'];
              $sql = "SELECT products_attributes_id
                      FROM " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . "
                      WHERE products_attributes_id = " . (int)$check_attribute;
              $check_download = $db->Execute($sql);
              if ($check_download->RecordCount()) {
            // count number of downloads
                $this->download_count += ($check_download->RecordCount() * $qty);
            // do not count download as free when set to product/download combo
                if ($adjust_downloads == 1 and $product->fields['product_is_always_free_shipping'] != 2) {
                  $freeShippingTotal += $products_price;
                  $this->free_shipping_item += $qty;
                }
            // adjust for attributes price
                $freeShippingTotal += $new_attributes_price;
              }

            ////////////////////////////////////////////////
            // calculate additional attribute charges
            $chk_price = zen_get_products_base_price($products_id);
            $chk_special = zen_get_products_special_price($products_id, false);
            // products_options_value_text
            if (ATTRIBUTES_ENABLED_TEXT_PRICES == 'true' && zen_get_attributes_type($attribute_price->fields['products_attributes_id']) == PRODUCTS_OPTIONS_TYPE_TEXT) {
              $text_words = zen_get_word_count_price($this->contents[$products_id]['attributes_values'][$attribute_price->fields['options_id']], $attribute_price->fields['attributes_price_words_free'], $attribute_price->fields['attributes_price_words']);
              $text_letters = zen_get_letters_count_price($this->contents[$products_id]['attributes_values'][$attribute_price->fields['options_id']], $attribute_price->fields['attributes_price_letters_free'], $attribute_price->fields['attributes_price_letters']);

              $productTotal += $text_letters;
              $productTotal += $text_words;
              if (($product->fields['product_is_always_free_shipping'] == 1) or ($product->fields['products_virtual'] == 1) or (preg_match('/^GIFT/', addslashes($product->fields['products_model'])))) {
                $freeShippingTotal += $text_letters;
                $freeShippingTotal += $text_words;
              }
        // calculate Product Price without Specials, Sales or Discounts
              $total_before_discounts += $text_letters;
              $total_before_discounts += $text_words;
            }

            // attributes_price_factor
            $added_charge = 0;
            if ($attribute_price->fields['attributes_price_factor'] > 0) {
//echo 'products_id: ' . $product->fields['products_id'] . ' Prices ' . '$chk_price: ' . $chk_price . ' $chk_special: ' . $chk_special . ' attributes_price_factor:' . $attribute_price->fields['attributes_price_factor'] . ' attributes_price_factor_offset: ' . $attribute_price->fields['attributes_price_factor_offset'] . '<br>';
              $added_charge = zen_get_attributes_price_factor($chk_price, $chk_special, $attribute_price->fields['attributes_price_factor'], $attribute_price->fields['attributes_price_factor_offset']);

              $productTotal += $added_charge;
              if (($product->fields['product_is_always_free_shipping'] == 1) or ($product->fields['products_virtual'] == 1) or (preg_match('/^GIFT/', addslashes($product->fields['products_model'])))) {
                $freeShippingTotal += $added_charge;
              }
        // calculate Product Price without Specials, Sales or Discounts
              $added_charge = zen_get_attributes_price_factor($chk_price, $chk_price, $attribute_price->fields['attributes_price_factor'], $attribute_price->fields['attributes_price_factor_offset']);
              $total_before_discounts += $added_charge;
            }

            // attributes_qty_prices
            $added_charge = 0;
            if ($attribute_price->fields['attributes_qty_prices'] != '') {
              $added_charge = zen_get_attributes_qty_prices_onetime($attribute_price->fields['attributes_qty_prices'], $qty);

              $productTotal += $added_charge;
              if (($product->fields['product_is_always_free_shipping'] == 1) or ($product->fields['products_virtual'] == 1) or (preg_match('/^GIFT/', addslashes($product->fields['products_model'])))) {
                $freeShippingTotal += $added_charge;
              }
        // calculate Product Price without Specials, Sales or Discounts
              $added_charge = zen_get_attributes_qty_prices_onetime($attribute_price->fields['attributes_qty_prices'], 1);
              $total_before_discounts += $attribute_price->fields['options_values_price'] + $added_charge;
            }

            //// one time charges
            // attributes_price_onetime
            if ($attribute_price->fields['attributes_price_onetime'] > 0) {
              $totalOnetimeCharge += $attribute_price->fields['attributes_price_onetime'];
        // calculate Product Price without Specials, Sales or Discounts
              $totalOnetimeChargeNoDiscount += $attribute_price->fields['attributes_price_onetime'];
            }

            // attributes_price_factor_onetime
            $added_charge = 0;
            if ($attribute_price->fields['attributes_price_factor_onetime'] > 0) {
              $chk_price = zen_get_products_base_price($products_id);
              $chk_special = zen_get_products_special_price($products_id, false);
              $added_charge = zen_get_attributes_price_factor($chk_price, $chk_special, $attribute_price->fields['attributes_price_factor_onetime'], $attribute_price->fields['attributes_price_factor_onetime_offset']);

              $totalOnetimeCharge += $added_charge;
        // calculate Product Price without Specials, Sales or Discounts
              $added_charge = zen_get_attributes_price_factor($chk_price, $chk_price, $attribute_price->fields['attributes_price_factor_onetime'], $attribute_price->fields['attributes_price_factor_onetime_offset']);
              $totalOnetimeChargeNoDiscount += $added_charge;
            }
            // attributes_qty_prices_onetime
            $added_charge = 0;
            if ($attribute_price->fields['attributes_qty_prices_onetime'] != '') {
              $added_charge = zen_get_attributes_qty_prices_onetime($attribute_price->fields['attributes_qty_prices_onetime'], $qty);
              $totalOnetimeCharge += $added_charge;
        // calculate Product Price without Specials, Sales or Discounts
              $added_charge = zen_get_attributes_qty_prices_onetime($attribute_price->fields['attributes_qty_prices_onetime'], 1);
              $totalOnetimeChargeNoDiscount += $added_charge;
            }
            ////////////////////////////////////////////////
          }
          $attributesTotal += zen_round($productTotal, $decimalPlaces);
        } // eof while
      } // attributes price
      $productTotal = $savedProductTotal + $attributesTotal;
      // attributes weight
      if (isset($this->contents[$products_id]['attributes'])) {
        foreach($this->contents[$products_id]['attributes'] as $option => $value) {
          $attribute_weight_query = "SELECT products_attributes_weight, products_attributes_weight_prefix
                                       FROM " . TABLE_PRODUCTS_ATTRIBUTES . "
                                       WHERE products_id = " . (int)$prid . "
                                       AND options_id = " . (int)$option . "
                                       AND options_values_id = " . (int)$value;

          $attribute_weight = $db->Execute($attribute_weight_query);

          if ($attribute_weight->EOF) {
            continue;
          }

          $this->notify('NOTIFY_CART_CALCULATE_ATTRIBUTE_WEIGHT',
                        array('products_id' => $products_id, 'options_id' => $option),
                        $attribute_weight->fields);

          // adjusted count for free shipping
          if ($product->fields['product_is_always_free_shipping'] != 1) {
            $new_attributes_weight = $attribute_weight->fields['products_attributes_weight'];
          } else {
            $new_attributes_weight = 0;
          }

          // shipping adjustments for Attributes
          if (($product->fields['product_is_always_free_shipping'] == 1) or ($product->fields['products_virtual'] == 1) or (preg_match('/^GIFT/', addslashes($product->fields['products_model'])))) {
            if ($attribute_weight->fields['products_attributes_weight_prefix'] == '-') {
              $this->free_shipping_weight -= ($qty * $attribute_weight->fields['products_attributes_weight']);
            } else {
              $this->free_shipping_weight += ($qty * $attribute_weight->fields['products_attributes_weight']);
            }
          }

          // + or blank adds
          if ($attribute_weight->fields['products_attributes_weight_prefix'] == '-') {
            $this->weight -= $qty * $new_attributes_weight;
          } else {
            $this->weight += $qty * $new_attributes_weight;
          }
        }
      } // attributes weight

/*
// uncomment for odd shipping requirements needing this:

      // if 0 weight defined as free shipping adjust for functions free_shipping_price and free_shipping_item
      if (($product->fields['products_weight'] == 0 && ORDER_WEIGHT_ZERO_STATUS == 1) && !($product->fields['products_virtual'] == 1) && !(preg_match('/^GIFT/', addslashes($product->fields['products_model']))) && !($product->fields['product_is_always_free_shipping'] == 1)) {
        $freeShippingTotal += $products_price;
        $this->free_shipping_item += $qty;
      }
*/
//echo 'shopping_cart class Price: ' . $productTotal . ' qty: ' . $qty . '<br>';

      $this->total += zen_round(zen_add_tax($productTotal, $products_tax), $decimalPlaces) * $qty;
      $this->total += zen_round(zen_add_tax($totalOnetimeCharge, $products_tax), $decimalPlaces);
      $this->free_shipping_price += zen_round(zen_add_tax($freeShippingTotal, $products_tax), $decimalPlaces) * $qty;
      if (($product->fields['product_is_always_free_shipping'] == 1) or ($product->fields['products_virtual'] == 1) or (preg_match('/^GIFT/', addslashes($product->fields['products_model'])))) {
        $this->free_shipping_price += zen_round(zen_add_tax($totalOnetimeCharge, $products_tax), $decimalPlaces);
      }

// ******* WARNING ADD ONE TIME ATTRIBUTES, PRICE FACTOR
      // calculate Product Price without Specials, Sales or Discounts
//echo 'Product Attribute before: ' . $new_attributes_price_before_discounts . '<br>';
      $total_before_discounts = $total_before_discounts * $qty;
      $total_before_discounts += $totalOnetimeChargeNoDiscount;
      $this->total_before_discounts += $total_before_discounts;
    }
  }
  /**
   * Calculate price of attributes for a given item
   *
   * @param mixed the product ID of the item to check
   * @return decimal the price of the item's attributes
   */
  function attributes_price($products_id) {
    global $db, $currencies;

    $total_attributes_price = 0;
    $qty = $this->contents[$products_id]['qty'];

    $this->notify('NOTIFY_CART_ATTRIBUTES_PRICE_START', $products_id);

    if (isset($this->contents[$products_id]['attributes'])) {

      if(!defined('ATTRIBUTES_PRICE_FACTOR_FROM_SPECIAL')) define('ATTRIBUTES_PRICE_FACTOR_FROM_SPECIAL', 1);
      foreach($this->contents[$products_id]['attributes'] as $option => $value) {
        $attributes_price = 0;
        $attribute_price_query = "SELECT *
                                    FROM " . TABLE_PRODUCTS_ATTRIBUTES . "
                                    WHERE products_id = " . (int)$products_id . "
                                    AND options_id = " . (int)$option . "
                                    AND options_values_id = " . (int)$value;

        $attribute_price = $db->Execute($attribute_price_query);

        if ($attribute_price->EOF) {
          continue;
        }

        $this->notify('NOTIFY_CART_ATTRIBUTES_PRICE_NEXT', $products_id, $attribute_price->fields);

        $new_attributes_price = 0;
        $discount_type_id = '';
        $sale_maker_discount = '';

        if ($attribute_price->fields['product_attribute_is_free'] == '1' && zen_get_products_price_is_free((int)$products_id)) {
          // no charge
        } else {
          // + or blank adds
          if ($attribute_price->fields['price_prefix'] == '-') {
            // calculate proper discount for attributes
            if ($attribute_price->fields['attributes_discounted'] == '1') {
              $discount_type_id = '';
              $sale_maker_discount = '';
              $new_attributes_price = zen_get_discount_calc($products_id, $attribute_price->fields['products_attributes_id'], $attribute_price->fields['options_values_price'], $qty);
              $attributes_price -= ($new_attributes_price);
            } else {
              $attributes_price -= $attribute_price->fields['options_values_price'];
            }
          } else {
            if ($attribute_price->fields['attributes_discounted'] == '1') {
              // calculate proper discount for attributes
              $discount_type_id = '';
              $sale_maker_discount = '';
              $new_attributes_price = zen_get_discount_calc($products_id, $attribute_price->fields['products_attributes_id'], $attribute_price->fields['options_values_price'] + (zen_get_products_price_is_priced_by_attributes($attribute_price->fields['products_id']) ? zen_products_lookup($attribute_price->fields['products_id'], 'products_price') : 0.0), $qty);
              $new_attributes_price = $new_attributes_price - (zen_get_products_price_is_priced_by_attributes($attribute_price->fields['products_id']) ? zen_products_lookup($attribute_price->fields['products_id'], 'products_price') : 0);
              $attributes_price += ($new_attributes_price);
            } else {
              $attributes_price += $attribute_price->fields['options_values_price'];
            }
          }

          //////////////////////////////////////////////////
          // calculate additional charges
          // products_options_value_text
          if (ATTRIBUTES_ENABLED_TEXT_PRICES == 'true' && zen_get_attributes_type($attribute_price->fields['products_attributes_id']) == PRODUCTS_OPTIONS_TYPE_TEXT) {
            $text_words = zen_get_word_count_price($this->contents[$products_id]['attributes_values'][$attribute_price->fields['options_id']], $attribute_price->fields['attributes_price_words_free'], $attribute_price->fields['attributes_price_words']);
            $text_letters = zen_get_letters_count_price($this->contents[$products_id]['attributes_values'][$attribute_price->fields['options_id']], $attribute_price->fields['attributes_price_letters_free'], $attribute_price->fields['attributes_price_letters']);
            $attributes_price += $text_letters;
            $attributes_price += $text_words;
          }
          // attributes_price_factor
          $added_charge = 0;
          if ($attribute_price->fields['attributes_price_factor'] > 0) {
            $chk_price = zen_get_products_base_price($products_id);
            $chk_special = zen_get_products_special_price($products_id, false);
            $added_charge = zen_get_attributes_price_factor($chk_price, $chk_special, $attribute_price->fields['attributes_price_factor'], $attribute_price->fields['attributes_price_factor_offset']);
            $attributes_price += $added_charge;
          }
          // attributes_qty_prices
          $added_charge = 0;
          if ($attribute_price->fields['attributes_qty_prices'] != '') {
            $chk_price = zen_get_products_base_price($products_id);
            $chk_special = zen_get_products_special_price($products_id, false);
            $added_charge = zen_get_attributes_qty_prices_onetime($attribute_price->fields['attributes_qty_prices'], $this->contents[$products_id]['qty']);
            $attributes_price += $added_charge;
          }

          //////////////////////////////////////////////////
        }
        // Validate Attributes
        if ($attribute_price->fields['attributes_display_only']) {
          $_SESSION['valid_to_checkout'] = false;
          $_SESSION['cart_errors'] .= zen_get_products_name($attribute_price->fields['products_id'], $_SESSION['languages_id']) . ERROR_PRODUCT_OPTION_SELECTION . '<br />';
        }
        /*
        //// extra testing not required on text attribute this is done in application_top before it gets to the cart
        if ($attribute_price->fields['attributes_required']) {
        $_SESSION['valid_to_checkout'] = false;
        $_SESSION['cart_errors'] .= zen_get_products_name($attribute_price->fields['products_id'], $_SESSION['languages_id'])  . ERROR_PRODUCT_OPTION_SELECTION . '<br />';
        }
        */
        $total_attributes_price += zen_round($attributes_price, $currencies->get_decimal_places($_SESSION['currency']));
      }
    }

    return $total_attributes_price;
  }
  /**
   * Calculate one-time price of attributes for a given item
   *
   * @param mixed the product ID of the item to check
   * @param decimal item quantity
   * @return decimal the price of the items attributes
   */
  function attributes_price_onetime_charges($products_id, $qty) {
    global $db;

    $attributes_price_onetime = 0;

    $this->notify('NOTIFY_CART_ATTRIBUTES_PRICE_ONETIME_CHARGES_START', $products_id);

    if (isset($this->contents[$products_id]['attributes'])) {
      foreach($this->contents[$products_id]['attributes'] as $option => $value) {

        $attribute_price_query = "SELECT *
                                    FROM " . TABLE_PRODUCTS_ATTRIBUTES . "
                                    WHERE products_id = " . (int)$products_id . "
                                    AND options_id = " . (int)$option . "
                                    AND options_values_id = " . (int)$value;

        $attribute_price = $db->Execute($attribute_price_query);

        if ($attribute_price->EOF) {
          continue;
        }

        $this->notify('NOTIFY_CART_ATTRIBUTES_PRICE_ONETIME_CHARGES_NEXT', $products_id, $attribute_price->fields);

        $new_attributes_price = 0;
        $discount_type_id = '';
        $sale_maker_discount = '';

        if ($attribute_price->fields['product_attribute_is_free'] == '1' and zen_get_products_price_is_free((int)$products_id)) {
          // no charge
        } else {
          $discount_type_id = '';
          $sale_maker_discount = '';
          $new_attributes_price = zen_get_discount_calc($products_id, $attribute_price->fields['products_attributes_id'], $attribute_price->fields['options_values_price'], $qty);

          //////////////////////////////////////////////////
          // calculate additional one time charges
          //// one time charges
          // attributes_price_onetime
          if ($attribute_price->fields['attributes_price_onetime'] > 0) {
            if ((int)$products_id != $products_id) {
              die('I DO NOT MATCH ' . $products_id);
            }
            $attributes_price_onetime += $attribute_price->fields['attributes_price_onetime'];
          }
          // attributes_price_factor_onetime
          $added_charge = 0;
          if ($attribute_price->fields['attributes_price_factor_onetime'] > 0) {
            $chk_price = zen_get_products_base_price($products_id);
            $chk_special = zen_get_products_special_price($products_id, false);
            $added_charge = zen_get_attributes_price_factor($chk_price, $chk_special, $attribute_price->fields['attributes_price_factor_onetime'], $attribute_price->fields['attributes_price_factor_onetime_offset']);

            $attributes_price_onetime += $added_charge;
          }
          // attributes_qty_prices_onetime
          $added_charge = 0;
          if ($attribute_price->fields['attributes_qty_prices_onetime'] != '') {
            $chk_price = zen_get_products_base_price($products_id);
            $chk_special = zen_get_products_special_price($products_id, false);
            $added_charge = zen_get_attributes_qty_prices_onetime($attribute_price->fields['attributes_qty_prices_onetime'], $qty);
            $attributes_price_onetime += $added_charge;
          }

          //////////////////////////////////////////////////
        }
      }
    }

    return $attributes_price_onetime;
  }
  /**
   * Calculate weight of attributes for a given item
   *
   * @param mixed the product ID of the item to check
   * @return decimal the weight of the items attributes
   */
  function attributes_weight($products_id) {
    global $db;

    $attribute_weight = 0;

    if (isset($this->contents[$products_id]['attributes'])) {

      $this->notify('NOTIFY_CART_ATTRIBUTES_WEIGHT_START', $products_id);

      foreach($this->contents[$products_id]['attributes'] as $option => $value) {
        $attribute_weight_query = "SELECT products_attributes_weight, products_attributes_weight_prefix
                                    FROM " . TABLE_PRODUCTS_ATTRIBUTES . "
                                    WHERE products_id = " . (int)$products_id . "
                                    AND options_id = " . (int)$option . "
                                    AND options_values_id = " . (int)$value;

        $attribute_weight_info = $db->Execute($attribute_weight_query);

        if ($attribute_weight_info->EOF) {
          continue;
        }

        $this->notify('NOTIFY_CART_ATTRIBUTES_WEIGHT_NEXT', $products_id, $attribute_weight_info->fields);

        // adjusted count for free shipping
        $product = $db->Execute("SELECT products_id, product_is_always_free_shipping
                          FROM " . TABLE_PRODUCTS . "
                          WHERE products_id = " . (int)$products_id);

        if ($product->fields['product_is_always_free_shipping'] != 1) {
          $new_attributes_weight = $attribute_weight_info->fields['products_attributes_weight'];
        } else {
          $new_attributes_weight = 0;
        }

        // + or blank adds
        if ($attribute_weight_info->fields['products_attributes_weight_prefix'] == '-') {
          $attribute_weight -= $new_attributes_weight;
        } else {
          $attribute_weight += $attribute_weight_info->fields['products_attributes_weight'];
        }
      }
    }

    return $attribute_weight;
  }
  /**
   * Get all products in the cart
   *
   * @param boolean whether to also check if cart contents are valid
   * @return array
   */
  function get_products($check_for_valid_cart = false) {
    global $db;

    $this->notify('NOTIFIER_CART_GET_PRODUCTS_START', array(), $check_for_valid_cart);

    if (!is_array($this->contents)) return false;

    $products_array = array();
    foreach($this->contents as $products_id => $data) {
      $products_query = "SELECT p.*, pd.products_name
                           FROM " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd
                           WHERE p.products_id = '" . (int)$products_id . "'
                           AND pd.products_id = p.products_id
                           AND pd.language_id = " . (int)$_SESSION['languages_id'] . " LIMIT 1";

      $products = $db->Execute($products_query);
      if (!$products->EOF) {
        $this->notify('NOTIFY_CART_GET_PRODUCTS_NEXT', $products_id, $products->fields);

        $prid = $products->fields['products_id'];
        $products_price = $products->fields['products_price'];
        //fix here
        /*
        $special_price = zen_get_products_special_price($prid);
        if ($special_price) {
        $products_price = $special_price;
        }
        */
        $special_price = zen_get_products_special_price($prid);
        if ($special_price and $products->fields['products_priced_by_attribute'] == 0) {
          $products_price = $special_price;
        } else {
          $special_price = 0;
        }

        if (zen_get_products_price_is_free($products->fields['products_id'])) {
          // no charge
          $products_price = 0;
        }

        // adjust price for discounts when priced by attribute
        if ($products->fields['products_priced_by_attribute'] == '1' and zen_has_product_attributes($products->fields['products_id'], 'false')) {
          if ($special_price) {
            $products_price = $special_price;
          } else {
            $products_price = $products->fields['products_price'];
          }
        } else {
          // discount qty pricing
          if ($products->fields['products_discount_type'] != '0') {
            $products_price = zen_get_products_discount_price_qty($products->fields['products_id'], $this->contents[$products_id]['qty']);
          }
        }

// validate cart contents for checkout
        if ($check_for_valid_cart == true) {
          if (empty($this->flag_duplicate_quantity_msgs_set['keep'])) $this->flag_duplicate_quantity_msgs_set = array();
          $fix_once = 0;
          // Check products_status if not already
          $check_status = $products->fields['products_status'];
          if ( $check_status == 0 ) {
            $fix_once ++;
            $_SESSION['valid_to_checkout'] = false;
            $_SESSION['cart_errors'] .= ERROR_PRODUCT . $products->fields['products_name'] . ERROR_PRODUCT_STATUS_SHOPPING_CART . '<br />';
            $this->remove($products_id);
          } else {
            if (isset($this->contents[$products_id]['attributes'])) {
              $chkcount = 0;
              foreach($this->contents[$products_id]['attributes'] as $value) {
                $chkcount ++;
                $chk_attributes_exist_query = "SELECT products_id
                                          FROM " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                          WHERE pa.products_id = " . (int)$products_id . "
                                          AND pa.options_values_id = " . (int)$value;

                $chk_attributes_exist = $db->Execute($chk_attributes_exist_query);
//echo 'what is it: ' . ' : ' . $products_id . ' - ' . $value . ' records: ' . $chk_attributes_exist->RecordCount() . ' vs ' . print_r($this->contents[$products_id]) . '<br>';
                if ($chk_attributes_exist->EOF) {
                  $fix_once ++;
                  $_SESSION['valid_to_checkout'] = false;
                  $chk_product_attributes = $db->Execute("SELECT products_status FROM " . TABLE_PRODUCTS . " WHERE products_status = 1 AND products_id = " . (int)$products->fields["products_id"] . " LIMIT 1");
                  if (!$chk_product_attributes->EOF && $chk_product_attributes->fields['products_status'] == 1) {
                    $chk_products_link = '<a href="' . zen_href_link(zen_get_info_page($products->fields["products_id"]), 'cPath=' . zen_get_generated_category_path_rev($products->fields["master_categories_id"]) . '&products_id=' . $products->fields["products_id"]) . '">' . $products->fields['products_name'] . '</a>';
                  } else {
                    $chk_products_link = $products->fields['products_name'];
                  }
                  $_SESSION['cart_errors'] .= ERROR_PRODUCT_ATTRIBUTES . $chk_products_link . ERROR_PRODUCT_STATUS_SHOPPING_CART_ATTRIBUTES . '<br />';
                  $this->remove($products_id);
                  break;
                }
              }
            }
          }

          // check only if valid products_status
          if ($fix_once == 0) {
            $check_quantity = $this->contents[$products_id]['qty'];
            $check_quantity_min = $products->fields['products_quantity_order_min'];
            // Check quantity min
            if ($new_check_quantity = $this->in_cart_mixed($prid) ) {
              $check_quantity = $new_check_quantity;
            }
          }

          // Check Quantity Max if not already an error on Minimum
          if ($fix_once == 0) {
            if ($products->fields['products_quantity_order_max'] != 0 && $check_quantity > $products->fields['products_quantity_order_max'] && !isset($this->flag_duplicate_quantity_msgs_set[(int)$prid]['max'])) {
              $fix_once ++;
              $_SESSION['valid_to_checkout'] = false;
              $_SESSION['cart_errors'] .= ERROR_PRODUCT . $products->fields['products_name'] . ERROR_PRODUCT_QUANTITY_MAX_SHOPPING_CART . ERROR_PRODUCT_QUANTITY_ORDERED . $check_quantity  . ' <span class="alertBlack">' . zen_get_products_quantity_min_units_display((int)$prid, false, true) . '</span> ' . '<br />';
              $this->flag_duplicate_quantity_msgs_set[(int)$prid]['max'] = true;
            }
          }

          if ($fix_once == 0) {
            if ($check_quantity < $check_quantity_min && !isset($this->flag_duplicate_quantity_msgs_set[(int)$prid]['min'])) {
              $fix_once ++;
              $_SESSION['valid_to_checkout'] = false;
              $_SESSION['cart_errors'] .= ERROR_PRODUCT . $products->fields['products_name'] . ERROR_PRODUCT_QUANTITY_MIN_SHOPPING_CART . ERROR_PRODUCT_QUANTITY_ORDERED . $check_quantity  . ' <span class="alertBlack">' . zen_get_products_quantity_min_units_display((int)$prid, false, true) . '</span> ' . '<br />';
              $this->flag_duplicate_quantity_msgs_set[(int)$prid]['min'] = true;
            }
          }

          // Check Quantity Units if not already an error on Quantity Minimum
          if ($fix_once == 0) {
            $check_units = $products->fields['products_quantity_order_units'];
            if ( fmod_round($check_quantity,$check_units) != 0 && !isset($this->flag_duplicate_quantity_msgs_set[(int)$prid]['units']) ) {
              $_SESSION['valid_to_checkout'] = false;
              $_SESSION['cart_errors'] .= ERROR_PRODUCT . $products->fields['products_name'] . ERROR_PRODUCT_QUANTITY_UNITS_SHOPPING_CART . ERROR_PRODUCT_QUANTITY_ORDERED . $check_quantity  . ' <span class="alertBlack">' . zen_get_products_quantity_min_units_display((int)$prid, false, true) . '</span> ' . '<br />';
              $this->flag_duplicate_quantity_msgs_set[(int)$prid]['units'] = true;
            }
          }

          // Verify Valid Attributes
        }

        // convert quantity to proper decimals
        if (QUANTITY_DECIMALS != 0) {
          $fix_qty = $this->contents[$products_id]['qty'];
          switch (true) {
            case (!strstr($fix_qty, '.')):
            $new_qty = $fix_qty;
            break;
            default:
            $new_qty = preg_replace('/[0]+$/','',$this->contents[$products_id]['qty']);
            break;
          }
        } else {
          $new_qty = $this->contents[$products_id]['qty'];
        }
        $check_unit_decimals = zen_get_products_quantity_order_units((int)$products->fields['products_id']);
        if (strstr($check_unit_decimals, '.')) {
          $new_qty = round($new_qty, QUANTITY_DECIMALS);
        } else {
          $new_qty = round($new_qty, 0);
        }

        $products_array[] = array('id' => $products_id,
                                  'category' => $products->fields['master_categories_id'],
                                  'name' => $products->fields['products_name'],
                                  'model' => $products->fields['products_model'],
                                  'image' => $products->fields['products_image'],
                                  'price' => ($products->fields['product_is_free'] =='1' ? 0 : $products_price),
                                  'quantity' => $new_qty,
                                  'weight' => $products->fields['products_weight'] + $this->attributes_weight($products_id),
                                  'final_price' => ($products_price + $this->attributes_price($products_id)),
                                  'onetime_charges' => ($this->attributes_price_onetime_charges($products_id, $new_qty)),
                                  'tax_class_id' => $products->fields['products_tax_class_id'],
                                  'attributes' => (isset($this->contents[$products_id]['attributes']) ? $this->contents[$products_id]['attributes'] : ''),
                                  'attributes_values' => (isset($this->contents[$products_id]['attributes_values']) ? $this->contents[$products_id]['attributes_values'] : ''),
                                  'products_priced_by_attribute' => $products->fields['products_priced_by_attribute'],
                                  'product_is_free' => $products->fields['product_is_free'],
                                  'products_discount_type' => $products->fields['products_discount_type'],
                                  'products_discount_type_from' => $products->fields['products_discount_type_from'],
                                  'products_virtual' => (int)$products->fields['products_virtual'],
                                  'product_is_always_free_shipping' => (int)$products->fields['product_is_always_free_shipping'],
                                  'products_quantity_order_min' => (float)$products->fields['products_quantity_order_min'],
                                  'products_quantity_order_units' => (float)$products->fields['products_quantity_order_units'],
                                  'products_quantity_order_max' => (float)$products->fields['products_quantity_order_max'],
                                  'products_quantity_mixed' => (int)$products->fields['products_quantity_mixed'],
                                  'products_mixed_discount_quantity' => (int)$products->fields['products_mixed_discount_quantity'],
                                  );
      }
    }
    $this->notify('NOTIFIER_CART_GET_PRODUCTS_END', array(), $products_array);
    return $products_array;
  }
  /**
   * Calculate total price of items in cart
   *
   * @return decimal Total Price
   */
  function show_total() {
    $this->notify('NOTIFIER_CART_SHOW_TOTAL_START');
    $this->calculate();
    $this->notify('NOTIFIER_CART_SHOW_TOTAL_END');
    return $this->total;
  }

  /**
   * Calculate total price of items in cart before Specials, Sales, Discounts
   *
   * @return decimal Total Price before Specials, Sales, Discounts
   */
  function show_total_before_discounts() {
    $this->notify('NOTIFIER_CART_SHOW_TOTAL_BEFORE_DISCOUNT_START');
    $this->calculate();
    $this->notify('NOTIFIER_CART_SHOW_TOTAL_BEFORE_DISCOUNT_END');
    return $this->total_before_discounts;
  }

  /**
   * Calculate total weight of items in cart
   *
   * @return decimal Total Weight
   */
  function show_weight() {
    $this->calculate();
    return $this->weight;
  }
  /**
   * Generate a cart ID, used to ensure contents have not been altered unexpectedly
   *
   * @param length of ID to generate
   * @return string cart ID
   */
  function generate_cart_id($length = 5) {
    return zen_create_random_value($length, 'digits');
  }
  /**
   * Calculate the content type of a cart
   *
   * @param boolean whether to test for Gift Vouchers only
   * @return string
   */
  function get_content_type($gv_only = 'false') {
    global $db;

    $this->content_type = false;
    $gift_voucher = 0;

    if ( $this->count_contents() > 0 ) {
      foreach($this->contents as $products_id => $data) {
        $free_ship_check = $db->Execute("SELECT products_virtual, products_model, products_price, product_is_always_free_shipping FROM " . TABLE_PRODUCTS . " WHERE products_id = " . (int)$products_id);
        $virtual_check = false;
        if (preg_match('/^GIFT/', addslashes($free_ship_check->fields['products_model']))) {
// @TODO - fix GIFT price in cart special/attribute
          $gift_special = zen_get_products_special_price(zen_get_prid($products_id), true);
          $gift_pba = zen_get_products_price_is_priced_by_attributes(zen_get_prid($products_id));
//echo '$products_id: ' . zen_get_prid($products_id) . ' price: ' . ($free_ship_check->fields['products_price'] + $this->attributes_price($products_id)) . ' vs special price: ' . $gift_special . ' qty: ' . $this->contents[$products_id]['qty'] . ' PBA: ' . ($gift_pba ? 'YES' : 'NO') . '<br>';
          if (!$gift_pba && $gift_special !=0 && $gift_special != $free_ship_check->fields['products_price']) {
            $gift_voucher += ($gift_special * $this->contents[$products_id]['qty']);
          } else {
          $gift_voucher += ($free_ship_check->fields['products_price'] + $this->attributes_price($products_id)) * $this->contents[$products_id]['qty'];
          }
        }
        // product_is_always_free_shipping = 2 is special requires shipping
        // Example: Product with download
        if (isset($this->contents[$products_id]['attributes']) and $free_ship_check->fields['product_is_always_free_shipping'] != 2) {
          foreach($this->contents[$products_id]['attributes'] as $value) {
            $virtual_check_query = "SELECT COUNT(*) as total
                                      FROM " . TABLE_PRODUCTS_ATTRIBUTES . " pa, "
                                      . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad
                                      WHERE pa.products_id = " . (int)$products_id . "
                                      AND pa.options_values_id = " . (int)$value . "
                                      AND pa.products_attributes_id = pad.products_attributes_id";

            $virtual_check = $db->Execute($virtual_check_query);

            if ($virtual_check->fields['total'] > 0) {
              switch ($this->content_type) {
                case 'physical':
                  $this->content_type = 'mixed';
                  if ($gv_only == 'true') {
                    return $gift_voucher;
                  } else {
                    return $this->content_type;
                  }
                  break;
                default:
                  $this->content_type = 'virtual';
                  break;
              }
            } else {
              switch ($this->content_type) {
                case 'virtual':
                  if ($free_ship_check->fields['products_virtual'] == '1') {
                   $this->content_type = 'virtual';
                  } else {
                    $this->content_type = 'mixed';
                    if ($gv_only == 'true') {
                      return $gift_voucher;
                    } else {
                      return $this->content_type;
                    }
                  }
                break;
                case 'physical':
                if ($free_ship_check->fields['products_virtual'] == '1') {
                  $this->content_type = 'mixed';
                  if ($gv_only == 'true') {
                    return $gift_voucher;
                  } else {
                    return $this->content_type;
                  }
                } else {
                  $this->content_type = 'physical';
                }
                break;
                default:
                if ($free_ship_check->fields['products_virtual'] == '1') {
                  $this->content_type = 'virtual';
                } else {
                  $this->content_type = 'physical';
                }
              }
            }
          }
        } else {
          switch ($this->content_type) {
            case 'virtual':
            if ($free_ship_check->fields['products_virtual'] == '1') {
              $this->content_type = 'virtual';
            } else {
              $this->content_type = 'mixed';
              if ($gv_only == 'true') {
                return $gift_voucher;
              } else {
                return $this->content_type;
              }
            }
            break;
            case 'physical':
            if ($free_ship_check->fields['products_virtual'] == '1') {
              $this->content_type = 'mixed';
              if ($gv_only == 'true') {
                return $gift_voucher;
              } else {
                return $this->content_type;
              }
            } else {
              $this->content_type = 'physical';
            }
            break;
            default:
            if ($free_ship_check->fields['products_virtual'] == '1') {
              $this->content_type = 'virtual';
            } else {
              $this->content_type = 'physical';
            }
          }
        }
      }
    } else {
      $this->content_type = 'physical';
    }

    if ($gv_only == 'true') {
      return $gift_voucher;
    } else {
      return $this->content_type;
    }
  }
  /**
   * Unserialize a cart object
   *
   * @deprecated
   * @private
   */
  function unserialize($broken) {
    foreach($broken as $kv) {
      $key=$kv['key'];
      if (gettype($this->$key)!="user function")
      $this->$key=$kv['value'];
    }
  }
  /**
   * Calculate item quantity, bounded by the mixed/min units settings
   *
   * @param boolean product id of item to check
   * @return decimal
   */
  function in_cart_mixed($products_id) {
    global $db;
    // if nothing is in cart return 0
    if (!is_array($this->contents)) return 0;

    // check if mixed is on
    $product = $db->Execute("SELECT products_id, products_quantity_mixed FROM " . TABLE_PRODUCTS . " WHERE products_id=" . (int)$products_id . " limit 1");

    // if mixed attributes is off return qty for current attribute selection
    if ($product->fields['products_quantity_mixed'] == '0') {
      return $this->get_quantity($products_id);
    }

    // compute total quantity regardless of attributes
    $in_cart_mixed_qty = 0;
    $chk_products_id= zen_get_prid($products_id);

// added for new code - Ajeh
    global $messageStack;

    $check_contents = $this->contents;
    foreach($check_contents as $products_id => $data) {
      $test_id = zen_get_prid($products_id);
//$messageStack->add_session('header', 'Product: ' . $products_id . ' test_id: ' . $test_id . '<br>', 'error');
      if ($test_id == $chk_products_id) {
//$messageStack->add_session('header', 'MIXED: ' . $products_id . ' test_id: ' . $test_id . ' qty:' . $check_contents[$products_id]['qty'] . ' in_cart_mixed_qty: ' . $in_cart_mixed_qty . '<br><br>', 'error');
        $in_cart_mixed_qty += $check_contents[$products_id]['qty'];
      }
    }
//$messageStack->add_session('header', 'FINAL: in_cart_mixed_qty: ' . 'PRODUCT: ' . $test_id . ' in cart:' . $in_cart_mixed_qty . '<br><br>', 'error');

    return $in_cart_mixed_qty;
  }
  /**
   * Calculate item quantity, bounded by the mixed/min units settings
   *
   * @param boolean product id of item to check
   * @return decimal
   */
  function in_cart_mixed_discount_quantity($products_id) {
    global $db;
    // if nothing is in cart return 0
    if (!is_array($this->contents)) return 0;

    // check if mixed is on
    //      $product = $db->Execute("select products_id, products_mixed_discount_quantity from " . TABLE_PRODUCTS . " where products_id='" . (int)$products_id . "' limit 1");
    $product = $db->Execute("SELECT products_id, products_mixed_discount_quantity FROM " . TABLE_PRODUCTS . " WHERE products_id=" . (int)$products_id . " limit 1");

    // if mixed attributes is off return qty for current attribute selection
    if ($product->fields['products_mixed_discount_quantity'] == '0') {
      return $this->get_quantity($products_id);
    }

    // compute total quantity regardless of attributes
    $in_cart_mixed_qty_discount_quantity = 0;
    $chk_products_id= zen_get_prid($products_id);

    $check_contents = $this->contents;
    foreach($check_contents as $products_id => $data) {
      $test_id = zen_get_prid($products_id);
      if ($test_id == $chk_products_id) {
        $in_cart_mixed_qty_discount_quantity += $check_contents[$products_id]['qty'];
      }
    }
    return $in_cart_mixed_qty_discount_quantity;
  }
  /**
   * Calculate the number of items in a cart based on an abitrary property
   *
   * $check_what is the fieldname example: 'products_is_free'
   * $check_value is the value being tested for - default is 1
   * Syntax: $_SESSION['cart']->in_cart_check('product_is_free','1');
   *
   * @param string product field to check
   * @param mixed value to check for
   * @return integer number of items matching constraint
   */
  function in_cart_check($check_what, $check_value='1') {
    global $db;
    // if nothing is in cart return 0
    if (!is_array($this->contents)) return 0;

    // compute total quantity for field
    $in_cart_check_qty=0;

    foreach($this->contents as $products_id => $data) {
      $testing_id = zen_get_prid($products_id);
      // check if field it true
      $product_check = $db->Execute("SELECT " . $check_what . " AS check_it FROM " . TABLE_PRODUCTS . " WHERE products_id=" . (int)$testing_id . " LIMIT 1");
      if ($product_check->fields['check_it'] == $check_value) {
        $in_cart_check_qty += $this->contents[$products_id]['qty'];
      }
    }
    return $in_cart_check_qty;
  }
  /**
   * Check whether cart contains only Gift Vouchers
   *
   * @return mixed value of Gift Vouchers in cart
   */
  function gv_only() {
    $gift_voucher = $this->get_content_type(true);
    return $gift_voucher;
  }
  /**
   * Return the number of free shipping items in the cart
   *
   * @return decimal
   */
  function free_shipping_items() {
    $this->calculate();
    return $this->free_shipping_item;
  }
  /**
   * Return the total price of free shipping items in the cart
   *
   * @return decimal
   */
  function free_shipping_prices() {
    $this->calculate();
    return $this->free_shipping_price;
  }
  /**
   * Return the total weight of free shipping items in the cart
   *
   * @return decimal
   */
  function free_shipping_weight() {
    $this->calculate();
    return $this->free_shipping_weight;
  }

  /**
   * Return the total number of downloads in the cart
   *
   * @return decimal
   */
  function download_counts() {
    $this->calculate();
    return $this->download_count;
  }

  /**
   * Handle updateProduct cart Action
   *
   * @param string $goto forward destination
   * @param array|string $parameters URL parameters to ignore
   */
  function actionUpdateProduct($goto, $parameters) {
    global $messageStack;
    if ($this->display_debug_messages) $messageStack->add_session('header', 'FUNCTION ' . __FUNCTION__, 'caution');

    $change_state = array();
    $this->flag_duplicate_quantity_msgs_set = array();
    $cart_delete = (isset($_POST['cart_delete']) && is_array($_POST['cart_delete'])) ? $_POST['cart_delete'] : array();
    for ($i=0, $n=sizeof($_POST['products_id']); $i<$n; $i++) {
      $adjust_max= 'false';
      if ($_POST['cart_quantity'][$i] == '') {
        $_POST['cart_quantity'][$i] = 0;
      }
      if (!is_numeric($_POST['cart_quantity'][$i]) || $_POST['cart_quantity'][$i] < 0) {
        // adjust quantity when not a value
        $chk_link = '<a href="' . zen_href_link(zen_get_info_page($_POST['products_id'][$i]), 'cPath=' . (zen_get_generated_category_path_rev(zen_get_products_category_id($_POST['products_id'][$i]))) . '&products_id=' . $_POST['products_id'][$i]) . '">' . zen_get_products_name($_POST['products_id'][$i]) . '</a>';
        $messageStack->add_session('header', ERROR_CORRECTIONS_HEADING . ERROR_PRODUCT_QUANTITY_UNITS_SHOPPING_CART . $chk_link . ' ' . PRODUCTS_ORDER_QTY_TEXT . zen_output_string_protected($_POST['cart_quantity'][$i]), 'caution');
//        $_POST['cart_quantity'][$i] = 0; // On an update, if an incorrect value was given, then with expectation that product is already in the cart, then the post quantity should equal what is in the cart, not 0...
        $_POST['cart_quantity'][$i] = $this->get_quantity($_POST['products_id'][$i]);
        continue;
      }
      if (in_array($_POST['products_id'][$i], $cart_delete) || $_POST['cart_quantity'][$i] == 0) {
        $this->remove($_POST['products_id'][$i]);
      } else {
        $add_max = zen_get_products_quantity_order_max($_POST['products_id'][$i]); // maximum allowed
        $chk_mixed = zen_get_products_quantity_mixed($_POST['products_id'][$i]); // use mixed
        // Adjust in cart quantities for product that have other cart
        //   product dependencies and reduction of product to allow a larger increase
        //   at each product's modification.
        //   This will maximize the maximum product quantities available.
        if ($chk_mixed === true && !array_key_exists(zen_get_prid($_POST['products_id'][$i]), $change_state)) {
          $change_check = $this->in_cart_product_mixed_changed($_POST['products_id'][$i], 'decrease'); // Returns full data on products.
          $change_state[zen_get_prid($_POST['products_id'][$i])] = $change_check;
          if (is_array($change_check) && count($change_state[zen_get_prid($_POST['products_id'][$i])]['decrease']) > 0) {
            // Verify minuses are good, and affect the items to be changed
            //  This leaves only increases or 'netzero' to be at play.
            foreach ($change_state[zen_get_prid($_POST['products_id'][$i])]['decrease'] as $prod_id) {
              $attributes = ($_POST['id'][$prod_id]) ? $_POST['id'][$prod_id] : '';
              $this_curr_qty = $this->get_quantity($prod_id);
              $this_new_qty = $this_curr_qty + $change_state[zen_get_prid($_POST['products_id'][$i])]['changed'][$prod_id];
              $this->add_cart($prod_id, $this_new_qty, $attributes, false);
              if ($this->display_debug_messages) $messageStack->add_session('header', 'FUNCTION ' . __FUNCTION__ . ' Products_id: ' . $_POST['products_id'][$i] . ' prod_id: ' . $prod_id . ' this_new_qty: ' . $this_new_qty . ' this_curr_qty: ' . $this_curr_qty . ' change_state[zen_get_prid(_POST[products_id][i])][changed][prod_id]: ' . $change_state[zen_get_prid($_POST['products_id'][$i])]['changed'][$prod_id] . ' attributes: ' . print_r($attributes, true) . ' change_state: ' . print_r($change_state, true) . ' <br>', 'caution');
            }
            unset($prod_num, $prod_id, $attributes, $this_curr_qty, $this_new_qty);
          }
        }
        $cart_qty = $this->in_cart_mixed($_POST['products_id'][$i]); // total currently in cart
        if ($this->display_debug_messages) $messageStack->add_session('header', 'FUNCTION ' . __FUNCTION__ . ' Products_id: ' . $_POST['products_id'][$i] . ' cart_qty: ' . $cart_qty . ' <br>', 'caution');
        $new_qty = $_POST['cart_quantity'][$i]; // new quantity
        $current_qty = $this->get_quantity($_POST['products_id'][$i]); // how many currently in cart for attribute
//        $chk_mixed = zen_get_products_quantity_mixed($_POST['products_id'][$i]); // use mixed

        $new_qty = $this->adjust_quantity($new_qty, $_POST['products_id'][$i], 'shopping_cart');
// bof: adjust new quantity to be same as current in stock
          $chk_current_qty = zen_get_products_stock($_POST['products_id'][$i]);
          if (STOCK_ALLOW_CHECKOUT == 'false' && ($new_qty > $chk_current_qty)) {
              $new_qty = $chk_current_qty;
              $messageStack->add_session('shopping_cart', ($this->display_debug_messages ? 'FUNCTION ' . __FUNCTION__ . ': ' : '') . WARNING_PRODUCT_QUANTITY_ADJUSTED . zen_get_products_name($_POST['products_id'][$i]), 'caution');
          }
// eof: adjust new quantity to be same as current in stock

        if (($add_max == 1 and $cart_qty == 1) && $new_qty != $cart_qty) {
          // do not add
          $adjust_max= 'true';
        } else {
        if ($add_max != 0) {
          // adjust quantity if needed
        switch (true) {
          case ($new_qty == $current_qty): // no change
            $adjust_max = 'false';
            $new_qty = $current_qty;
            break;
          case ($new_qty > $add_max && $chk_mixed == false):
            $adjust_max = 'true';
            $new_qty = $add_max ;
            break;
          case (($add_max - $cart_qty + $new_qty >= $add_max) && $new_qty > $add_max && $chk_mixed == true):
            $adjust_max = 'true';
            $requested_qty = $new_qty;
//            $new_qty = $current_qty;
            $alter_qty = $add_max - $cart_qty + $current_qty;
            $new_qty = ($alter_qty > 0 ? $alter_qty : $current_qty);
            break;
          case (($cart_qty + $new_qty - $current_qty > $add_max) && $chk_mixed == true):
            $adjust_max = 'true';
            $requested_qty = $new_qty;
//            $new_qty = $current_qty;
            $alter_qty = $add_max - $cart_qty + $current_qty;
            $new_qty = ($alter_qty > 0 ? $alter_qty : $current_qty);
            break;
          default:
            $adjust_max = 'false';
          }

// bof: notify about adjustment to new quantity to be same as current in stock or maximum to add
          if ($adjust_max == 'true') {
            $messageStack->add_session('shopping_cart', ($this->display_debug_messages ? 'FUNCTION ' . __FUNCTION__ . ': ' : '') . WARNING_PRODUCT_QUANTITY_ADJUSTED . zen_get_products_name($_POST['products_id'][$i]), 'caution');
          }
// eof: notify about adjustment to new quantity to be same as current in stock or maximum to add

          $attributes = isset($_POST['id'][$_POST['products_id'][$i]]) ? $_POST['id'][$_POST['products_id'][$i]] : '';
          $this->add_cart($_POST['products_id'][$i], $new_qty, $attributes, false);
        } else {
          // adjust minimum and units
          $attributes = isset($_POST['id'][$_POST['products_id'][$i]]) ? $_POST['id'][$_POST['products_id'][$i]] : '';
          $this->add_cart($_POST['products_id'][$i], $new_qty, $attributes, false);
        }
        }
        if ($adjust_max == 'true') {
          if ($this->display_debug_messages) $messageStack->add_session('header', 'FUNCTION ' . __FUNCTION__ . '<br>' . ERROR_MAXIMUM_QTY . zen_get_products_name($_POST['products_id'][$i]) . '<br>requested_qty: ' . $requested_qty . ' current_qty: ' . $current_qty , 'caution');
          $messageStack->add_session('shopping_cart', ERROR_MAXIMUM_QTY . zen_get_products_name($_POST['products_id'][$i]), 'caution');
        } else {
// display message if all is good and not on shopping_cart page
          if ((DISPLAY_CART == 'false' && $_GET['main_page'] != FILENAME_SHOPPING_CART) && $messageStack->size('shopping_cart') == 0) {
            $messageStack->add_session('header', ($this->display_debug_messages ? 'FUNCTION ' . __FUNCTION__ . ': ' : '') . SUCCESS_ADDED_TO_CART_PRODUCTS, 'success');
          } else {
            if ($_GET['main_page'] != FILENAME_SHOPPING_CART) {
              zen_redirect(zen_href_link(FILENAME_SHOPPING_CART));
            }
          }
        }
      }
    }
    zen_redirect(zen_href_link($goto, zen_get_all_get_params($parameters)));
  }
  /**
   * Handle AddProduct cart Action
   *
   * @param string $goto forward destination
   * @param array|string $parameters URL parameters to ignore
   */
  function actionAddProduct($goto, $parameters) {
    global $db, $messageStack;
    if ($this->display_debug_messages) $messageStack->add_session('header', 'A: FUNCTION ' . __FUNCTION__, 'caution');

    $the_list = '';

    if (isset($_POST['products_id']) && is_numeric($_POST['products_id'])) {
      // verify attributes and quantity first
      if ($this->display_debug_messages) $messageStack->add_session('header', 'A2: FUNCTION ' . __FUNCTION__, 'caution');
      $adjust_max= 'false';
      if (isset($_POST['id'])) {
        foreach ($_POST['id'] as $key => $value) {
          $check = zen_get_attributes_valid($_POST['products_id'], $key, $value);
          if ($check == false) {
            $the_list .= TEXT_ERROR_OPTION_FOR . '<span class="alertBlack">' . zen_options_name($key) . '</span>' . TEXT_INVALID_SELECTION . '<span class="alertBlack">' . ($value == (int)PRODUCTS_OPTIONS_VALUES_TEXT_ID ? TEXT_INVALID_USER_INPUT : zen_values_name($value)) . '</span>' . '<br />';
          }
        }
      }
      if (!is_numeric($_POST['cart_quantity']) || $_POST['cart_quantity'] <= 0) {
        // adjust quantity when not a value
        // If use an extra_cart_actions file to prevent processing by this function,
        //   then be sure to set $_POST['shopping_cart_zero_or_less'] to a value other than true
        //   to display success on add to cart and not display the below message.
        if (!isset($_POST['shopping_cart_zero_or_less'])) {
          $chk_link = '<a href="' . zen_href_link(zen_get_info_page($_POST['products_id']), 'cPath=' . (zen_get_generated_category_path_rev(zen_get_products_category_id($_POST['products_id']))) . '&products_id=' . $_POST['products_id']) . '">' . zen_get_products_name($_POST['products_id']) . '</a>';
          $messageStack->add_session('header', ERROR_CORRECTIONS_HEADING . ERROR_PRODUCT_QUANTITY_UNITS_SHOPPING_CART . $chk_link . ' ' . PRODUCTS_ORDER_QTY_TEXT . zen_output_string_protected($_POST['cart_quantity']), 'caution');
          $_POST['shopping_cart_zero_or_less'] = true;
        }
        $_POST['cart_quantity'] = 0;
      }
      // verify qty to add
      $add_max = zen_get_products_quantity_order_max($_POST['products_id']);
      $cart_qty = $this->in_cart_mixed($_POST['products_id']);
      if ($this->display_debug_messages) $messageStack->add_session('header', 'B: FUNCTION ' . __FUNCTION__ . ' Products_id: ' . $_POST['products_id'] . ' cart_qty: ' . $cart_qty . ' $_POST[cart_quantity]: ' . $_POST['cart_quantity'] . ' <br>', 'caution');
      $new_qty = $_POST['cart_quantity'];

      $new_qty = $this->adjust_quantity($new_qty, $_POST['products_id'], 'shopping_cart');

      // adjust new quantity to be no more than current in stock
      $chk_current_qty = zen_get_products_stock($_POST['products_id']);
      $this->flag_duplicate_msgs_set = FALSE;
      if (STOCK_ALLOW_CHECKOUT == 'false' && ($cart_qty + $new_qty > $chk_current_qty)) {
          $new_qty = $chk_current_qty;
          $messageStack->add_session('shopping_cart', ($this->display_debug_messages ? 'C: FUNCTION ' . __FUNCTION__ . ': ' : '') . WARNING_PRODUCT_QUANTITY_ADJUSTED . zen_get_products_name($_POST['products_id']), 'caution');
          $this->flag_duplicate_msgs_set = TRUE;
      }

      if ($add_max == 1 && $cart_qty == 1) {
        // do not add
        $new_qty = 0;
        $adjust_max= 'true';
      } else {
        // adjust new quantity to be no more than current in stock
        if (STOCK_ALLOW_CHECKOUT == 'false' && ($new_qty + $cart_qty > $chk_current_qty)) {
          $adjust_new_qty = 'true';
          $alter_qty = $chk_current_qty - $cart_qty;
          $new_qty = ($alter_qty > 0 ? $alter_qty : 0);
          if (!$this->flag_duplicate_msgs_set) {
            $messageStack->add_session('shopping_cart', ($this->display_debug_messages ? 'D: FUNCTION ' . __FUNCTION__ . ': ' : '') . WARNING_PRODUCT_QUANTITY_ADJUSTED . zen_get_products_name($_POST['products_id']), 'caution');
          }
        }

        // adjust quantity if needed
        if (($new_qty + $cart_qty > $add_max) && $add_max != 0) {
          $adjust_max= 'true';
          $new_qty = $add_max - $cart_qty;
        }
      }
      if (zen_get_products_quantity_order_max($_POST['products_id']) == 1 && $this->in_cart_mixed($_POST['products_id']) == 1) {
        // do not add
      } else {
        // process normally
        // bof: set error message
        if ($the_list != '') {
          $messageStack->add('product_info', ERROR_CORRECTIONS_HEADING . $the_list, 'caution');
        } else {
          // process normally
          // iii 030813 added: File uploading: save uploaded files with unique file names
          $real_ids = isset($_POST['id']) ? $_POST['id'] : array();
          if (isset($_GET['number_of_uploads']) && $_GET['number_of_uploads'] > 0) {
            /**
             * Need the upload class for attribute type that allows user uploads.
             *
             */
            include(DIR_WS_CLASSES . 'upload.php');
            for ($i = 1, $n = $_GET['number_of_uploads']; $i <= $n; $i++) {
              if (isset($_POST[UPLOAD_PREFIX . $i]) && isset($_FILES['id']['tmp_name'][TEXT_PREFIX . $_POST[UPLOAD_PREFIX . $i]]) && zen_not_null($_FILES['id']['tmp_name'][TEXT_PREFIX . $_POST[UPLOAD_PREFIX . $i]]) && (!isset($_POST[UPLOAD_PREFIX . $i]) || !isset($_FILES['id']['tmp_name'][TEXT_PREFIX . $_POST[UPLOAD_PREFIX . $i]]) || ($_FILES['id']['tmp_name'][TEXT_PREFIX . $_POST[UPLOAD_PREFIX . $i]] != 'none'))) {
                $products_options_file = new upload('id');
                $products_options_file->set_destination(DIR_FS_UPLOADS);
                $products_options_file->set_output_messages('session');
                if ($products_options_file->parse(TEXT_PREFIX . $_POST[UPLOAD_PREFIX . $i])) {
                  $products_image_extension = substr($products_options_file->filename, strrpos($products_options_file->filename, '.'));
                  if (zen_is_logged_in()) {
                    $db->Execute("INSERT INTO " . TABLE_FILES_UPLOADED . " (sesskey, customers_id, files_uploaded_name) VALUES ('" . zen_session_id() . "', " . (int)$_SESSION['customer_id'] . ", '" . zen_db_input($products_options_file->filename) . "')");
                  } else {
                    $db->Execute("INSERT INTO " . TABLE_FILES_UPLOADED . " (sesskey, files_uploaded_name) VALUES ('" . zen_session_id() . "', '" . zen_db_input($products_options_file->filename) . "')");
                  }
                  $insert_id = $db->Insert_ID();
                  $real_ids[TEXT_PREFIX . $_POST[UPLOAD_PREFIX . $i]] = $insert_id . ". " . $products_options_file->filename;
                  $products_options_file->set_filename("$insert_id" . $products_image_extension);
                  if (!($products_options_file->save())) {
                    break;
                  }
                } else {
                  break;
                }
              } else { // No file uploaded -- use previous value
                $real_ids[TEXT_PREFIX . $_POST[UPLOAD_PREFIX . $i]] = isset($_POST[TEXT_PREFIX . UPLOAD_PREFIX . $i]) ? $_POST[TEXT_PREFIX . UPLOAD_PREFIX . $i] : '';
                if (!zen_get_attributes_valid($_POST['products_id'], TEXT_PREFIX . $_POST[UPLOAD_PREFIX . $i], !empty($_POST[TEXT_PREFIX . UPLOAD_PREFIX . $i]) ? $_POST[TEXT_PREFIX . UPLOAD_PREFIX . $i] : '')) {
                  $the_list .= TEXT_ERROR_OPTION_FOR . '<span class="alertBlack">' . zen_options_name($_POST[UPLOAD_PREFIX . $i]) . '</span>' . TEXT_INVALID_SELECTION . '<span class="alertBlack">' . ($_POST[TEXT_PREFIX . UPLOAD_PREFIX . $i] == (int)PRODUCTS_OPTIONS_VALUES_TEXT_ID ? TEXT_INVALID_USER_INPUT : zen_values_name($value)) . '</span>' . '<br />';
                  $new_qty = 0; // Don't increase the quantity of product in the cart.
                }
              }
            }

            if ($the_list != '') {
              $messageStack->add('product_info', ERROR_CORRECTIONS_HEADING . $the_list, 'caution');
            }

            // remove helper param from URI of the upcoming redirect
            $parameters[] = 'number_of_uploads';
            unset($_GET['number_of_uploads']);
          }

          // do the actual add to cart
          $this->add_cart($_POST['products_id'], $this->get_quantity(zen_get_uprid($_POST['products_id'], $real_ids))+($new_qty), $real_ids);
          // iii 030813 end of changes.
        } // eof: set error message
      } // eof: quantity maximum = 1

      if ($adjust_max == 'true') {
        $messageStack->add_session('shopping_cart', ERROR_MAXIMUM_QTY . zen_get_products_name($_POST['products_id']), 'caution');
        if ($this->display_debug_messages) $messageStack->add_session('header', 'E: FUNCTION ' . __FUNCTION__ . '<br>' . ERROR_MAXIMUM_QTY . zen_get_products_name($_POST['products_id']), 'caution');
      }
    }
    if (empty($the_list)) { // no errors
      // display message if all is good and not on shopping_cart page
      if (DISPLAY_CART == 'false' && $_GET['main_page'] != FILENAME_SHOPPING_CART && $messageStack->size('shopping_cart') == 0) {
        if (!isset($_POST['shopping_cart_zero_or_less']) || $_POST['shopping_cart_zero_or_less'] !== true) {
          $messageStack->add_session('header', ($this->display_debug_messages ? 'FUNCTION ' . __FUNCTION__ . ': ' : '') . SUCCESS_ADDED_TO_CART_PRODUCT, 'success');
        }
        zen_redirect(zen_href_link($goto, zen_get_all_get_params($parameters)));
      } else {
        zen_redirect(zen_href_link(FILENAME_SHOPPING_CART));
      }
    } else {
      // errors found with attributes - perhaps display an additional message here, using an observer class to add to the messageStack
      $this->notify('NOTIFIER_CART_OPTIONAL_ATTRIBUTE_ERROR_MESSAGE_HOOK', $_POST, $the_list);
    }
  }
  /**
   * Handle BuyNow cart Action
   *
   * @param string $goto forward destination
   * @param array|string $parameters URL parameters to ignore
   */
  function actionBuyNow($goto, $parameters) {
    global $messageStack;
    if ($this->display_debug_messages) $messageStack->add_session('header', 'FUNCTION ' . __FUNCTION__ . ' $_GET[products_id]: ' . $_GET['products_id'], 'caution');

    $this->flag_duplicate_msgs_set = FALSE;
    if (isset($_GET['products_id'])) {
      if (zen_has_product_attributes($_GET['products_id'])) {
        zen_redirect(zen_href_link(zen_get_info_page($_GET['products_id']), 'products_id=' . $_GET['products_id']));
      } else {
        $add_max = zen_get_products_quantity_order_max($_GET['products_id']);
        $cart_qty = $this->in_cart_mixed($_GET['products_id']);
        $new_qty = zen_get_buy_now_qty($_GET['products_id']);
        if (!is_numeric($new_qty) || $new_qty < 0) {
          // adjust quantity when not a value
          $chk_link = '<a href="' . zen_href_link(zen_get_info_page($_GET['products_id']), 'cPath=' . (zen_get_generated_category_path_rev(zen_get_products_category_id($_GET['products_id']))) . '&products_id=' . $_GET['products_id']) . '">' . zen_get_products_name($_GET['products_id']) . '</a>';
          $messageStack->add_session('header', ERROR_CORRECTIONS_HEADING . ERROR_PRODUCT_QUANTITY_UNITS_SHOPPING_CART . $chk_link . ' ' . PRODUCTS_ORDER_QTY_TEXT . zen_output_string_protected($new_qty), 'caution');
          $new_qty = 0;
        }
        if (($add_max == 1 and $cart_qty == 1)) {
          // do not add
          $new_qty = 0;
        } else {
          // adjust quantity if needed
          if (($new_qty + $cart_qty > $add_max) and $add_max != 0) {
            $new_qty = $add_max - $cart_qty;
          }
        }
        if ((zen_get_products_quantity_order_max($_GET['products_id']) == 1 and $this->in_cart_mixed($_GET['products_id']) == 1)) {
          // do not add
        } else {
          // check for min/max and add that value or 1
          $this->add_cart($_GET['products_id'], $this->get_quantity($_GET['products_id'])+$new_qty);
        }
      }
    }
    // display message if all is good and not on shopping_cart page
    if ((DISPLAY_CART == 'false' && $_GET['main_page'] != FILENAME_SHOPPING_CART) && $messageStack->size('shopping_cart') == 0) {
      $messageStack->add_session('header', ($this->display_debug_messages ? 'FUNCTION ' . __FUNCTION__ . ': ' : '') . SUCCESS_ADDED_TO_CART_PRODUCTS, 'success');
    } else {
      if (DISPLAY_CART == 'false') {
        zen_redirect(zen_href_link(FILENAME_SHOPPING_CART));
      }
    }
    if (is_array($parameters) && !in_array('products_id', $parameters) && !strpos($goto, 'reviews') > 5) $parameters[] = 'products_id';
    zen_redirect(zen_href_link($goto, zen_get_all_get_params($parameters)));
  }
  /**
   * Handle MultipleAddProduct cart Action
   *
   * @param string $goto forward destination
   * @param array|string $parameters URL parameters to ignore
   */
  function actionMultipleAddProduct($goto, $parameters) {
    global $messageStack;
    if ($this->display_debug_messages) $messageStack->add_session('header', 'FUNCTION ' . __FUNCTION__, 'caution');

    $addCount = 0;
    if (is_array($_POST['products_id']) && sizeof($_POST['products_id']) > 0) {
      $products_list = $_POST['products_id'];
      foreach($products_list as $key => $val) {
        $prodId = preg_replace('/[^0-9a-f:.]/', '', $key);
        if (is_numeric($val) && $val > 0) {
          $adjust_max = false;
          $qty = $val;
          $add_max = zen_get_products_quantity_order_max($prodId);
          $cart_qty = $this->in_cart_mixed($prodId);
          $new_qty = $this->adjust_quantity($qty, $prodId, 'shopping_cart');

          // adjust new quantity to be no more than current in stock
          $chk_current_qty = zen_get_products_stock($prodId);
          if (STOCK_ALLOW_CHECKOUT == 'false' && ($new_qty > $chk_current_qty)) {
              $new_qty = $chk_current_qty;
              $messageStack->add_session('shopping_cart', ($this->display_debug_messages ? 'FUNCTION ' . __FUNCTION__ . ': ' : '') . WARNING_PRODUCT_QUANTITY_ADJUSTED . zen_get_products_name($prodId), 'caution');
          }

          if (($add_max == 1 and $cart_qty == 1)) {
            // do not add
            $adjust_max= 'true';
          } else {
            // adjust new quantity to be no more than current in stock
            if (STOCK_ALLOW_CHECKOUT == 'false' && ($new_qty + $cart_qty > $chk_current_qty)) {
                $adjust_new_qty = 'true';
                $alter_qty = $chk_current_qty - $cart_qty;
                $new_qty = ($alter_qty > 0 ? $alter_qty : 0);
                $messageStack->add_session('shopping_cart', ($this->display_debug_messages ? 'FUNCTION ' . __FUNCTION__ . ': ' : '') . WARNING_PRODUCT_QUANTITY_ADJUSTED . zen_get_products_name($prodId), 'caution');
            }

            // adjust quantity if needed
            if ((($new_qty + $cart_qty > $add_max) and $add_max != 0)) {
              $adjust_max= 'true';
              $new_qty = $add_max - $cart_qty;
            }
            $this->add_cart($prodId, $this->get_quantity($prodId)+($new_qty));
            $addCount++;
          }
          if ($adjust_max == 'true') {
            if ($this->display_debug_messages) $messageStack->add_session('header', 'FUNCTION ' . __FUNCTION__ . '<br>' . ERROR_MAXIMUM_QTY . zen_get_products_name($prodId), 'caution');
            $messageStack->add_session('shopping_cart', ERROR_MAXIMUM_QTY . zen_get_products_name($prodId), 'caution');
          }
        }
        if (!is_numeric($val) || $val < 0) {
          // adjust quantity when not a value
          $chk_link = '<a href="' . zen_href_link(zen_get_info_page($prodId), 'cPath=' . (zen_get_generated_category_path_rev(zen_get_products_category_id($prodId))) . '&products_id=' . $prodId) . '">' . zen_get_products_name($prodId) . '</a>';
          $messageStack->add_session('header', ERROR_CORRECTIONS_HEADING . ERROR_PRODUCT_QUANTITY_UNITS_SHOPPING_CART . $chk_link . ' ' . PRODUCTS_ORDER_QTY_TEXT . zen_output_string_protected($val), 'caution');
          $val = 0;
        }
      }

      // display message if all is good and not on shopping_cart page
      if (($addCount && DISPLAY_CART == 'false' && $_GET['main_page'] != FILENAME_SHOPPING_CART) && $messageStack->size('shopping_cart') == 0) {
        $messageStack->add_session('header', ($this->display_debug_messages ? 'FUNCTION ' . __FUNCTION__ . ': ' : '') . SUCCESS_ADDED_TO_CART_PRODUCTS, 'success');
      } else {
        if (DISPLAY_CART == 'false') {
          zen_redirect(zen_href_link(FILENAME_SHOPPING_CART));
        }
      }
      zen_redirect(zen_href_link($goto, zen_get_all_get_params($parameters)));
    }
  }
  /**
   * Handle Notify cart Action
   *
   * @param string $goto forward destination
   * @param array|string $parameters URL parameters to ignore
   */
  function actionNotify($goto, $parameters = 'ignored') {
    global $db;
    if (zen_is_logged_in() && !zen_in_guest_checkout()) {
      if (isset($_GET['products_id'])) {
        $notify = $_GET['products_id'];
      } elseif (isset($_GET['notify'])) {
        $notify = $_GET['notify'];
      } elseif (isset($_POST['notify'])) {
        $notify = $_POST['notify'];
      } else {
        zen_redirect(zen_href_link($_GET['main_page'], zen_get_all_get_params(array('action', 'notify', 'main_page'))));
      }
      if (!is_array($notify)) $notify = array($notify);
      for ($i=0, $n=sizeof($notify); $i<$n; $i++) {
        $check_query = "SELECT count(*) AS count
                          FROM " . TABLE_PRODUCTS_NOTIFICATIONS . "
                          WHERE products_id = " . (int)$notify[$i] . "
                          AND customers_id = " . (int)$_SESSION['customer_id'];
        $check = $db->Execute($check_query);
        if ($check->fields['count'] < 1) {
          $sql = "INSERT INTO " . TABLE_PRODUCTS_NOTIFICATIONS . "
                    (products_id, customers_id, date_added)
                     VALUES (" . (int)$notify[$i] . ", " . (int)$_SESSION['customer_id'] . ", now())";
          $db->Execute($sql);
        }
      }
     zen_redirect(zen_href_link($_GET['main_page'], zen_get_all_get_params(array('action', 'notify', 'main_page'))));

    } else {
      $_SESSION['navigation']->set_snapshot();
      zen_redirect(zen_href_link(FILENAME_LOGIN, '', 'SSL'));
    }
  }
  /**
   * Handle NotifyRemove cart Action
   *
   * @param string $goto forward destination
   * @param array|string $parameters URL parameters to ignore
   */
  function actionNotifyRemove($goto, $parameters = 'ignored') {
    global $db;
    if (zen_is_logged_in() && !zen_in_guest_checkout() && isset($_GET['products_id'])) {
      $check_query = "SELECT count(*) AS count
                        FROM " . TABLE_PRODUCTS_NOTIFICATIONS . "
                        WHERE products_id = " . (int)$_GET['products_id'] . "
                        AND customers_id = " . (int)$_SESSION['customer_id'];
      $check = $db->Execute($check_query);

      if ($check->fields['count'] > 0) {
        $sql = "DELETE FROM " . TABLE_PRODUCTS_NOTIFICATIONS . "
                  WHERE products_id = " . (int)$_GET['products_id'] . "
                  AND customers_id = " . (int)$_SESSION['customer_id'];
        $db->Execute($sql);
      }
      zen_redirect(zen_href_link($_GET['main_page'], zen_get_all_get_params(array('action', 'main_page'))));
    } else {
      $_SESSION['navigation']->set_snapshot();
      zen_redirect(zen_href_link(FILENAME_LOGIN, '', 'SSL'));
    }
  }
  /**
   * Handle CustomerOrder cart Action
   *
   * @param string $goto forward destination
   * @param array|string $parameters URL parameters to ignore
   */
  function actionCustomerOrder($goto, $parameters) {
    global $zco_page, $messageStack;
    if ($this->display_debug_messages) $messageStack->add_session('header', 'FUNCTION ' . __FUNCTION__, 'caution');

    if (zen_is_logged_in() && isset($_GET['pid'])) {
      if (zen_has_product_attributes($_GET['pid'])) {
        zen_redirect(zen_href_link(zen_get_info_page($_GET['pid']), 'products_id=' . $_GET['pid']));
      } else {
        $this->add_cart($_GET['pid'], $this->get_quantity($_GET['pid'])+1);
      }
    }
    // display message if all is good and not on shopping_cart page
    if ((DISPLAY_CART == 'false' && $_GET['main_page'] != FILENAME_SHOPPING_CART) && $messageStack->size('shopping_cart') == 0) {
      $messageStack->add_session('header', ($this->display_debug_messages ? 'FUNCTION ' . __FUNCTION__ . ': ' : '') . SUCCESS_ADDED_TO_CART_PRODUCTS, 'success');
    } else {
      if (DISPLAY_CART == 'false') {
        zen_redirect(zen_href_link(FILENAME_SHOPPING_CART));
      }
    }
    zen_redirect(zen_href_link($goto, zen_get_all_get_params($parameters)));
  }
  /**
   * Handle RemoveProduct cart Action
   *
   * @param string $goto forward destination
   * @param array|string $parameters URL parameters to ignore
   */
  function actionRemoveProduct($goto, $parameters) {
    if (isset($_GET['product_id']) && zen_not_null($_GET['product_id'])) $this->remove($_GET['product_id']);
    zen_redirect(zen_href_link($goto, zen_get_all_get_params($parameters)));
  }
  /**
   * Handle CartUserAction cart Action
   * This just fires any NOTIFY_CART_USER_ACTION observers.
   *
   * @param string $goto forward destination
   * @param array|string $parameters URL parameters to ignore
   */
  function actionCartUserAction($goto, $parameters) {
    $this->notify('NOTIFY_CART_USER_ACTION', array(), $goto, $parameters);
  }
/**
 * calculate quantity adjustments based on restrictions
 * USAGE:  $qty = $this->adjust_quantity($qty, (int)$products_id, 'shopping_cart');
 *
 * @param float $check_qty
 * @param int $products
 * @param string $stack messageStack placement
 */
  function adjust_quantity($check_qty, $products, $stack = 'shopping_cart') {
    global $messageStack;
    if ($stack == '' || $stack == FALSE) $stack = 'shopping_cart';
      $old_quantity = $check_qty;
        if (QUANTITY_DECIMALS != 0) {
          $fix_qty = $check_qty;
          switch (true) {
            case (!strstr($fix_qty, '.')):
            $new_qty = $fix_qty;
            break;
            default:
            $new_qty = preg_replace('/[0]+$/','', $check_qty);
            break;
          }
        } else {
          if ($check_qty != round($check_qty, QUANTITY_DECIMALS)) {
            $new_qty = round($check_qty, QUANTITY_DECIMALS);
            $messageStack->add_session($stack, ERROR_QUANTITY_ADJUSTED . zen_get_products_name($products) . ERROR_QUANTITY_CHANGED_FROM . $old_quantity . ERROR_QUANTITY_CHANGED_TO . $new_qty, 'caution');
          } else {
            $new_qty = $check_qty;
          }
        }
     return $new_qty;
  }
/**
 * calculate the number of items in a cart based on an attribute option_id and option_values_id combo
 * USAGE:  $chk_attrib_1_16 = $this->in_cart_check_attrib_quantity(1, 16);
 * USAGE:  $chk_attrib_1_16 = $_SESSION['cart']->in_cart_check_attrib_quantity(1, 16);
 *
 * @param integer $check_option_id
 * @param integer $check_option_values_id
 * @return float
 */
  function in_cart_check_attrib_quantity($check_option_id, $check_option_values_id) {
    // if nothing is in cart return 0
    if (!is_array($this->contents)) return 0;

    $in_cart_check_qty = 0;
    // get products in cart to check
    $chk_products = $this->get_products();
    for ($i=0, $n=sizeof($chk_products); $i<$n; $i++) {
      if (is_array($chk_products[$i]['attributes'])) {
        foreach ($chk_products[$i]['attributes'] as $option => $value) {
          if ($option == $check_option_id && $value == $check_option_values_id) {
  //          echo 'Attribute FOUND FOR $option: ' . $option . ' $value: ' . $value . ' quantity: ' . $chk_products[$i]['quantity'] . '<br><br>';
            $in_cart_check_qty += $chk_products[$i]['quantity'];
          }
        }
      }
    }
    return $in_cart_check_qty;
  }
/**
 * calculate products_id price in cart
 * USAGE:  $product_total_price = $this->in_cart_product_total_price(12);
 * USAGE:  $chk_product_cart_total_price = $_SESSION['cart']->in_cart_product_total_price(12);
 *
 * @param mixed $product_id
 */
  function in_cart_product_total_price($product_id) {
    $products = $this->get_products();
//echo '<pre>'; echo print_r($products); echo '</pre>';
    for ($i=0, $n=sizeof($products); $i<$n; $i++) {
      $productsName = $products[$i]['name'];
      $ppe = $products[$i]['final_price'];
      $ppt = $ppe * $products[$i]['quantity'];
      $productsPriceEach = $ppe + $products[$i]['onetime_charges'];
      $productsPriceTotal = $ppt + $products[$i]['onetime_charges'];
      if ((int)$product_id == (int)$products[$i]['id']) {
//        echo 'GOOD id: ' . $products[$i]['id'] . ' vs ' . ' $product_id: ' . $product_id . ' $products[$i][name]: ' . $products[$i]['name'] . ' $productsPriceEach: ' . $productsPriceEach . ' $productsPriceTotal: ' . $productsPriceTotal . '<br><br>';
        $in_cart_product_price += $productsPriceTotal;
      } else {
//        echo 'NOT GOOD id: ' . $products[$i]['id'] . ' vs ' . ' $product_id: ' . $product_id . ' $products[$i][name]: ' . $products[$i]['name'] . ' $productsPriceEach: ' . $productsPriceEach . ' $productsPriceTotal: ' . $productsPriceTotal . '<br><br>';
      }
    }
    return $in_cart_product_price;
  }
/**
 * calculate products_id quantity in cart regardless of attributes
 * USAGE:  $product_total_quantity = $this->in_cart_product_total_quantity(12);
 * USAGE:  $chk_product_cart_total_quantity = $_SESSION['cart']->in_cart_product_total_quantity(12);
 *
 * @param mixed $product_id
 */
  function in_cart_product_total_quantity($product_id) {
    $products = $this->get_products();
//echo '<pre>'; echo print_r($products); echo '</pre>';
    $in_cart_product_quantity = 0;
    for ($i=0, $n=sizeof($products); $i<$n; $i++) {
      if ((int)$product_id == (int)$products[$i]['id']) {
//        echo 'GOOD id: ' . $products[$i]['id'] . ' vs ' . ' $product_id: ' . $product_id . ' $products[$i][name]: ' . $products[$i]['name'] . ' $in_cart_product_quantity: ' . $in_cart_product_quantity . '<br><br>';
        $in_cart_product_quantity += $products[$i]['quantity'];
      } else {
//        echo 'NOT GOOD id: ' . $products[$i]['id'] . ' vs ' . ' $product_id: ' . $product_id . ' $products[$i][name]: ' . $products[$i]['name'] . ' $in_cart_product_quantity: ' . $in_cart_product_quantity . '<br><br>';
      }
    }
    return $in_cart_product_quantity;
  }

/**
 * calculate products_id weight in cart regardless of attributes
 * USAGE:  $product_total_weight = $this->in_cart_product_total_weight(12);
 * USAGE:  $chk_product_cart_total_weight = $_SESSION['cart']->in_cart_product_total_weight(12);
 *
 * @param mixed $product_id
 * @return float
 */
  function in_cart_product_total_weight($product_id) {
    $products = $this->get_products();
    $in_cart_product_weight = 0;
    for ($i=0, $n=sizeof($products); $i<$n; $i++) {
      if ((int)$product_id == (int)$products[$i]['id']) {
        $in_cart_product_weight += $products[$i]['weight'] * $products[$i]['quantity'];
      }
    }
    return $in_cart_product_weight;
  }

/**
 * calculate weight in cart for a category without subcategories
 * USAGE:  $category_total_weight_cat = $this->in_cart_product_total_weight_category(9);
 * USAGE:  $chk_category_cart_total_weight_cat = $_SESSION['cart']->in_cart_product_total_weight_category(9);
 *
 * @param integer $category_id
 * @return float
 */
  function in_cart_product_total_weight_category($category_id) {
    $products = $this->get_products();
    $in_cart_product_weight = 0;
    for ($i=0, $n=sizeof($products); $i<$n; $i++) {
      if ($products[$i]['category'] == $category_id) {
        $in_cart_product_weight += $products[$i]['weight'] * $products[$i]['quantity'];
      }
    }
    return $in_cart_product_weight;
  }

/**
 * calculate price in cart for a category without subcategories
 * USAGE:  $category_total_price_cat = $this->in_cart_product_total_price_category(9);
 * USAGE:  $chk_category_cart_total_price_cat = $_SESSION['cart']->in_cart_product_total_price_category(9);
 *
 * @param integer $category_id
 * @return float
 */
  function in_cart_product_total_price_category($category_id) {
    $products = $this->get_products();
//echo '<pre>'; echo print_r($products); echo '</pre>';
    for ($i=0, $n=sizeof($products); $i<$n; $i++) {
      $productsName = $products[$i]['name'];
      $ppe = $products[$i]['final_price'];
      $ppt = $ppe * $products[$i]['quantity'];
      $productsPriceEach = $ppe + $products[$i]['onetime_charges'];
      $productsPriceTotal = $ppt + $products[$i]['onetime_charges'];
      if ($products[$i]['category'] == $category_id) {
        $in_cart_product_price += $productsPriceTotal;
      }
    }
    return $in_cart_product_price;
  }

/**
 * calculate quantity in cart for a category without subcategories
 * USAGE:  $category_total_quantity_cat = $this->in_cart_product_total_quantity_category(9);
 * USAGE:  $chk_category_cart_total_quantity_cat = $_SESSION['cart']->in_cart_product_total_quantity_category(9);
 *
 * @param integer $category_id
 * @return float
 */
  function in_cart_product_total_quantity_category($category_id) {
    $products = $this->get_products();
//echo '<pre>'; echo print_r($products); echo '</pre>';
    $in_cart_product_quantity = 0;
    for ($i=0, $n=sizeof($products); $i<$n; $i++) {
      if ($products[$i]['category'] == $category_id) {
        $in_cart_product_quantity += $products[$i]['quantity'];
      }
    }
    return $in_cart_product_quantity;
  }

/**
 * calculate weight in cart for a category with or without subcategories
 * USAGE:  $category_total_weight_cat = $this->in_cart_product_total_weight_category_sub(3);
 * USAGE:  $chk_category_cart_total_weight_cat = $_SESSION['cart']->in_cart_product_total_weight_category_sub(3);
 *
 * @param integer $category_id
 * @return float
 */
  function in_cart_product_total_weight_category_sub($category_id) {
    $chk_cart_weight = 0;
    if (zen_has_category_subcategories($category_id)) {
      $subcategories_array = array();
      $chk_cat = $category_id; // parent categories_id
      zen_get_subcategories($subcategories_array, $chk_cat);
      for ($i=0, $n=sizeof($subcategories_array); $i<$n; $i++ ) {
        $chk_cart_weight += $this->in_cart_product_total_weight_category($subcategories_array[$i]);
      }
    } else {
      $chk_cart_weight = $this->in_cart_product_total_weight_category($category_id);
    }
    return $chk_cart_weight;
  }

/**
 * calculate price in cart for a category with or without subcategories
 * USAGE:  $category_total_price_cat = $this->in_cart_product_total_price_category_sub(3);
 * USAGE:  $chk_category_cart_total_price_cat = $_SESSION['cart']->in_cart_product_total_price_category_sub(3);
 *
 * @param integer $category_id
 * @return float
 */
  function in_cart_product_total_price_category_sub($category_id) {
    $chk_cart_price = 0;
    if (zen_has_category_subcategories($category_id)) {
      $subcategories_array = array();
      $chk_cat = $category_id; // parent categories_id
      zen_get_subcategories($subcategories_array, $chk_cat);
      for ($i=0, $n=sizeof($subcategories_array); $i<$n; $i++ ) {
        $chk_cart_price += $this->in_cart_product_total_price_category($subcategories_array[$i]);
      }
    } else {
      $chk_cart_price = $this->in_cart_product_total_price_category($category_id);
    }
    return $chk_cart_price;
  }

/**
 * calculate quantity in cart for a category with or without subcategories
 * USAGE:  $category_total_quantity_cat = $this->in_cart_product_total_quantity_category_sub(3);
 * USAGE:  $chk_category_cart_total_quantity_cat = $_SESSION['cart']->in_cart_product_total_quantity_category_sub(3);
 *
 * @param integer $category_id
 * @return float
 */
  function in_cart_product_total_quantity_category_sub($category_id) {
    $chk_cart_quantity = 0;
    if (zen_has_category_subcategories($category_id)) {
      $subcategories_array = array();
      $chk_cat = $category_id; // parent categories_id
      zen_get_subcategories($subcategories_array, $chk_cat);
      for ($i=0, $n=sizeof($subcategories_array); $i<$n; $i++ ) {
        $chk_cart_quantity += $this->in_cart_product_total_quantity_category($subcategories_array[$i]);
      }
    } else {
      $chk_cart_quantity = $this->in_cart_product_total_quantity_category($category_id);
    }
    return $chk_cart_quantity;
  }

/**
 * calculate shopping cart stats for a products_id to obtain data about submitted (posted) items as compared to what is in the cart.
 * USAGE:  $mix_increase = in_cart_product_mixed_changed($product_id, 'increase');
 * USAGE:  $mix_decrease = in_cart_product_mixed_changed($product_id, 'decrease');
 * USAGE:  $mix_all = in_cart_product_mixed_changed($product_id);
 * USAGE:  $mix_all = in_cart_product_mixed_changed($product_id, 'all'); (Second value anything other than 'increase' or 'decrease')
 *
 * @param mixed $product_id
 * @return array
 */
  function in_cart_product_mixed_changed($product_id, $chk = false) {
    global $db;

    $pr_id = zen_get_prid($product_id);

    // check if mixed is on
    $product = $db->Execute("SELECT products_id, products_quantity_mixed FROM " . TABLE_PRODUCTS . " WHERE products_id=" . (int)$pr_id . " limit 1");

    // if mixed attributes is off identify that this product is the last of its kind (which is also the first of its kind).
    if ($product->fields['products_quantity_mixed'] == '0') {
      return true;
    }

    $product_changed = array();
    $product_total_change = array();
    $product_tracked_changed = array();
    $product_last_changed = array();
    $product_increase = array();
    $product_decrease = array();

    for ($i=0, $n=count($_POST['products_id']); $i<$n; $i++) {
      $products_id = $_POST['products_id'][$i];
      $prs_id = zen_get_prid($products_id);
      $current_qty = $this->get_quantity($products_id); // $products[$i]['quantity']
      if (!is_numeric($_POST['cart_quantity'][$i]) || $_POST['cart_quantity'][$i] < 0) {
        $_POST['cart_quantity'][$i] = $current_qty; // Default response behavior in cart.
      }
      if ($_POST['cart_quantity'][$i] != $current_qty) { // identify that quantity changed
        $product_changed[$products_id] = $_POST['cart_quantity'][$i] - $current_qty;  // Identify that the specific product changed and by how much the customer increased it.
        if (array_key_exists($prs_id, $product_total_change)) {
          $product_total_change[$prs_id] = $product_total_change[$prs_id] + $product_changed[$products_id];
        } else {
          $product_total_change[$prs_id] = $product_changed[$products_id];
        }

        switch (true) {
          case ($chk == 'increase'): // track only increases
            if ($_POST['cart_quantity'][$i] > $current_qty) {
              $product_tracked_changed[$products_id] = true;  // Identify that the specific product changed
              $product_last_changed[$prs_id] = $products_id; // Identify what the last changed product was.
              $product_increase[] = $products_id;
            }
            break;
          case ($chk == 'decrease'): // track only decreases
            if ($_POST['cart_quantity'][$i] < $current_qty) {
              $product_tracked_changed[$products_id] = true;  // Identify that the specific product changed
              $product_last_changed[$prs_id] = $products_id; // Identify what the last changed product was.
              $product_decrease[] = $products_id;
            }
            break;
          default: // track the last that had a difference in quantity.
            $product_tracked_changed[$products_id] = true;  // Identify that the specific product changed
            $product_last_changed[$prs_id] = $products_id; // Identify what the last changed product was.
            if ($_POST['cart_quantity'][$i] > $current_qty) {
              $product_increase[] = $products_id;
            }
            if ($_POST['cart_quantity'][$i] < $current_qty) {
              $product_decrease[] = $products_id;
            }
        }
      }
    }

    $changed_array = array(
         'state'=>false,
         'changed' => $product_changed,
         'total_change' => $product_total_change[$pr_id],
         'last_changed' => $product_last_changed[$pr_id],
         'increase' => $product_increase,
         'decrease' => $product_decrease,
       );

    if (array_key_exists($product_id, $product_changed)) {
      if ($product_total_change[$pr_id] == '0') {
        $changed_array['state'] = 'netzero';
        return $changed_array;
      }

      if (array_key_exists($product_id, $product_tracked_changed)) {
        if ($product_last_changed[$pr_id] == $product_id) {
          $changed_array['state'] = true;
          return $changed_array;
        }
      }
    }

    return $changed_array;
  }

}
