<?php
/**
 * @package page
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: DrByte  Sat Jul 7 00:29:18 2012 -0400 New in v1.5.1 $
 */
?>

<script type="text/javascript">
function onloadFocus() {
<?php
  if (PRODUCT_LISTING_MULTIPLE_ADD_TO_CART != 0) {
?>
 var x=document.getElementsByName('multiple_products_cart_quantity');
 if (x.length > 0) {
   document.forms['multiple_products_cart_quantity'].elements[1].focus();
 }
<?php } ?>
}
</script>