<?php
/**
 * Page Template
 *
 * Loaded by index.php?main_page=site_map
 * Displays site-map and some hard-coded navigation components
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Aug 26 Modified in v2.1.0-alpha2 $
 */
?>
<div class="centerColumn" id="siteMap">
    <h1 id="siteMapHeading"><?= HEADING_TITLE ?></h1>
<?php
if (in_array($tplSetting->DEFINE_SITE_MAP_STATUS, ['1', '2'], true)) {
?>
    <div id="siteMapMainContent" class="content">
<?php
    /**
     * require the html_define for the site_map page
     */
      require $define_page;
?>
    </div>
<?php
}
?>
    <div id="siteMapList"><?= $zen_SiteMapTree->buildTree() ?>
        <ul>
<?php
if (!empty($flag_show_about_us_sidebox_link)) {
?>
            <li><?= '<a href="' . zen_href_link(FILENAME_ABOUT_US) . '">' . BOX_INFORMATION_ABOUT_US . '</a>' ?></li>
<?php
}

if ($tplSetting->SHOW_ACCOUNT_LINKS_ON_SITE_MAP === 'Yes') {
?>
            <li><?= '<a href="' . zen_href_link(FILENAME_ACCOUNT, '', 'SSL') . '">' . PAGE_ACCOUNT . '</a>' ?><ul>
                <li><?= '<a href="' . zen_href_link(FILENAME_ACCOUNT_EDIT, '', 'SSL') . '">' . PAGE_ACCOUNT_EDIT . '</a>' ?></li>
                <li><?= '<a href="' . zen_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL') . '">' . PAGE_ADDRESS_BOOK . '</a>' ?></li>
                <li><?= '<a href="' . zen_href_link(FILENAME_ACCOUNT_HISTORY, '', 'SSL') . '">' . PAGE_ACCOUNT_HISTORY . '</a>' ?></li>
                <li><?= '<a href="' . zen_href_link(FILENAME_ACCOUNT_NEWSLETTERS, '', 'SSL') . '">' . PAGE_ACCOUNT_NOTIFICATIONS . '</a>' ?></li>
            </ul></li>
            <li><?= '<a href="' . zen_href_link(FILENAME_SHOPPING_CART) . '">' . PAGE_SHOPPING_CART . '</a>' ?></li>
            <li><?= '<a href="' . zen_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL') . '">' . PAGE_CHECKOUT_SHIPPING . '</a>' ?></li>
<?php
}
?>
            <li><?= '<a href="' . zen_href_link(FILENAME_SEARCH) . '">' . PAGE_ADVANCED_SEARCH . '</a>' ?></li>
            <li><?= '<a href="' . zen_href_link(FILENAME_PRODUCTS_ALL) . '">' . PAGE_PRODUCTS_ALL. '</a>' ?></li>
            <li><?= '<a href="' . zen_href_link(FILENAME_PRODUCTS_NEW) . '">' . PAGE_PRODUCTS_NEW . '</a>' ?></li>
            <li><?= '<a href="' . zen_href_link(FILENAME_SPECIALS) . '">' . PAGE_SPECIALS . '</a>' ?></li>
            <li><?= '<a href="' . zen_href_link(FILENAME_FEATURED_PRODUCTS) . '">' . PAGE_FEATURED . '</a>' ?></li>
            <li><?= '<a href="' . zen_href_link(FILENAME_REVIEWS) . '">' . PAGE_REVIEWS . '</a>' ?></li>
            <li><?= BOX_HEADING_INFORMATION ?><ul>
<?php
if ($tplSetting->DEFINE_SHIPPINGINFO_STATUS <= '1') {
?>
                <li><?= '<a href="' . zen_href_link(FILENAME_SHIPPING) . '">' . BOX_INFORMATION_SHIPPING . '</a>' ?></li>
<?php
}

if ($tplSetting->DEFINE_PRIVACY_STATUS <= '1') {
?>
                <li><?= '<a href="' . zen_href_link(FILENAME_PRIVACY) . '">' . BOX_INFORMATION_PRIVACY . '</a>' ?></li>
<?php
}

if ($tplSetting->DEFINE_CONDITIONS_STATUS <= '1') {
?>
                <li><?= '<a href="' . zen_href_link(FILENAME_CONDITIONS) . '">' . BOX_INFORMATION_CONDITIONS . '</a>' ?></li>
<?php
}

if (!empty($flag_show_accessibility_sidebox_link)) {
?>
                <li><?= '<a href="' . zen_href_link(FILENAME_ACCESSIBILITY) . '">' . BOX_INFORMATION_ACCESSIBILITY . '</a>' ?></li>
<?php
}

if ($tplSetting->DEFINE_CONTACT_US_STATUS <= '1') {
?>
                <li><?= '<a href="' . zen_href_link(FILENAME_CONTACT_US, '', 'SSL') . '">' . BOX_INFORMATION_CONTACT . '</a>' ?></li>
<?php
}

if (!empty($external_bb_url) && !empty($external_bb_text)) {
?>
                <li><?= '<a href="' . $external_bb_url . '" rel="noopener" target="_blank">' . $external_bb_text . '</a>' ?></li>
<?php
}

if (zen_config('MODULE_ORDER_TOTAL_GV_STATUS') === 'true') {
?>
                <li><?= '<a href="' . zen_href_link(FILENAME_GV_FAQ) . '">' . BOX_INFORMATION_GV . '</a>' ?></li>
<?php
}

if (zen_config('MODULE_ORDER_TOTAL_COUPON_STATUS') === 'true') {
?>
                <li><?= '<a href="' . zen_href_link(FILENAME_DISCOUNT_COUPON) . '">' . BOX_INFORMATION_DISCOUNT_COUPONS . '</a>' ?></li>
<?php
}

if (zen_config('SHOW_NEWSLETTER_UNSUBSCRIBE_LINK') === 'true') { 
?>
                <li><?= '<a href="' . zen_href_link(FILENAME_UNSUBSCRIBE) . '">' . BOX_INFORMATION_UNSUBSCRIBE . '</a>' ?></li>
<?php
}

if ($tplSetting->DEFINE_PAGE_2_STATUS <= '1') {
?>
                <li><?= '<a href="' . zen_href_link(FILENAME_PAGE_2) . '">' . BOX_INFORMATION_PAGE_2 . '</a>' ?></li>
<?php
}

if ($tplSetting->DEFINE_PAGE_3_STATUS <= '1') {
?>
                <li><?= '<a href="' . zen_href_link(FILENAME_PAGE_3) . '">' . BOX_INFORMATION_PAGE_3 . '</a>' ?></li>
<?php
}

if ($tplSetting->DEFINE_PAGE_4_STATUS <= '1') { ?>
                <li><?= '<a href="' . zen_href_link(FILENAME_PAGE_4) . '">' . BOX_INFORMATION_PAGE_4 . '</a>' ?></li>
<?php
}
?>
            </ul></li>
<?php
$pages_query = $db->Execute(
    "SELECT e.*, ec.*
       FROM " . TABLE_EZPAGES . " e, " . TABLE_EZPAGES_CONTENT . " ec
      WHERE e.pages_id = ec.pages_id
        AND ec.languages_id = " . (int)$_SESSION['languages_id'] . "
        AND (
            (e.status_sidebox = 1 AND e.sidebox_sort_order > 0) OR
            (e.status_header = 1 AND e.header_sort_order > 0) OR
            (e.status_footer = 1 AND e.footer_sort_order > 0) OR
            (e.status_visible = 1)
        )
      ORDER BY e.sidebox_sort_order, ec.pages_title"
);
if (!$pages_query->EOF) {
    $rows = 0;
    $page_query_list = [];
    foreach ($pages_query as $page_query) {
        $rows++;
        $page_query_list[$rows]['id'] = $page_query['pages_id'];
        $page_query_list[$rows]['name'] = $page_query['pages_title'];
        $page_query_list[$rows]['altURL'] = '';
        switch (true) {
            // external link new window or same window
            case ($page_query['alt_url_external'] !== ''):
                $page_query_list[$rows]['altURL'] = $page_query['alt_url_external'];
                break;

            // internal link new window or same window
            case ($page_query['alt_url'] !== ''):
                if (str_starts_with($page_query['alt_url'], 'http')) {
                    $page_query_list[$rows]['altURL'] = $page_query['alt_url'];
                    break;
                }
                $page_query_list[$rows]['altURL'] = zen_href_link($page_query['alt_url'], '', 'SSL', true, true, true);
                break;

            default:
                break;
        }

        // if altURL is specified, use it; otherwise, use EZPage ID to create link
        if ($page_query_list[$rows]['altURL'] !== '') {
            $page_query_list[$rows]['link'] = $page_query_list[$rows]['altURL'];
        } else {
            $page_query_list[$rows]['link'] =
                zen_href_link(FILENAME_EZPAGES, 'id=' . $page_query['pages_id'] . ($page_query['toc_chapter'] > 0 ? '&chapter=' . $page_query['toc_chapter'] : ''), 'SSL');
        }
        if ($page_query['page_open_new_window'] === '1') {
            $page_query_list[$rows]['link'] .= '" rel="noreferrer noopener" target="_blank';
        }
    }
    if (!empty($page_query_list)) {
?>
            <li><?= BOX_HEADING_EZPAGES ?><ul>
<?php
        foreach ($page_query_list as $item) {
?>
                <li><?= '<a href="' . $item['link'] . '">' . $item['name'] . '</a>' ?></li>
<?php
        }
?>
            </ul></li>
<?php
    }
}
?>
        </ul>
    </div>
    <br class="clearBoth">
    <div class="buttonRow back"><?= zen_back_link() . zen_image_button(BUTTON_IMAGE_BACK, BUTTON_BACK_ALT) . '</a>' ?></div>
</div>
