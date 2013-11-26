<?php
/**
 * @package Installer
 * @access private
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: DrByte  Fri Oct 19 16:21:34 2012 -0400 Modified in v1.5.2 $
 */

  if ($zc_install->error) include(DIR_WS_INSTALL_TEMPLATE . 'templates/display_errors.php');
?>

    <form method="post" action="index.php?main_page=admin_setup<?php echo zcInstallAddSID(); ?>">
    <fieldset>
    <legend><strong><?php echo ADMIN_INFORMATION; ?></strong></legend>
    <div class="section">
      <input type="text" id="admin_username" name="admin_username" tabindex="1" value="<?php echo ADMIN_USERNAME_VALUE; ?>" autocomplete="off"/>
      <label for="admin_username"><?php echo ADMIN_USERNAME; ?></label>
      <p><?php echo ADMIN_USERNAME_INSTRUCTION . '<a href="javascript:popupWindow(\'popup_help_screen.php?error_code=51\')"> ' . TEXT_HELP_LINK . '</a>'; ?></p>
    </div>
    <div class="section">
      <input type="password" id="admin_pass" name="admin_pass" tabindex="2" autocomplete="off" />
      <label for="admin_pass"><?php echo ADMIN_PASS; ?></label>
      <p><?php echo ADMIN_PASS_INSTRUCTION . '<a href="javascript:popupWindow(\'popup_help_screen.php?error_code=53\')"> ' . TEXT_HELP_LINK . '</a>'; ?></p>
    </div>
    <div class="section">
      <input type="password" id="admin_pass_confirm" name="admin_pass_confirm" tabindex="3" autocomplete="off" />
      <label for="admin_pass_confirm"><?php echo ADMIN_PASS_CONFIRM; ?></label>
      <p><?php echo ADMIN_PASS_CONFIRM_INSTRUCTION . '<a href="javascript:popupWindow(\'popup_help_screen.php?error_code=54\')"> ' . TEXT_HELP_LINK . '</a>'; ?></p>
    </div>
    <div class="section">
      <input type="text" id="admin_email" name="admin_email" tabindex="4" value="<?php echo ADMIN_EMAIL_VALUE; ?>" autocomplete="off" />
      <label for="admin_email"><?php echo ADMIN_EMAIL; ?></label>
      <p><?php echo ADMIN_EMAIL_INSTRUCTION . '<a href="javascript:popupWindow(\'popup_help_screen.php?error_code=52\')"> ' . TEXT_HELP_LINK . '</a>'; ?></p>
    </div>
    </fieldset>

    <fieldset>
      <legend><strong><?PHP echo UPGRADE_DETECTION; ?></strong></legend>
      <div class="input">
      <input name="check_for_updates" type="checkbox" id="checkbox1" value="1"  tabindex="10" checked="checked"/>
      <label for="checkbox1"><?php echo UPGRADE_INSTRUCTION_TITLE; ?></label>
      <p><?php echo UPGRADE_INSTRUCTION_TEXT; ?></p>
    </div>
    </fieldset>
    <input type="submit" name="submit" class="button" tabindex="20" value="<?php echo SAVE_ADMIN_SETTINGS; ?>" />
    </form>