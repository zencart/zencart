<?php
/**
 * Page Template
 *
 * Displays EZ-Pages Header-Bar content.
 *
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Dec 25 Modified in v1.5.8-alpha $
 */

  /**
   * require code to show EZ-Pages list
   */
  include(DIR_WS_MODULES . zen_get_module_directory('ezpages_bar_header.php'));
?>
<?php if (!empty($var_linksList)) { ?>
<div id="navEZPagesTop">
  <ul>
<?php for ($i=1, $n=sizeof($var_linksList); $i<=$n; $i++) {  ?>
    <li><a href="<?php echo $var_linksList[$i]['link']; ?>"><?php echo $var_linksList[$i]['name']; ?></a></li>
<?php } // end FOR loop ?>
  </ul>
</div>
<?php } ?>
