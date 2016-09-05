<?php
/**
 * @package templateSystem
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:   New in v1.6.0 $
 */
?>
<?php if ($tplVars['pageDefinition']['action'] == 'list') { ?>
    <?php echo TEXT_LEGEND; ?>&nbsp;
    <?php echo zen_image(DIR_WS_IMAGES . 'icon_status_green.gif') . TEXT_LEGEND_TAX_AND_ZONES; ?>&nbsp;&nbsp;&nbsp;
    <?php echo zen_image(DIR_WS_IMAGES . 'icon_status_yellow.gif') . TEXT_LEGEND_ONLY_ZONES; ?>&nbsp;&nbsp;&nbsp;
    <?php echo zen_image(DIR_WS_IMAGES . 'icon_status_red.gif') . TEXT_LEGEND_NOT_CONF; ?>&nbsp;&nbsp;&nbsp;<br/>
<?php } ?>
