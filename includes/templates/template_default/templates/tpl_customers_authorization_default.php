<?php
/**
 * Page Template
 *
 * Loaded automatically by index.php?main_page=customers_authorization.
 * Displays information if customer authorization checks fail.
 *
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Dec 25 Modified in v1.5.8-alpha $
 */
?>
<div class="centerColumn" id="customerAuthDefault">
    <h1 id="customerAuthDefaultHeading"><?= $customer_authorization_heading_title ?></h1>
<?php
if ($messageStack->size('account') > 0) {
    echo $messageStack->output('account');
}
?>
    <div id="customerAuthDefaultImage">
        <?= zen_image(DIR_WS_TEMPLATE_IMAGES . OTHER_IMAGE_CUSTOMERS_AUTHORIZATION, OTHER_IMAGE_CUSTOMERS_AUTHORIZATION_ALT) ?>
    </div>

    <div id="customerAuthDefaultMainContent" class="content">
        <?= $main_content ?>
    </div>

    <div id="customerAuthDefaultSecondaryContent" class="content"><?= CUSTOMERS_AUTHORIZATION_STATUS_TEXT ?></div>

    <div class="buttonRow forward">
        <a href="<?= zen_href_link(CUSTOMERS_AUTHORIZATION_FILENAME, '', 'SSL') ?>">
            <?= zen_image_button(BUTTON_IMAGE_CONTINUE, BUTTON_CONTINUE_ALT) ?>
        </a>
    </div>
</div>
