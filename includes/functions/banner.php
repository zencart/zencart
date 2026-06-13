<?php
/**
 * banner functions
 *
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2025 Sep 18 Modified in v2.2.0 $
 */

/**
 * Set the status of a specified banner
 * @since ZC v1.0.3
 */
function zen_set_banner_status(int|string $banners_id, int|string $status): int|queryFactoryResult
{
    if ((int)$status !== 0 && (int)$status !== 1) {
        return -1;
    }
    global $db;
    $sql = "UPDATE " . TABLE_BANNERS;
    $sql .= ((int)$status === 1) ? " SET status = 1, date_scheduled = NULL" : " SET status = 0";
    $sql .= ", date_status_change = now() WHERE banners_id = '" . (int)$banners_id . "'";
    return $db->Execute($sql);
}

/**
 * Activate any unactivated banners whose scheduled start date is set to a time before "now".
 * @since ZC v1.0.3
 */
function zen_activate_banners(): void
{
    global $db;
    $banners_query = "SELECT banners_id, date_scheduled
                      FROM " . TABLE_BANNERS . "
                      WHERE date_scheduled IS NOT NULL";
    $banners = $db->Execute($banners_query);

    if ($banners->RecordCount() > 0) {
        foreach ($banners as $banner) {
            if (date('Y-m-d H:i:s') >= $banner['date_scheduled']) {
                zen_set_banner_status($banner['banners_id'], 1);
            }
        }
    }
}

/**
 * Expire any banners whose expiry date has recently passed
 * @since ZC v1.0.3
 */
function zen_expire_banners(): void
{
    global $db;
    $banners_query = "SELECT b.banners_id, b.expires_date, b.expires_impressions,
                             SUM(bh.banners_shown) AS banners_shown
                      FROM " . TABLE_BANNERS . " b, " . TABLE_BANNERS_HISTORY . " bh
                      WHERE b.status = 1
                      AND b.banners_id = bh.banners_id
                      GROUP BY b.banners_id, b.expires_date, b.expires_impressions";

    $banners = $db->Execute($banners_query);

    foreach ($banners as $banner) {
        if (zen_not_null($banner['expires_date'])) {
            if (date('Y-m-d H:i:s') >= $banner['expires_date']) {
                zen_set_banner_status($banner['banners_id'], 0);
            }
        } elseif (!empty($banner['expires_impressions']) && $banner['banners_shown'] >= $banner['expires_impressions']) {
            zen_set_banner_status($banner['banners_id'], 0);
        }
    }
}

/**
 * Given a specific action and group or banner-id identifier, display a relevant banner.
 * @since ZC v1.0.3
 */
function zen_display_banner(string $action = '', string|queryFactoryResult|null $identifier = ''): string
{
    if (empty($identifier) || empty($action)) {
        return false;
    }
    global $db;

    $my_banner_filter = '';

    if ($action === 'dynamic') {
        $new_banner_search = zen_build_banners_group($identifier);

        $banners_query = "SELECT count(*) AS count
                          FROM " . TABLE_BANNERS . "
                           WHERE status = '1' " .
                             $new_banner_search . $my_banner_filter;

        $banners = $db->Execute($banners_query);

        if ($banners->fields['count'] > 0) {
            $banner = $db->Execute("SELECT banners_id, banners_title, banners_image, banners_html_text, banners_open_new_windows, banners_url
                               FROM " . TABLE_BANNERS . "
                               WHERE status = 1 " .
                                   $new_banner_search . $my_banner_filter . " ORDER BY rand()");

        } else {
            return '<p class="alert">ZEN ERROR! (zen_display_banner(' . $action . ', ' . $identifier . ') -> No banners with group \'' . $identifier . '\' found!</p>';
        }
    } elseif ($action === 'static') {
        if ($identifier instanceof queryFactoryResult) {
            $banner = $identifier;
        } else {
            $banner_query = "SELECT banners_id, banners_title, banners_image, banners_html_text, banners_open_new_windows, banners_url
                         FROM " . TABLE_BANNERS . "
                         WHERE status = 1
                         AND banners_id = " . (int)$identifier . " " . $my_banner_filter;

            $banner = $db->Execute($banner_query);

            if ($banner->RecordCount() < 1) {
                //return '<strong>ZEN ERROR! (zen_display_banner(' . $action . ', ' . $identifier . ') -> Banner with ID \'' . $identifier . '\' not found, or status inactive</strong>';
            }
        }
    } else {
        return '<p class="alert">ZEN ERROR! (zen_display_banner(' . $action . ', ' . $identifier . ') -> Unknown $action parameter value - it must be either \'dynamic\' or \'static\'</p>';
    }

    if ($banner->RecordCount() < 1) {
        return '<strong>ZEN ERROR! (zen_display_banner(' . $action . ') failed.)</strong>';
    }

    if (!empty($banner->fields['banners_html_text'])) {
        $banner_string = $banner->fields['banners_html_text'];
    } else {
        if ($banner->fields['banners_url'] === '') {
            $banner_string = zen_image(DIR_WS_IMAGES . $banner->fields['banners_image'], $banner->fields['banners_title']);
        } else {
            $target = '';
            if ((int)$banner->fields['banners_open_new_windows'] === 1) {
                $target = ' rel="noopener" target="_blank"';
            }
            $banner_string = '<a href="' . zen_href_link(FILENAME_REDIRECT, 'action=banner&goto=' . $banner->fields['banners_id']) . '"' . $target . ' aria-label="' . $banner->fields['banners_title'] . '">' . zen_image(DIR_WS_IMAGES . $banner->fields['banners_image'], $banner->fields['banners_title']) . '</a>';
        }
    }

    zen_update_banner_display_count((int)$banner->fields['banners_id']);

    return $banner_string;
}

/**
 * Check to see whether a banner exists according to the criteria of action and banner-group-id identifier
 *
 * @since ZC v1.0.3
 */
function zen_banner_exists(string $action = '', ?string $identifier = ''): queryFactoryResult|false
{
    if (empty($identifier) || empty($action)) {
        return false;
    }
    global $db;

    $my_banner_filter = '';
    $sql = "SELECT banners_id, banners_title, banners_image, banners_html_text, banners_open_new_windows, banners_url
            FROM " . TABLE_BANNERS . " WHERE status = 1 ";

    if ($action === 'dynamic') {
        $new_banner_search = zen_build_banners_group($identifier);
        $sql .= $new_banner_search . $my_banner_filter . " ORDER BY rand()";
        $result = $db->Execute($sql);

    } elseif ($action === 'static') {
        $sql .= " AND banners_id = " . (int)$identifier . " " . $my_banner_filter;
        $result = $db->Execute($sql);

    } else {
        return false;
    }

    if ($result->RecordCount() > 0) {
        return $result;
    }

    return false;
}

/**
 * Update statistics for a given banner
 * @since ZC v1.0.3
 */
function zen_update_banner_display_count(int|string $banner_id): void
{
    global $db;
    $banner_check = $db->Execute(sprintf(SQL_BANNER_CHECK_QUERY, (int)$banner_id));

    if ($banner_check->fields['count'] > 0) {
        $db->Execute(sprintf(SQL_BANNER_CHECK_UPDATE, (int)$banner_id));

    } else {
        $sql = "INSERT INTO " . TABLE_BANNERS_HISTORY . "
                (banners_id, banners_shown, banners_history_date)
                VALUES (" . (int)$banner_id . ", 1, now())";
        $db->Execute($sql);
    }
}

/**
 * Update banner click statistics
 * @since ZC v1.0.3
 */
function zen_update_banner_click_count(int|string $banner_id): void
{
    global $db;
    $db->Execute(sprintf(SQL_BANNER_UPDATE_CLICK_COUNT, (int)$banner_id));
}

/**
 * Build banner group SQL queries
 * @param ?string $selected_banners (colon-separated list of banner groups)
 * @return string SQL where clause
 * @since ZC v1.2.0d
 */
function zen_build_banners_group(?string $selected_banners): string
{
    if (empty($selected_banners)) {
        return ' AND 1=0 ';
    }
    global $db;
    $banners_lookup = explode(':', (string)$selected_banners);
    $size = count($banners_lookup);
    $new_banner_search = '';
    if ($size === 1) {
        $new_banner_search = " banners_group = '" . $db->prepare_input($banners_lookup[0]) . "'";
    } else {
        for ($i = 0, $n = $size; $i < $n; ++$i) {
            $new_banner_search .= " banners_group = '" . $db->prepare_input($banners_lookup[$i]) . "'";
            if ($i + 1 < $n) {
                $new_banner_search .= ' OR ';
            }
        }
    }
    if ($new_banner_search !== '') {
        $new_banner_search = ' AND (' . $new_banner_search . ')';
    }
    return $new_banner_search;
}
