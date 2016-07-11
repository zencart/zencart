<?php
/**
 * Whos Online Dashboard widget Template
 *
 * @package templateSystem
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: New in v1.6.0 $
 */
?>
<div class="row">
  <div class="col-xs-4"><?php echo TEXT_LEGEND; ?></div>
  <div class="col-xs-2"><?php echo WHOS_ONLINE_ACTIVE_TEXT; ?></div>
  <div class="col-xs-2"><?php echo WHOS_ONLINE_INACTIVE_TEXT; ?></div>
  <div class="col-xs-2"><?php echo WHOS_ONLINE_ACTIVE_NO_CART_TEXT; ?></div>
  <div class="col-xs-2"><?php echo WHOS_ONLINE_INACTIVE_NO_CART_TEXT; ?></div>
</div>
<div class="row">
  <div class="col-xs-4"><?php echo WO_REGISTERED; ?></div>
  <div class="col-xs-2"><?php echo zen_wo_get_visitor_status_icon(0); ?>&nbsp;&nbsp;<?php echo $widget['users'][0]; ?></div>
  <div class="col-xs-2"><?php echo zen_wo_get_visitor_status_icon(1); ?>&nbsp;&nbsp;<?php echo $widget['users'][1]; ?></div>
  <div class="col-xs-2"><?php echo zen_wo_get_visitor_status_icon(2); ?>&nbsp;&nbsp;<?php echo $widget['users'][2]; ?></div>
  <div class="col-xs-2"><?php echo zen_wo_get_visitor_status_icon(3); ?>&nbsp;&nbsp;<?php echo $widget['users'][3]; ?></div>
</div>
<div class="row">
  <div class="col-xs-4"><?php echo WO_GUEST; ?></div>
  <div class="col-xs-2"><?php echo zen_wo_get_visitor_status_icon(0); ?>&nbsp;&nbsp;<?php echo $widget['guests'][0]; ?></div>
  <div class="col-xs-2"><?php echo zen_wo_get_visitor_status_icon(1); ?>&nbsp;&nbsp;<?php echo $widget['guests'][1]; ?></div>
  <div class="col-xs-2"><?php echo zen_wo_get_visitor_status_icon(2); ?>&nbsp;&nbsp;<?php echo $widget['guests'][2]; ?></div>
  <div class="col-xs-2"><?php echo zen_wo_get_visitor_status_icon(3); ?>&nbsp;&nbsp;<?php echo $widget['guests'][3]; ?></div>
</div>
<div class="row">
  <div class="col-xs-4"><?php echo WO_SPIDER; ?></div>
  <div class="col-xs-2"><?php echo zen_wo_get_visitor_status_icon(0); ?>&nbsp;&nbsp;<?php echo $widget['spiders'][0]; ?></div>
  <div class="col-xs-2"><?php echo zen_wo_get_visitor_status_icon(1); ?>&nbsp;&nbsp;<?php echo $widget['spiders'][1]; ?></div>
  <div class="col-xs-2"><?php echo zen_wo_get_visitor_status_icon(2); ?>&nbsp;&nbsp;<?php echo $widget['spiders'][2]; ?></div>
  <div class="col-xs-2"><?php echo zen_wo_get_visitor_status_icon(3); ?>&nbsp;&nbsp;<?php echo $widget['spiders'][3]; ?></div>
</div>
<div class="row">
  <div class="col-xs-12">&nbsp;</div>
</div>
<div class="row">
  <div class="col-xs-12"><?php echo WO_TOTAL; ?> <?php echo $widget['total']; ?></div>
</div>
<div class="row">
  <div class="col-xs-6 right"><?php echo '<a href="' . zen_href_link(FILENAME_WHOS_ONLINE) . '">' . WO_FULL_DETAILS . '</a>'; ?></div>
</div>
