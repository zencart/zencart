<?php
/**
 * @package Installer
 * @access private
 * @copyright Copyright 2003-2007 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: license_default.php 6981 2007-09-12 18:26:56Z drbyte $
 */

  if ($zc_install->error) include(DIR_WS_INSTALL_TEMPLATE . 'templates/display_errors.php');
?>
<iframe src="includes/templates/template_default/templates/gpl_license.html"></iframe>
<form method="post" action="index.php?main_page=license<?php echo zcInstallAddSID(); ?>">
  <input type="radio" name="license_consent" id="agree" value="agree" />
  <label for="agree"><?php echo AGREE; ?></label><br />
  <input type="radio" name="license_consent" id="disagree" checked="checked" value="disagree" />
  <label for="disagree"><?php echo DISAGREE; ?></label><br />
  <input type="submit" name="submit" class="button" value="<?php echo INSTALL; ?>" />
</form>