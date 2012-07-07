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