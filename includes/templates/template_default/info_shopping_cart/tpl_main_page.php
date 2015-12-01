<?php
/**
 * Override Template for common/tpl_main_page.php
 *
 * @package templateSystem
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: tpl_main_page.php 3056 2006-02-21 06:41:36Z birdbrain $
 */
// Notifier hook to allow for dynamic changes to template operation
$zco_notifier->notify('NOTIFY_TPL_MAIN_PAGE_BEFORE_BODY', $body_id, $template_dir);
?>
<body id="<?php echo $body_id; ?>"<?php if ($bodyClasses) echo ' class="' . $bodyClasses . '"';?>>

<p><a class="btn close-window" href="javascript:window.close();"><?php echo TEXT_CURRENT_CLOSE_WINDOW; ?></a></p>
<div>
<h1><?php echo HEADING_TITLE; ?></h1>
<h2><?php echo SUB_HEADING_TITLE_1; ?></h2>
<p><?php echo SUB_HEADING_TEXT_1; ?></p>
<h2><?php echo SUB_HEADING_TITLE_2; ?></h2>
<p><?php echo SUB_HEADING_TEXT_2; ?></p>
<h2><?php echo SUB_HEADING_TITLE_3; ?></h2>
<p><?php echo SUB_HEADING_TEXT_3; ?></p>
</div>
<p><a class="btn close-window" href="javascript:window.close();"><?php echo TEXT_CURRENT_CLOSE_WINDOW; ?></a></p>

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
