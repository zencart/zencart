<?php
/**
 * Module Template - categories_tabs
 *
 * Template stub used to display categories-tabs output
 *
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Jul 10 Modified in v1.5.8-alpha $
 */

  include(DIR_WS_MODULES . zen_get_module_directory(FILENAME_CATEGORIES_TABS));
?>
<?php if (CATEGORIES_TABS_STATUS == '1' && sizeof($links_list) >= 1) { ?>
<div id="navCatTabsWrapper">
<div id="navCatTabs">
<ul>
<?php for ($i=0, $n=sizeof($links_list); $i<$n; $i++) { ?>
  <li><?php echo $links_list[$i];?></li>
<?php } ?>
</ul>
</div>
</div>
<?php } ?>