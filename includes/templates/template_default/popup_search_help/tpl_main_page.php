<?php
/**
 * Override Template for common/tpl_main_page.php
 *
 * @package templateSystem
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: DrByte  Thu Jul 12 12:12:23 2012 -0400 Modified in v1.5.1 $
 */

// Notifier hook to allow for dynamic changes to template operation
$zco_notifier->notify('NOTIFY_TPL_MAIN_PAGE_BEFORE_BODY', $body_id, $template_dir);
?>
<body id="<?php echo $body_id; ?>"<?php if ($bodyClasses) echo ' class="' . $bodyClasses . '"';?>>

<p><a class="btn close-window" href="javascript:window.close();"><?php echo TEXT_CURRENT_CLOSE_WINDOW; ?></a></p>

<br clear="all">
<div><?php echo TEXT_SEARCH_HELP ?></div>

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
