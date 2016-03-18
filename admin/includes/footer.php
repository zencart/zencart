<?php
/**
 * @package admin
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Author: DrByte  Fri Feb 19 20:48:40 2016 -0500 Modified in v1.5.5 $
 */

// check and display zen cart version and history version in footer
  $current_sinfo = PROJECT_VERSION_NAME . ' v' . PROJECT_VERSION_MAJOR . '.' . PROJECT_VERSION_MINOR . '/';
  $check_hist_query = "SELECT * from " . TABLE_PROJECT_VERSION . " WHERE project_version_key = 'Zen-Cart Database' ORDER BY project_version_date_applied DESC LIMIT 1";
  $check_hist_details = $db->Execute($check_hist_query);
  if (!$check_hist_details->EOF) {
    $current_sinfo .=  'v' . $check_hist_details->fields['project_version_major'] . '.' . $check_hist_details->fields['project_version_minor'];
    if (zen_not_null($check_hist_details->fields['project_version_patch1'])) $current_sinfo .= '&nbsp;&nbsp;Patch: ' . $check_hist_details->fields['project_version_patch1'];
  }
?>
<footer>
  <div id="footer">
    <a href="http://www.zen-cart.com" target="_blank"><img src="images/small_zen_logo.gif" alt="Zen Cart:: the art of e-commerce" border="0"></a><br />
    <br />
    E-Commerce Engine Copyright &copy; 2003-<?php echo date('Y'); ?> <a href="http://www.zen-cart.com" target="_blank">Zen Cart&reg;</a><br />
    <?php echo '<a href="' . zen_href_link(FILENAME_SERVER_INFO) . '">' . $current_sinfo . '</a>'; ?>
  </div>
</footer>
