/**
 * @package templateSystem
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id$
 */

// Shipping Estimator auto-submit form after selecting an address from address-book
jQuery(".autosubmit select, SELECT#seAddressPulldown").change(function() {
    jQuery(this).closest('form').submit();
});

/**
 * On multiple-product pages where cart-quantity is displayed, set focus on first item
 */
jQuery('form[name="multiple_products_cart_quantity"]').find(':input[type="number"]:enabled:visible:first, :input:text:enabled:visible:first').focus();

