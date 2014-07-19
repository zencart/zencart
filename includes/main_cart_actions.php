<?php
/**
 * Main shopping Cart actions supported.
 *
 * The main cart actions supported by the current shoppingCart class.
 * This can be added to externally using the extra_cart_actions directory.
 *
 * @package initSystem
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: DrByte  Fri Jul 6 11:57:44 2012 -0400 Modified in v1.5.1 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
/**
 * include the list of extra cart action files  (*.php in the extra_cart_actions folder)
 */
if ($za_dir = @dir(DIR_WS_INCLUDES . 'extra_cart_actions')) {
  while ($zv_file = $za_dir->read()) {
    if (preg_match('~^[^\._].*\.php$~i', $zv_file) > 0) {
      /**
       * get user/contribution defined cart actions
       */
      include(DIR_WS_INCLUDES . 'extra_cart_actions/' . $zv_file);
    }
  }
  $za_dir->close();
}
switch ($_GET['action']) {
  /**
   * customer wants to update the product quantity in their shopping cart
   * delete checkbox or 0 quantity removes from cart
   */
  case 'update_product' :
  $_SESSION['cart']->actionUpdateProduct($goto, $parameters);
  break;
  /**
   * customer adds a product from the products page
   */
  case 'add_product' :
  $_SESSION['cart']->actionAddProduct($goto, $parameters);
  break;
  case 'buy_now' :
  /**
   * performed by the 'buy now' button in product listings and review page
   */
  $_SESSION['cart']->actionBuyNow($goto, $parameters);
  break;
  case 'multiple_products_add_product' :
  /**
   * performed by the multiple-add-products button
   */
  $_SESSION['cart']->actionMultipleAddProduct($goto, $parameters);
  break;
  case 'notify' :
  $_SESSION['cart']->actionNotify($goto, $parameters);
  break;
  case 'notify_remove' :
  $_SESSION['cart']->actionNotifyRemove($goto, $parameters);
  break;
  case 'cust_order' :
  $_SESSION['cart']->actionCustomerOrder($goto, $parameters);
  break;
  case 'remove_product' :
  $_SESSION['cart']->actionRemoveProduct($goto, $parameters);
  break;
  case 'cart' :
  $_SESSION['cart']->actionCartUserAction($goto, $parameters);
  break;
  case 'empty_cart' :
  $_SESSION['cart']->reset(true);
  break;
}
