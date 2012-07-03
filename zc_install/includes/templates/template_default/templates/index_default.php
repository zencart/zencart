<?php
/**
 * @package Installer
 * @access private
 * @copyright Copyright 2003-2007 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: index_default.php 7414 2007-11-11 06:18:50Z drbyte $
 */

  if ($zc_install->error) include(DIR_WS_INSTALL_TEMPLATE . 'templates/display_errors.php');
?>
<iframe src="includes/templates/template_default/templates/about_zencart.html"></iframe>
<form method="post" action="index.php?main_page=license<?php echo zcInstallAddSID(); ?>">
  <input type="submit" name="submit" class="button" value="<?php echo INSTALL; ?>" />
</form>