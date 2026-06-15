<?php
/**
 * ezpages functions - used to prepare links for EZ-Pages
 *
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2025 Sep 18 Modified in v2.2.0 $
 */

/**
 * look up page_id and create a storefront link for ez_pages
 * to use this link add '\<a href="' . zen_ez_pages_link($pages_id) . '">\</a>';
 *
 * Note:
 *   - The $ez_pages_is_ssl input is no longer used, but is kept for backwards compatibility.
 *   - The function is valid **only** on the storefront; use in the admin will result in a FATAL error.
 *
 * @since ZC v1.3.0
 */
function zen_ez_pages_link(
    int|string $ez_pages_id,
    int|string $ez_pages_chapter = 0,
    bool $ez_pages_is_ssl = true,
    bool $ez_pages_open_new_window = false,
    bool $ez_pages_return_full_url = false
): string {
    // -----
    // Function is only available on the storefront.
    //
    if (IS_ADMIN_FLAG !== false) {
        trigger_error("FATAL ERROR: zen_ez_pages_link is a storefront-only function.", \E_USER_WARNING);
        zen_exit();
    }

    global $db;
    $ez_link = 'unknown';
    $ez_pages_name = 'Click Here';

    if ((int)$ez_pages_chapter === 0) {
        $page_query = $db->Execute(
            "SELECT *
               FROM " . TABLE_EZPAGES . " e, " . TABLE_EZPAGES_CONTENT . " ec
              WHERE e.pages_id = ec.pages_id
                AND ec.languages_id = " . (int)$_SESSION['languages_id'] . "
                AND e.pages_id = " . (int)$ez_pages_id . "
              LIMIT 1"
        );

        $ez_pages_name = $page_query->fields['pages_title'];
        $ez_pages_alturl = $page_query->fields['alt_url'];
        $ez_pages_chapter = $page_query->fields['toc_chapter'];
        $ez_pages_external = $page_query->fields['alt_url_external'];

        $ez_link = '';
        if ($ez_pages_external !== '') {
            $ez_link = $ez_pages_external;
        } elseif ($ez_pages_alturl !== '') {
            $ez_link = (str_starts_with($ez_pages_alturl, 'http')) ? $ez_pages_alturl : zen_href_link($ez_pages_alturl, '', 'SSL', true, true, true);
        } else {
            $ez_link = zen_href_link(FILENAME_EZPAGES, 'id=' . $ez_pages_id . ((int)$ez_pages_chapter !== 0 ? '&chapter=' . $ez_pages_chapter : ''));
        }

        $ez_link .= ($ez_pages_open_new_window === '1' ? '" rel="noopener" target="_blank' : '');
    }

    if ($ez_pages_return_full_url === false) {
        return $ez_link;
    } else {
        return '<a href="' . $ez_link . '">' . zen_output_string_protected($ez_pages_name) . '</a>';
    }
}
