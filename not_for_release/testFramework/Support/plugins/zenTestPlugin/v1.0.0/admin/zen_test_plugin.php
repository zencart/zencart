<?php
require 'includes/application_top.php';
?>

<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <?php require DIR_WS_INCLUDES . 'admin_html_head.php'; ?>
  </head>
  <body>
    <!-- header //-->
    <?php require DIR_WS_INCLUDES . 'header.php'; ?>
    <!-- header_eof //-->

    <!-- body //-->
    <div class="container-fluid">
    <?php echo '<h1>' . ZEN_TEST_PLUGIN_TEST_STRING . '</h1>'; ?>
    <?php echo '<h1>' . TEXT_DOCS_HELP . '</h1>'; ?>
   </div>

   
    <?php require DIR_WS_INCLUDES . 'footer.php'; ?>
    <!-- footer_eof //-->
  </body>
</html>


<?php 
require(DIR_WS_INCLUDES . 'application_bottom.php'); 
?>
