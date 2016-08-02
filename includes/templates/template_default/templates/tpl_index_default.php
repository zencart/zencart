<?php
/**
 * Page Template
 *
 * Main index page<br />
 * Displays greetings, welcome text (define-page content), and various centerboxes depending on switch settings in Admin<br />
 * Centerboxes are called as necessary
 *
 * @package templateSystem
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */
?>
<div class="centerColumn" id="indexDefault">
<h1 id="indexDefaultHeading"><?php echo HEADING_TITLE; ?></h1>

<?php if (SHOW_CUSTOMER_GREETING == 1) { ?>
<h2 class="greeting"><?php echo zen_customer_greeting(); ?></h2>
<?php } ?>

<?php if (DEFINE_MAIN_PAGE_STATUS >= 1 and DEFINE_MAIN_PAGE_STATUS <= 2) { ?>
<?php
/**
 * get the Define Main Page Text
 */
?>
<div id="indexDefaultMainContent" class="content"><?php require($define_page); ?></div>
<?php } ?>

<?php 
   function mainBoxSort($a, $b) {
     if ($a['formatter']['sortMainPage'] == $b['formatter']['sortMainPage'])
        return 0;
     if ($a['formatter']['sortMainPage'] < $b['formatter']['sortMainPage'])
        return -1;
     return 1;
   }
   usort($tplVars['listingBoxes'], "mainBoxSort");
   foreach ($tplVars['listingBoxes'] as $tplVars['listingBox']) {
     if ($tplVars['listingBox']['formatter']['sortMainPage'] != 0) { 
        require($template->get_template_dir($tplVars['listingBox']['formatter']['template'], DIR_WS_TEMPLATE, $current_page_base, 'listingboxes') . '/' . $tplVars['listingBox']['formatter']['template']); 
     }
   }
?>

</div>
