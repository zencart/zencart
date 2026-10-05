<?php
/**
 * record_companies sidebox - displays list of record companies for customer to filter products on
 *
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: piloujp 2025 May 16 Modified in v2.2.0 $
 */
/**
 * Nothing varies this list -- no language, no customer, no request input -- so
 * one cached copy serves the whole store. The names are cached untruncated so
 * that changing MAX_DISPLAY_RECORD_COMPANY_NAME_LEN takes effect immediately.
 */
$sidebox_cache_ttl = (int)(defined('SIDEBOX_CACHE_SECONDS') ? SIDEBOX_CACHE_SECONDS : 3600);
$sidebox_cache = new \Zencart\Cache\FileCache();
$record_company = $sidebox_cache->read('recordcompaniesbox', $sidebox_cache_ttl);

if ($record_company === null) {
    $record_company = [];
    foreach ($db->Execute(
        "SELECT record_company_id, record_company_name
          FROM " . TABLE_RECORD_COMPANY . "
          ORDER BY record_company_name"
    ) as $company_row) {
        $record_company[] = [
            'record_company_id' => $company_row['record_company_id'],
            'record_company_name' => $company_row['record_company_name'],
        ];
    }

    if ($sidebox_cache_ttl > 0) {
        $sidebox_cache->write('recordcompaniesbox', $record_company);
    }
}

if ($record_company !== []) {
// Display a list
    $record_company_array = [];
    $default_selection = (isset($_GET['record_company_id'])) ? (int)$_GET['record_company_id'] : '';
    if (!isset($_GET['record_company_id']) || $_GET['record_company_id'] === '' ) {
        $required = ' required';
        $record_company_array[] = ['id' => '', 'text' => PULL_DOWN_ALL];
    } else {
        $required = '';
        $record_company_array[] = ['id' => '', 'text' => PULL_DOWN_RECORD_COMPANIES];
    }

    foreach ($record_company as $next_company) {
        $record_company_name = $next_company['record_company_name'];
        if (mb_strlen($record_company_name) > (int)MAX_DISPLAY_RECORD_COMPANY_NAME_LEN) {
            $record_company_name = mb_substr($record_company_name, 0, (int)MAX_DISPLAY_RECORD_COMPANY_NAME_LEN) . '..';
        }
        $record_company_array[] = [
            'id' => $next_company['record_company_id'],
            'text' => $record_company_name
        ];
    }
    require $template->get_template_dir('tpl_record_company_select.php', DIR_WS_TEMPLATE, $current_page_base, 'sideboxes') . '/tpl_record_company_select.php';

    $title = BOX_HEADING_RECORD_COMPANY;
    $title_link = false;
    require $template->get_template_dir($column_box_default, DIR_WS_TEMPLATE, $current_page_base, 'common') . '/' . $column_box_default;
}
