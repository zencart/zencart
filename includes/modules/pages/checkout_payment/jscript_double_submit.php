<?php 
  // For payment modules using AJAX for security, confirmation page JS is 
  // not loaded.  So load the relevant file here. 
  if (PADSS_AJAX_CHECKOUT == '1') { 
    require(DIR_WS_MODULES . '/pages/checkout_confirmation/jscript_double_submit.php'); 
  }
