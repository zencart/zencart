<?php
/**
 * Module Template - categories_tabs
 *
 * Template stub used to display categories-tabs output
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Sep 02 Modified in v2.1.0-beta1 $
 */

include DIR_WS_MODULES . zen_get_module_directory(FILENAME_CATEGORIES_TABS);

if (CATEGORIES_TABS_STATUS === '1' && (!empty($links_list) || !empty($links_list_by_category))) {
?>

<div id="navCatTabsWrapper">
<div id="navCatTabs">
<ul>
<?php foreach (($links_list_by_category ?? $links_list) as $link_key => $link_val) { ?>
    <?php
    // Since v2.1.0, if $links_list_by_category is not empty,
    // then $link_key is the category_id prefixed by the letter 'c'.
    // So, $category_id = ltrim($link_key, 'c')
    // ... which can then be used to query alternate details about the category or its products
    // ... and therefore can be used inside this loop to do more things with this menu
    ?>
    <li><?php echo $link_val;?></li>
<?php } ?>
</ul>
</div>
</div>
<?php } ?>

