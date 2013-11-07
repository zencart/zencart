<?php
/**
 * Override Template for common/tpl_main_page.php
 *
 * @package templateSystem
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: tpl_main_page.php 3155 2006-03-10 23:19:39Z drbyte $
 */

// Notifier hook to allow for dynamic changes to template operation
$zco_notifier->notify('NOTIFY_TPL_MAIN_PAGE_BEFORE_BODY', $body_id, $template_dir);
?>
<body id="<?php echo $body_id; ?>"<?php if ($bodyClasses) echo ' class="' . $bodyClasses . '"';?>>

<div class="shippingEstimatorWrapper biggerText">
<p><a class="btn close-window" href="javascript:window.close();"><?php echo TEXT_CURRENT_CLOSE_WINDOW; ?></a></p>

<?php require(DIR_WS_MODULES . zen_get_module_directory('shipping_estimator.php')); ?>

<p><a class="btn close-window" href="javascript:window.close();"><?php echo TEXT_CURRENT_CLOSE_WINDOW; ?></a></p>
</div>


<?php
/*************** JAVASCRIPT OUTPUT before </body> close ************************/
  // run dynamically-generated jscript_XXXXX.php files
  foreach ($jscripts as $val) {
    require($val);
    echo "\n";
  }

  // output src links to .js scripts
  echo $template_js_output_bottom;
?>
</body>
</html>