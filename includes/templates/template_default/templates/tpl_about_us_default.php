<?php

declare(strict_types=1);
/**
 * Page Template
 *
 * Loaded automatically by index.php?main_page=about_us
 * Displays About Us page.
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Simon 2023 Sep 07 Modified in v2.0.0-alpha1 $
 */
?>
<div class="centerColumn" id="aboutUs">
    <h1 id="aboutUsHeading" class="pageHeading"><?= HEADING_TITLE ?></h1>
    <div id="aboutUsContent" class="content">
        <?php
        /**
         * require the html_define for the about_us page
         */
        require $define_page;
        ?>
    </div>
    <div class="buttonRow back"><?= zen_back_link() . zen_image_button(BUTTON_IMAGE_BACK, BUTTON_BACK_ALT) . '</a>' ?></div>
</div>
