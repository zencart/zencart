<?php
/**
 * Page Template
 *
 * Displays EZ-Pages Header-Bar content.<br />
 *
 * @package templateSystem
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: picaflor-azul Modified in v1.5.5 $
 */

  /**
   * require code to show EZ-Pages list
   */
  include(DIR_WS_MODULES . zen_get_module_directory('ezpages_bar_header.php'));
?>
<?php if (sizeof($var_linksList) >= 1) { ?>
<div id="navEZPagesTop">
  <ul>
<?php for ($i=1, $n=sizeof($var_linksList); $i<=$n; $i++) {  ?>
    <li><a href="<?php echo $var_linksList[$i]['link']; ?>"><?php echo $var_linksList[$i]['name']; ?></a></li>
<?php } // end FOR loop ?>
  </ul>
</div>
<?php } ?>
