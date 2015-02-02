<?php
/**
 * @package admin
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: Ian Wilson  New in v1.6.0 $
 */
?>
<script>
if (typeof zcJS == "undefined" || !zcJS) {
  window.zcJS = { name: 'zcJS', version: '0.1.0.0' };
};

zcJS.securityToken = '<?php echo $_SESSION['securityToken']; ?>';

<?php if (isset($_SESSION['jscript_enabled'])) { ?>
<?php   unset($_SESSION['jscript_enabled']); ?>
<?php } ?>
<?php if (PADSS_AJAX_CHECKOUT=='1' && in_array($current_page, array(FILENAME_CHECKOUT_CONFIRMATION,FILENAME_CHECKOUT_PAYMENT,FILENAME_CHECKOUT_SHIPPING,FILENAME_SHOPPING_CART,FILENAME_LOGIN))) { ?>
zcJS.ajax({
    url: "ajax.php?act=ajaxPayment&method=setNoscriptCookie",
    data: {test: '1'},
    async: false
  }).done(function( response ) {
});
<?php } ?>
</script>
<?php if (PADSS_AJAX_CHECKOUT=='1' && ($current_page == FILENAME_CHECKOUT_CONFIRMATION || $current_page == FILENAME_CHECKOUT_PAYMENT || $current_page == FILENAME_CHECKOUT_SHIPPING) && !isset($_SESSION['jscript_enabled'])) { ?>
<?php   if ($payment_modules->doesCollectsCardDataOnsite == true) { ?>
<noscript>
  <meta http-equiv="refresh" content="0;url=<?php echo zen_href_link(FILENAME_SHOPPING_CART, 'jscript=no');?>">
</noscript>
<?php   }?>
<?php }?>
