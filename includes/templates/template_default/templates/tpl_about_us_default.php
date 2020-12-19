<?php
/**
 * Page Template
 *
 * Loaded automatically by index.php?main_page=about_us
 * Displays About Us page.
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: tpl_about_us_default.php  New in v1.5.8 $
 */
?>
<div class="centerColumn" id="aboutUs">
    <h1 id="aboutUsHeading" class="pageHeading"><?php echo HEADING_TITLE; ?></h1>

    <div id="aboutUsContent" class="content">


        <?php
        /**
         * require the html_define for the about_us page
         */
        if (!empty($define_page)) require($define_page);
        ?>


    </div>

</div>
