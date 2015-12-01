<?php
/**
 * jscript_main
 *
 * @package page
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: Ian Wilson   New in v1.6.0 $
 */
?>

<script>
$(document).ready(function(){
  $('form[name="checkout_payment"]').submit(function() {
      $('.paymentSubmit').attr('disabled', true);
<?php if ($flagOnSubmit) { ?>
      formPassed = check_form();
      if (formPassed == false) {
          $('.paymentSubmit').attr('disabled', false);
      }
      return formPassed;
<?php } ?>
  });
});
</script>
