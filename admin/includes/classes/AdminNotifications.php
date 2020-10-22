<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2020 Feb 13 Modified in v1.5.7 $
 */

class AdminNotifications
{
    protected $notificationServer = 'https://versionserver.zen-cart.com/api/notifications';

    protected $enabled = true;

    public function __construct()
    {
        if (defined('PROJECT_NOTIFICATIONSERVER_URL')) {
            $this->projectNotificationServer = PROJECT_NOTIFICATIONSERVER_URL;
        }

        if (defined('DISABLE_ADMIN_NOTIFICATIONS_CHECKING') && DISABLE_ADMIN_NOTIFICATIONS_CHECKING === true) {
            $this->enabled = false;
        }

        global $sniffer;  
        if (!$sniffer->table_exists(TABLE_ADMIN_NOTIFICATIONS)) { 
            $this->enabled = false;
        }
    }

    public function getNotifications($target, $adminId)
    {
        if ($this->enabled === false) {
            return [];
        }

        $notificationList = $this->getNotificationInfo();
        if (empty($notificationList)) {
            return [];
        }

        $this->pruneSavedState($notificationList);
        $savedState = $this->getSavedState($adminId);
        $result = [];
        foreach ($notificationList as $name => $notification) {
            if ($this->isNotificationAvailable($name, $target, $notification, $savedState)) {
                $result[$name] = $notification;
            }
        }
        return $result;
    }

    protected function getNotificationInfo()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->projectNotificationServer);
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 9);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 9);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Notification Messages Check');
        $response = curl_exec($ch);
        $error = curl_error($ch);
        $errno = curl_errno($ch);
        if ($errno > 0) {
            return [];
        }
        $result = json_decode($response, true);
        return $result;
    }

    protected function isNotificationAvailable($name, $target, $notification, $savedState)
    {
        if ($notification['target'] !== $target) {
            return false;
        }
        if ($this->isNotificationDismissed($name, $savedState)) {
            return false;
        }
        if (!$this->isNotificationInDate($notification, $this->getCurrentDate())) {
            return false;
        }
        if (!$this->isNotificationInCountry($notification)) {
            return false;
        }
        return true;
    }

    protected function isNotificationDismissed($name, $savedState)
    {
        if (!isset($savedState[$name])) {
            return false;
        }
        return $savedState[$name]['dismissed'];
    }

    protected function isNotificationInDate($notification, $currentDatetime)
    {
        if (!isset($notification['start-date']) && !isset($notification['end-date'])) {
            return true;
        }
        if (isset($notification['start-date']) && $currentDatetime > $notification['start-date']) {
            return false;
        }
        if (isset($notification['end-date']) && $currentDatetime < $notification['end-date']) {
            return false;
        }
        return true;
    }

    protected function isNotificationInCountry($notification)
    {
        if (!isset($notification['countries'])) {
            return true;
        }
        $iso3 = $this->getStoreCountryIso3();
        if (!in_array($iso3, $notification['countries'])) {
            return false;
        }
        return true;
    }

    protected  function getStoreCountryIso3()
    {
        global $db;

        $sql = "SELECT countries_iso_code_3 from " . TABLE_COUNTRIES . " WHERE countries_id = " . STORE_COUNTRY;
        $r = $db->execute($sql);
        $iso3 = $r->fields['countries_iso_code_3'];
        return $iso3;
    }

    protected function getSavedState($adminId)
    {
        global $db;

        $savedState = [];
        $sql = "SELECT * FROM " . TABLE_ADMIN_NOTIFICATIONS . " WHERE admin_id = :adminId:";
        $sql = $db->bindVars($sql, ':adminId:', $adminId, 'integer');
        $results = $db->execute($sql);
        foreach ($results as $result) {
            $savedState[$result['notification_key']]['dismissed'] = $result['dismissed'];
        }
        return $savedState;
    }

    protected function getCurrentDate()
    {
        return new DateTime("now");
    }

    protected function pruneSavedState($notificationList)
    {
        global $db;

        $keys = array_keys($notificationList);
        $keys = implode(',', $keys);
        $sql = "DELETE FROM " . TABLE_ADMIN_NOTIFICATIONS . " WHERE notification_key NOT IN (:keys:)";
        $sql = $db->bindVars($sql, ':keys:', $keys, 'inConstructString');
        $db->execute($sql);
    }
}
