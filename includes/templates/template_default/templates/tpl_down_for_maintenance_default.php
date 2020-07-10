<?php
/**
 * Page Template
 *
 * Loaded automatically by index.php?main_page=down_for_maintenance.<br />
 * When site is down for maintenance (and database is still active), this page is displayed to the customer
 *
 * @package templateSystem
 * @copyright Copyright 2003-2005 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: tpl_down_for_maintenance_default.php 2786 2006-01-05 01:52:38Z birdbrain $
 */
?>
<!-- body_text //-->
<div class="centerColumn" id="maintenanceDefault">

<h1 id="maintenanceDefaultHeading"><?php echo HEADING_TITLE; ?></h1>

<div class="forward"><?php echo zen_image(DIR_WS_TEMPLATE_IMAGES . OTHER_IMAGE_DOWN_FOR_MAINTENANCE, OTHER_DOWN_FOR_MAINTENANCE_ALT); ?></div>

<h2 id="maintenanceDefaultMainContent"><?php echo DOWN_FOR_MAINTENANCE_TEXT_INFORMATION; ?></h2>

<?php if (DISPLAY_MAINTENANCE_TIME == 'true') { ?>
<h3 id="maintenanceDefaultTime"><?php echo TEXT_MAINTENANCE_ON_AT_TIME . '<br />' . TEXT_DATE_TIME; ?></h3>
<?php } ?>
<?php if (DISPLAY_MAINTENANCE_PERIOD == 'true') { ?>
<h3 id="maintenanceDefaultPeriod"><?php echo TEXT_MAINTENANCE_PERIOD . TEXT_MAINTENANCE_PERIOD_TIME; ?></h3>
<?php } ?>
<br class="clearBoth" />

<div class="buttonRow forward"><?php echo DOWN_FOR_MAINTENANCE_STATUS_TEXT; ?></div>
<br class="clearBoth" />
<div class="buttonRow forward"><a href="<?php echo zen_href_link(FILENAME_DEFAULT); ?>"><?php echo zen_image_button(BUTTON_IMAGE_CONTINUE, BUTTON_CONTINUE_ALT); ?></a></div>
<br class="clearBoth" />
<!-- body_text_eof //-->
</div>
