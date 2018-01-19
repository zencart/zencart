<?php
/**
 * jscript_main
 *
 * @package page
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: Ian Wilson   New in v1.6.0 $
 */
?>

<script>
jQuery(document).ready(function(){
  jQuery('form[name="checkout_payment"]').submit(function() {
      jQuery('#paymentSubmit').attr('disabled', true);
<?php if ($flagOnSubmit) { ?>
      formPassed = check_form();
      if (formPassed == false) {
          jQuery('#paymentSubmit').attr('disabled', false);
      }
      return formPassed;
<?php } ?>
  });
});
</script>
