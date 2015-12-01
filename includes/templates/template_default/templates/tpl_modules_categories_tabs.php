<?php
/**
 * Module Template - categories_tabs
 *
 * Template stub used to display categories-tabs output
 *
 * @package templateSystem
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: tpl_modules_categories_tabs.php 3395 2006-04-08 21:13:00Z ajeh $
 */

  include(DIR_WS_MODULES . zen_get_module_directory(FILENAME_CATEGORIES_TABS));
?>
<?php if ($hasCatTabLinks) { ?>
<div id="navCatTabsWrapper">
<div id="navCatTabs">
<ul>
<?php for ($i=0, $n=sizeof($links_list); $i<$n; $i++) { ?>
  <li class="<?php echo $links_list[$i]['li-class'];?>"><a class="<?php echo $links_list[$i]['a-class'];?>" href="<?php echo $links_list[$i]['href'];?>"><?php echo $links_list[$i]['text'];?></a></li>

<?php } ?>
</ul>
</div>
</div>
<?php } ?>
