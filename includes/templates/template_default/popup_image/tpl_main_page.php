<?php
/**
 * Override Template for common/tpl_main_page.php
 *
 * @package templateSystem
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: tpl_main_page.php 2993 2006-02-08 07:14:52Z birdbrain $
 */

// Notifier hook to allow for dynamic changes to template operation
$zco_notifier->notify('NOTIFY_TPL_MAIN_PAGE_BEFORE_BODY', $body_id, $template_dir);
?>
<body id="<?php echo $body_id; ?>"<?php if ($bodyClasses) echo ' class="' . $bodyClasses . '"';?>>

<div>
<?php
  // $products_values->fields['products_image']
  echo '<a class="btn close-window" href="javascript:window.close()">' . zen_image($products_image_large, $products_values->fields['products_name'] . ' ' . TEXT_CLOSE_WINDOW) . '</a>';
?>
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
