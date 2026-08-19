<?php

declare(strict_types=1);
/**
 * Page Template
 *
 * Loaded automatically by index.php?main_page=search.
 * Displays options fields upon which a product search will be run
 *
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Dec 25 New in v1.5.8-alpha $
 */
?>
<div class="centerColumn" id="searchDefault">
    <?= zen_draw_form('search', zen_href_link(FILENAME_SEARCH_RESULT, '', '', false), 'get', 'onsubmit="return check_form(this);"') . zen_hide_session_id() ?>
    <?= zen_draw_hidden_field('main_page', FILENAME_SEARCH_RESULT) ?>

    <h1 id="searchDefaultHeading"><?= HEADING_TITLE_1 ?></h1>

    <?php
    if ($messageStack->size('search') > 0) {
        echo $messageStack->output('search');
    } ?>

    <fieldset>
        <legend><?= HEADING_SEARCH_CRITERIA ?></legend>
        <div class="forward"><?= '<a href="javascript:popupWindow(\'' . zen_href_link(FILENAME_POPUP_SEARCH_HELP) . '\')">' . TEXT_SEARCH_HELP_LINK . '</a>' ?></div>
        <br class="clearBoth">

        <div class="centeredContent"><?= zen_draw_input_field('keyword', $sData['keyword'], 'placeholder="' . KEYWORD_FORMAT_STRING . '" autofocus aria-label="' . KEYWORD_FORMAT_STRING . '"', 'search') ?>
            &nbsp;&nbsp;&nbsp;<?= zen_draw_checkbox_field('search_in_description', '1', $sData['search_in_description'], 'id="search-in-description"') ?>
            <label class="checkboxLabel" for="search-in-description"><?= TEXT_SEARCH_IN_DESCRIPTION ?></label></div>
        <br class="clearBoth">
    </fieldset>

    <fieldset class="floatingBox back">
        <legend><?= ENTRY_CATEGORIES ?></legend>
        <div class="floatLeft"><?= zen_draw_pull_down_menu('categories_id', zen_get_categories([['id' => '', 'text' => TEXT_ALL_CATEGORIES]], '0', '', '1'), $sData['categories_id'], 'id="searchCategoryId" aria-label="' . PLEASE_SELECT . '"') ?></div>
        <?= zen_draw_checkbox_field('inc_subcat', '1', $sData['inc_subcat'], 'id="inc-subcat"') ?><label class="checkboxLabel" for="inc-subcat"><?= ENTRY_INCLUDE_SUBCATEGORIES ?></label>
        <br class="clearBoth">
    </fieldset>

    <?php
    if (empty($skip_manufacturers)) { ?>
        <fieldset class="floatingBox forward">
            <legend><?= ENTRY_MANUFACTURERS ?></legend>
            <?= zen_draw_pull_down_menu('manufacturers_id', zen_get_manufacturers([['id' => '', 'text' => TEXT_ALL_MANUFACTURERS]], zen_config('PRODUCTS_MANUFACTURERS_STATUS')), $sData['manufacturers_id'], 'id="searchMfgId" aria-label="' . PLEASE_SELECT . '"') ?>
            <br class="clearBoth">
        </fieldset>
    <?php
    } ?>
    <br class="clearBoth">

    <fieldset class="floatingBox back">
        <legend><?= ENTRY_PRICE_RANGE ?></legend>
        <fieldset class="floatLeft">
            <label for="pfrom"><?= ENTRY_PRICE_FROM ?></label>
            <?= zen_draw_input_field('pfrom', $sData['pfrom'], 'id="pfrom" inputmode="decimal"') ?>
        </fieldset>
        <fieldset class="floatLeft">
            <label for="pto"><?= ENTRY_PRICE_TO ?></label>
            <?= zen_draw_input_field('pto', $sData['pto'], 'id="pto" inputmode="decimal"') ?>
        </fieldset>
    </fieldset>

    <fieldset class="floatingBox forward">
        <legend><?= ENTRY_DATE_RANGE ?></legend>
        <fieldset class="floatLeft">
            <label for="dfrom"><?= ENTRY_DATE_FROM ?></label>
            <?= zen_draw_input_field('dfrom', $sData['dfrom'], 'id="dfrom" placeholder="' . DOB_FORMAT_STRING . '" onfocus="RemoveFormatString(this, \'' . DOB_FORMAT_STRING . '\')"') ?>
        </fieldset>
        <fieldset class="floatLeft">
            <label for="dto"><?= ENTRY_DATE_TO ?></label>
            <?= zen_draw_input_field('dto', $sData['dto'], 'id="dto" placeholder="' . DOB_FORMAT_STRING . '" onfocus="RemoveFormatString(this, \'' . DOB_FORMAT_STRING . '\')"') ?>
        </fieldset>
    </fieldset>

    <br class="clearBoth">
    <div class="buttonRow forward"><?= zen_image_submit(BUTTON_IMAGE_SEARCH, BUTTON_SEARCH_ALT) ?></div>
    <div class="buttonRow back"><?= zen_back_link() . zen_image_button(BUTTON_IMAGE_BACK, BUTTON_BACK_ALT) . '</a>' ?></div>
    <?= '</form>' ?>
</div>
