<?php
/**
 * Whos Online Dashboard Widget
 *
 * @package classes
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id:  $
 */

namespace ZenCart\DashboardWidget;

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

/**
 * Class WhosOnline
 * @package ZenCart\DashboardWidget
 */
class WhosOnline extends AbstractWidget
{
    public function prepareContent()
    {
        global $db;
        $user_array = array(0=>0, 1=>0, 2=>0, 3=>0);
        $guest_array = array(0=>0, 1=>0, 2=>0, 3=>0);
        $spider_array = array(0=>0, 1=>0, 2=>0, 3=>0);
        $totalRecords = 0;
        $tplVars = array('users' => $user_array, 'guests' => $guest_array, 'spiders' => $spider_array, 'total' => $totalRecords);

        $result = $db->Execute("select customer_id, full_name, ip_address, time_entry, time_last_click, last_page_url, session_id, host_address, user_agent from " . TABLE_WHOS_ONLINE);

        $totalRecords = $result->RecordCount();

        if ($totalRecords == 0) return $tplVars;

        foreach ($result as $row) {
        	$session = $row['session_id'];
        	$full_name = $row['full_name'];

        	$visitorStatus = zen_wo_get_status_for_sessionid($session, 180);

            if (empty($session)) {
                $spider_array[$visitorStatus]++;
            } else {
                if ($full_name == "&yen;Guest") {
                    $guest_array[$visitorStatus]++;
                } else {
                    $user_array[$visitorStatus]++;
                }
            }
        }
        $tplVars = array('users' => $user_array, 'guests' => $guest_array, 'spiders' => $spider_array, 'total' => $totalRecords);

        return $tplVars;
    }
}
