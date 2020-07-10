<?php
/**
 * Page Template
 *
 * Displays EZ-Pages footer-bar content.<br />
 *
 * @package templateSystem
 * @copyright Copyright 2003-2019 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2019 Jan 04 Modified in v1.5.6a $
 */

   /**
   * require code to show EZ-Pages list
   */
  include(DIR_WS_MODULES . zen_get_module_directory('ezpages_bar_footer.php'));
?>
<?php if (!empty($var_linksList)) { ?>
<?php for ($i=1, $n=sizeof($var_linksList); $i<=$n; $i++) {  ?>
<?php echo ($i <= $n ? EZPAGES_SEPARATOR_FOOTER : '') . "\n"; ?>
  <a href="<?php echo $var_linksList[$i]['link']; ?>"><?php echo $var_linksList[$i]['name']; ?></a>
<?php } // end FOR loop ?>
<?php } ?>
