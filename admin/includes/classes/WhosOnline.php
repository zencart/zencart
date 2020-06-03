<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  New in v1.5.7 $
 */

class WhosOnline extends base
{
    /* @var int seconds when considered inactive - 180 default = 3 minutes */
    protected $timer_inactive_threshold = 180;

    /* @var int seconds til dead, ie: considered dead after this long since last click; default 540 seconds = 9 minutes */
    protected $timer_dead_threshold = 540;

    /* @var int purge after how many seconds? default= 1200 = 20 minutes */
    protected $timer_remove_threshold = 1200;

    protected $total_sessions = 0;
    protected $duplicates = 0;
    protected $unique_sessions = 0;
    protected $whos_online = [];

    protected $user_array = [0 => 0, 1 => 0, 2 => 0, 3 => 0];
    protected $guest_array = [0 => 0, 1 => 0, 2 => 0, 3 => 0];
    protected $spider_array = [0 => 0, 1 => 0, 2 => 0, 3 => 0];

    protected $statsCacheLastCalculatedAt = 0;


    public function __construct($forceRebuild = false, $skip_gc = false)
    {
        if (defined('WHOIS_TIMER_REMOVE')) $this->timer_remove_threshold = WHOIS_TIMER_REMOVE;
        if (defined('WHOIS_TIMER_INACTIVE')) $this->timer_inactive_threshold = WHOIS_TIMER_INACTIVE;
        if (defined('WHOIS_TIMER_DEAD')) $this->timer_dead_threshold = WHOIS_TIMER_DEAD;

        // normally we get rid of expired data
        if (!$skip_gc) {
            $this->doGarbageCollection();
        }

        // normally we don't retrieve all data on instantiation; passing true skips the statsCache check
        if ($forceRebuild) {
            $this->retrieve();
        }
    }

    public function getTimerInactive()
    {
        return (int)$this->timer_inactive_threshold;
    }

    public function getTimerDead()
    {
        return (int)$this->timer_dead_threshold;
    }

    public function getUniques()
    {
        return (int)$this->unique_sessions;
    }

    public function getDuplicates()
    {
        return (int)$this->duplicates;
    }

    public function getTotalSessions()
    {
        return (int)$this->total_sessions;
    }

    public function retrieve($selectedView = '', $sessionToInspect = '', $exclude_spiders = false, $exclude_admins = true)
    {
        switch ($selectedView) {
            case "full_name-desc":
                $order = "full_name DESC, LPAD(ip_address,11,'0')";
                break;
            case "full_name":
                $order = "full_name, LPAD(ip_address,11,'0')";
                break;
            case "ip_address":
                $order = "ip_address, session_id";
                break;
            case "ip_address-desc":
                $order = "ip_address DESC, session_id";
                break;
            case "time_last_click-desc":
                $order = "time_last_click DESC, LPAD(ip_address,11,'0')";
                break;
            case "time_last_click":
                $order = "time_last_click, LPAD(ip_address,11,'0')";
                break;
            case "time_entry-desc":
                $order = "time_entry DESC, LPAD(ip_address,11,'0')";
                break;
            case "time_entry":
                $order = "time_entry, LPAD(ip_address,11,'0')";
                break;
            case "last_page_url-desc":
                $order = "last_page_url DESC, LPAD(ip_address,11,'0')";
                break;
            case "last_page_url":
                $order = "last_page_url, LPAD(ip_address,11,'0')";
                break;
            case "session_id":
                $order = "session_id, ip_address";
                break;
            case "session_id-desc":
                $order = "session_id DESC, ip_address";
                break;
            default:
                $order = "time_entry, LPAD(ip_address,11,'0')";
        }
        $where = '';
        if ($exclude_spiders) {
            $where = "WHERE session_id != '' ";
        }
        if ($exclude_admins) {
            $where .= ($where == '') ? " WHERE " : " AND ";
            $where .= "ip_address != '' AND ip_address NOT IN ('" . implode("','", preg_split('/[\s,]/', zen_db_input(EXCLUDE_ADMIN_IP_FOR_MAINTENANCE) . ',' . zen_db_input($_SERVER['REMOTE_ADDR']))) . "') ";
        }
        $sql = "SELECT customer_id, full_name, ip_address, time_entry, time_last_click, last_page_url, session_id, host_address, user_agent, s.value as session_data
                FROM " . TABLE_WHOS_ONLINE . " w
                LEFT OUTER JOIN " . TABLE_SESSIONS . " s ON (s.sesskey = w.session_id) 
                :where:
                ORDER BY :orderby:";

        global $db;
        $sql = $db->bindVars($sql, ':where:', $where, 'passthru');
        $sql = $db->bindVars($sql, ':orderby:', $order, 'passthru');
        $results = $db->Execute($sql);

        $ip_array = [];

        // make the first entry show cart by default
        if (empty($sessionToInspect) && !$results->EOF) {
            $sessionToInspect = $results->fields['session_id']; // uses the fields array because we're not iterating the array yet
        }

        foreach ($results as $result) {
            $result['time_online'] = (time() - $result['time_entry']);
            $result['is_a_bot'] = empty($result['session_id']);
            $result['time_since_last_click'] = $this->getHumanFriendlyTimeSince($result['time_last_click']);

            $result['status_code'] = $this->getStatusCode($result); // depends on having the 'time_last_click' and 'session_data' from the query
            $result['icon_image'] = $this->getImageForStatus($result['status_code']);
            $result['icon_class'] = $this->getIconClassForStatus($result['status_code']);

            // if a session_id has been passed, we inspect it now to save re-querying and re-processing
            $result['cart'] = null;
            if (!empty($sessionToInspect) && $sessionToInspect === $result['session_id']) {
                $result['cart'] = $this->inspectSessionCart($result['session_id'], $result['session_data']);
            }
            unset($result['session_data']);

            $this->whos_online[$result['session_id']] = $result;

            // track duplicate IPs
            if (in_array($result['ip_address'], $ip_array)) {
                $this->duplicates++;
            } else {
                $ip_array[] = $result['ip_address'];
            }
        }

        if (empty($this->duplicates)) {
            $this->duplicates = 0;
        }

        $this->total_sessions = $results->RecordCount();
        $this->unique_sessions = $this->total_sessions - $this->duplicates;

        $this->calculateStats();

        return $this->whos_online;
    }

    public function getImageForStatus($status_code = 0)
    {
        switch ($status_code) {
            case 3:
                return zen_image(DIR_WS_IMAGES . 'icon_status_red_light.gif');
            case 2:
                return zen_image(DIR_WS_IMAGES . 'icon_status_red.gif');
            case 1:
                return zen_image(DIR_WS_IMAGES . 'icon_status_yellow.gif');
            default:
            case 0:
                return zen_image(DIR_WS_IMAGES . 'icon_status_green.gif');
        }
    }

    // future-use CSS classes for icons
    public function getIconClassForStatus($status_code = 0)
    {
        switch ($status_code) {
            case 3:
                return 'wo-inactive-empty';
            case 2:
                return 'wo-active-empty';
            case 1:
                return 'wo-inactive-not-empty';
            default:
            case 0:
                return 'wo-active-not-empty';
        }
    }

    public function getStats()
    {
        if ($this->statsCacheLastCalculatedAt < (time() - 15)) {
            $this->retrieve();
        }

        return ['user_array' => $this->user_array, 'guest_array' => $this->guest_array, 'spider_array' => $this->spider_array];
    }

    protected function getHumanFriendlyTimeSince($timestamp_of_last_click)
    {
        $diff_in_seconds = (time() - $timestamp_of_last_click);
        return gmdate('H:i:s', $diff_in_seconds);
    }

    protected function getStatusCode($data)
    {
        $xx_mins_ago_long = (time() - (int)$this->timer_inactive_threshold);

        // empty session data means definitely no cart (or not parseable, so we treat as empty)
        if (empty($data['session_data'])) {
            if ($data['time_last_click'] < $xx_mins_ago_long) {
                return 3;
            }
            return 2;
        }

        $chk_cart_status = base64_decode($data['session_data']);
        // lookup how many rows are in the shopping cart contents array
        if (preg_match('/shoppingCart":\d*:{s:\d*:"(contents)";a:(\d*):/', $chk_cart_status, $matches)) {
            $rows_in_cart = $matches[2];
        }

        if (empty($rows_in_cart)) {
            if ($data['time_last_click'] < $xx_mins_ago_long) {
                return 3; // empty inactive
            }
            return 2; // empty active
        }
        if ($rows_in_cart > 0) {
            if ($data['time_last_click'] < $xx_mins_ago_long) {
                return 1; // not-empty, inactive
            }
            return 0; // not-empty, active
        }
    }

    protected function calculateStats()
    {
        foreach ($this->whos_online as $session) {
            if (empty($session['session_id'])) {
                $this->spider_array[$session['status_code']]++;
            } else {
                if ($session['full_name'] === "&yen;Guest") {
                    $this->guest_array[$session['status_code']]++;
                } else {
                    $this->user_array[$session['status_code']]++;
                }
            }
        }
    }

    /**
     * Remove expired entries
     */
    public function doGarbageCollection()
    {
        global $db;
        $xx_mins_ago_dead = (time() - (int)$this->timer_dead_threshold);
        $xx_mins_ago = (time() - (int)$this->timer_remove_threshold);

        $db->Execute("DELETE FROM " . TABLE_WHOS_ONLINE . "
              WHERE time_last_click < '" . $xx_mins_ago . "'
              OR (time_entry=time_last_click
                AND time_last_click < '" . $xx_mins_ago_dead . "')");

    }


    /**
     * @param string $session_id
     * @param string $session_data
     * @return array|null
     */
    protected function inspectSessionCart($session_id = '', $session_data = '')
    {
        // we need at least one of these parameters
        if (empty($session_id) && empty($session_data)) return null;

        // or we can pass in the already-queried session data
        if (empty($session_data)) {
            $result = $GLOBALS['db']->Execute("
                SELECT value as session_data
                FROM " . TABLE_SESSIONS . "
                WHERE sesskey = '" . zen_db_input($session_id) . "'");
            $session_data = $result->EOF === false ? trim($result->fields['session_data']) : '';
        }

        if (strpos($session_data, 'cart|O') == 0) {
            $session_data = base64_decode($session_data);
        }
        if (strpos($session_data, 'cart|O') == 0) {
            $session_data = '';
        }

        if (empty($session_data)) {
            return null;
        }

        $extracted_data = [];
        $fields_to_extract = [
            'language' => 'language_name',
            'languages_id' => 'language_id',
            'languages_code' => 'language_code',
            'customers_ip_address' => 'customer_ip',
            'customers_host_address' => 'customer_hostname',
            'customers_email_address' => 'customers_email_address',
            'customer_default_address_id' => 'address_default_id',
            'billto' => 'address_billing_id',
            'sendto' => 'address_delivery_id',
            'customer_country_id' => 'customer_country_id',
            'customer_zone_id' => 'customer_zone_id',
            'shipping_weight' => 'shipping_weight',
            'shipping' => 'shipping',
            'payment' => 'payment',
            'cot_gv' => 'cot_gv',
            'cart_errors' => 'cart_errors',
            'comments' => 'checkout_comments',
        ];

        $adminSession = session_encode();
        $backupSessionArray = $_SESSION;

        if (session_decode($session_data) !== false) {
            $cart = $_SESSION['cart'];
            $currency = $_SESSION['currency'];

            if (is_object($cart) && isset($currency)) {
                $extracted_data['products'] = $cart->get_products();
                $extracted_data['total'] = $GLOBALS['currencies']->format($cart->show_total(), true, $currency);
                $extracted_data['cartObject'] = $cart;
                $extracted_data['currency_code'] = $currency;
                $extracted_data['cartID'] = $_SESSION['cartID'];
            }

            foreach($fields_to_extract as $field => $as) {
                if (isset($_SESSION[$field])) {
                    $extracted_data[$as] = $_SESSION[$field];
                }
            }
        }

        // protect against tampering
        $_SESSION = $backupSessionArray;
        foreach($_SESSION as $key => $value) {
            if (!isset($backupSessionArray[$key])) {
                unset($_SESSION[$key]);
            }
        }
        session_decode($adminSession);
        unset($adminSession, $backupSessionArray);

        return $extracted_data;
    }
}
