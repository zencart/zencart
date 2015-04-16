<?php
/**
 * banner functions
 *
 * @package functions
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: banner_manager.php Author: ajeh  Modified in v1.5.5 $
 */

////
// Set the status of a banner
  function zen_set_banner_status($banners_id, $status) {
    global $db;
    if ($status == '1') {
      $sql = "update " . TABLE_BANNERS . "
              set status = 1, date_status_change = now(), date_scheduled = NULL
              where banners_id = '" . (int)$banners_id . "'";

      return $db->Execute($sql);

    } elseif ($status == '0') {
      $sql = "update " . TABLE_BANNERS . "
              set status = 0, date_status_change = now()
              where banners_id = '" . (int)$banners_id . "'";

      return $db->Execute($sql);

    } else {
      return -1;
    }
  }

////
// Auto activate banners
  function zen_activate_banners() {
    global $db;
    $banners_query = "select banners_id, date_scheduled
                      from " . TABLE_BANNERS . "
                      where date_scheduled IS NOT NULL";
    $banners = $db->Execute($banners_query);

    if ($banners->RecordCount() > 0) {
      while (!$banners->EOF) {
        if (date('Y-m-d H:i:s') >= $banners->fields['date_scheduled']) {
          zen_set_banner_status($banners->fields['banners_id'], '1');
        }
        $banners->MoveNext();
      }
    }
  }

////
// Auto expire banners
  function zen_expire_banners() {
    global $db;
    $banners_query = "select b.banners_id, b.expires_date, b.expires_impressions,
                             sum(bh.banners_shown) as banners_shown
                      from " . TABLE_BANNERS . " b, " . TABLE_BANNERS_HISTORY . " bh
                      where b.status = 1
                      and b.banners_id = bh.banners_id
                      group by b.banners_id, b.expires_date, b.expires_impressions";

    $banners = $db->Execute($banners_query);

    if ($banners->RecordCount() > 0) {
      while (!$banners->EOF) {
        if (zen_not_null($banners->fields['expires_date'])) {
          if (date('Y-m-d H:i:s') >= $banners->fields['expires_date']) {
            zen_set_banner_status($banners->fields['banners_id'], '0');
          }
        } elseif (zen_not_null($banners->fields['expires_impressions'])) {
          if ( ($banners->fields['expires_impressions'] > 0) && ($banners->fields['banners_shown'] >= $banners->fields['expires_impressions']) ) {
            zen_set_banner_status($banners->fields['banners_id'], '0');
          }
        }
        $banners->MoveNext();
      }
    }
  }

////
// Display a banner from the specified group or banner id ($identifier)
  function zen_display_banner($action, $identifier) {
    global $db, $request_type;

    switch ($request_type) {
      case ('SSL'):
        $my_banner_filter=" and banners_on_ssl= " . "1 ";
        break;
      case ('NONSSL'):
        $my_banner_filter='';
        break;
    }

    if ($action == 'dynamic') {
      $new_banner_search = zen_build_banners_group($identifier);

      $banners_query = "select count(*) as count
                        from " . TABLE_BANNERS . "
                           where status = '1' " .
                           $new_banner_search . $my_banner_filter;

      $banners = $db->Execute($banners_query);

      if ($banners->fields['count'] > 0) {
        $banner = $db->Execute("select banners_id, banners_title, banners_image, banners_html_text, banners_open_new_windows, banners_url
                               from " . TABLE_BANNERS . "
                               where status = 1 " .
                               $new_banner_search . $my_banner_filter . " order by rand()");

      } else {
        return '<p class="alert">ZEN ERROR! (zen_display_banner(' . $action . ', ' . $identifier . ') -> No banners with group \'' . $identifier . '\' found!</p>';
      }
    } elseif ($action == 'static') {
      if (is_object($identifier)) {
        $banner = $identifier;
      } else {
        $banner_query = "select banners_id, banners_title, banners_image, banners_html_text, banners_open_new_windows, banners_url
                         from " . TABLE_BANNERS . "
                         where status = 1
                         and banners_id = '" . (int)$identifier . "'" . $my_banner_filter;

        $banner = $db->Execute($banner_query);

        if ($banner->RecordCount() < 1) {
          //return '<strong>ZEN ERROR! (zen_display_banner(' . $action . ', ' . $identifier . ') -> Banner with ID \'' . $identifier . '\' not found, or status inactive</strong>';
        }
      }
    } else {
      return '<p class="alert">ZEN ERROR! (zen_display_banner(' . $action . ', ' . $identifier . ') -> Unknown $action parameter value - it must be either \'dynamic\' or \'static\'</p>';
    }

    if (zen_not_null($banner->fields['banners_html_text'])) {
      $banner_string = $banner->fields['banners_html_text'];
    } else {
      if ($banner->fields['banners_url'] == '') {
        $banner_string = zen_image(DIR_WS_IMAGES . $banner->fields['banners_image'], $banner->fields['banners_title']);
      } else {
        if ($banner->fields['banners_open_new_windows'] == '1') {
          $banner_string = '<a href="' . zen_href_link(FILENAME_REDIRECT, 'action=banner&goto=' . $banner->fields['banners_id']) . '" target="_blank">' . zen_image(DIR_WS_IMAGES . $banner->fields['banners_image'], $banner->fields['banners_title']) . '</a>';
        } else {
          $banner_string = '<a href="' . zen_href_link(FILENAME_REDIRECT, 'action=banner&goto=' . $banner->fields['banners_id']) . '">' . zen_image(DIR_WS_IMAGES . $banner->fields['banners_image'], $banner->fields['banners_title']) . '</a>';
        }
      }
    }

    zen_update_banner_display_count($banner->fields['banners_id']);

    return $banner_string;
  }

////
// Check to see if a banner exists
  function zen_banner_exists($action, $identifier) {
    global $db, $request_type;

    switch ($request_type) {
      case ('SSL'):
        $my_banner_filter=" and banners_on_ssl= " . "1 ";
        break;
      case ('NONSSL'):
        $my_banner_filter='';
        break;
    }

    if ($action == 'dynamic') {
      $new_banner_search = zen_build_banners_group($identifier);
      return $db->Execute("select banners_id, banners_title, banners_image, banners_html_text, banners_open_new_windows, banners_url
                           from " . TABLE_BANNERS . "
                               where status = 1 " .
                               $new_banner_search . $my_banner_filter . " order by rand()");
    } elseif ($action == 'static') {
      $banner_query = "select banners_id, banners_title, banners_image, banners_html_text, banners_open_new_windows, banners_url
                       from " . TABLE_BANNERS . "
                       where status = 1
                       and banners_id = '" . (int)$identifier . "'" . $my_banner_filter;

      return $banner = $db->Execute($banner_query);
    } else {
      return false;
    }
  }

////
// Update the banner display statistics
  function zen_update_banner_display_count($banner_id) {
    global $db;
    $banner_check = $db->Execute(sprintf(SQL_BANNER_CHECK_QUERY, (int)$banner_id));

    if ($banner_check->fields['count'] > 0) {

      $db->Execute(sprintf(SQL_BANNER_CHECK_UPDATE, (int)$banner_id));

    } else {
      $sql = "insert into " . TABLE_BANNERS_HISTORY . "
                     (banners_id, banners_shown, banners_history_date)
              values ('" . (int)$banner_id . "', 1, now())";

      $db->Execute($sql);
    }
  }

////
// Update the banner click statistics
  function zen_update_banner_click_count($banner_id) {
    global $db;
    $db->Execute(sprintf(SQL_BANNER_UPDATE_CLICK_COUNT, (int)$banner_id));
  }

////
// build banner groups
  function zen_build_banners_group($selected_banners) {
    $selected_banners = explode(':', $selected_banners);
    $size = sizeof($selected_banners);
    if ($size == 1) {
      $new_banner_search = " banners_group = '" . $selected_banners[0] . "'";
    } else {
      for ($i=0, $n=$size; $i<$n; $i+=1) {
        $new_banner_search .= " banners_group = '" . $selected_banners[$i] . "'";
        if ($i+1 < $n) {
          $new_banner_search .= ' or ';
        }
      }
    }
    if ($new_banner_search != '') {
      $new_banner_search = ' and (' . $new_banner_search . ')';
    }
    return $new_banner_search;
  }
?>