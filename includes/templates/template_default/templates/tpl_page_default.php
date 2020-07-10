<?php
/**
 * Page Template
 *
 * This is the template used for EZ-Pages content display.  It is named "tpl_page_default" instead of ezpage for friendlier appearance
 *
 * @package templateSystem
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Author: DrByte  Sun Oct 18 13:09:57 2015 -0400 Modified in v1.5.5 $
 */
?>
<div class="centerColumn" id="ezPageDefault">
<h1 id="ezPagesHeading"><?php echo $var_pageDetails->fields['pages_title']; ?></h1>

<?php if (EZPAGES_SHOW_PREV_NEXT_BUTTONS=='2' and $counter > 1) { ?>
<div id="navEZPageNextPrev">
      <a href="<?php echo $prev_link; ?>"><?php echo $previous_button; ?></a>
      <?php echo zen_back_link() . $home_button; ?></a>
      <a href="<?php echo $next_link; ?>"><?php echo $next_item_button; ?></a>
    </div>
<?php } elseif (EZPAGES_SHOW_PREV_NEXT_BUTTONS=='1') { ?>
    <div id="navEZPageNextPrev"><?php echo zen_back_link() . $home_button . '</a>'; ?></div>
<?php  } ?>

<?php

// vertical TOC listing
// create a table of contents for chapter when more than 1 page in the TOC
  if (sizeof($toc_links) > 1 and EZPAGES_SHOW_TABLE_CONTENTS == '1') {?>
<div id="navEZPagesTOCWrapper">
<h2 id="ezPagesTOCHeading"><?php echo TEXT_EZ_PAGES_TABLE_CONTEXT; ?></h2>
<div id="navEZPagesTOC">
<ul class="list">
<?php foreach($toc_links as $link) {
// could be used to change classes on current link and toc (table of contents) links
      if ($link['pages_id'] == $_GET['id']) { ?>

<li><?php echo CURRENT_PAGE_INDICATOR; ?><a href="<?php echo zen_ez_pages_link($link['pages_id']);?>"><?php echo $link['pages_title']; ?></a></li>

<?php } else { ?>

<li><?php echo NOT_CURRENT_PAGE_INDICATOR; ?><a href="<?php echo zen_ez_pages_link($link['pages_id']); ?>"><?php echo $link['pages_title']; ?></a></li>
<?php
      }
    } ?>
</ul>
</div>
</div>
<?php
    }
?>
    <div><?php echo $var_pageDetails->fields['pages_html_text']; ?></div>
</div>
