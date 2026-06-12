<?php

declare(strict_types=1);
/**
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license https://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2025 Sep 18 Modified in v2.2.0 $
 * @since ZC v1.5.6
 */

class AdminNotifications
{
    protected bool $enabled = true;
    private string $projectNotificationServer = 'https://ping.zen-cart.com/api/notifications';

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

    /**
     * @since ZC v1.5.6
     */
    public function getNotifications(string $target, int $adminId): array
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

    /**
     * @since ZC v1.5.6
     */
    protected function getNotificationInfo()
    {
        if (empty($this->projectNotificationServer)) {
            return [];
        }
        $ch = curl_init();
        curl_setopt($ch, \CURLOPT_URL, $this->projectNotificationServer);
        curl_setopt($ch, \CURLOPT_VERBOSE, 0);
        curl_setopt($ch, \CURLOPT_HEADER, false);
        curl_setopt($ch, \CURLOPT_TIMEOUT, 9);
        curl_setopt($ch, \CURLOPT_CONNECTTIMEOUT, 9);
        curl_setopt($ch, \CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, \CURLOPT_USERAGENT, 'Notification Messages Check');
        $response = curl_exec($ch);
        $error = curl_error($ch);
        $errno = curl_errno($ch);
        if ($errno > 0) {
            return [];
        }
        $result = json_decode($response, true);
        return $result;
    }

    /**
     * @since ZC v1.5.6
     */
    protected function isNotificationAvailable(string $name, string $target, array $notification, array $savedState): bool
    {
        if ($notification['target'] !== $target) {
            return false;
        }
        if ($this->isNotificationDismissed($name, $savedState)) {
            return false;
        }
        if (!$this->isNotificationInCountry($notification)) {
            return false;
        }
        if (!$this->isNotificationInDate($notification, $this->getCurrentDate())) {
            return false;
        }
        return true;
    }

    /**
     * @since ZC v1.5.6
     */
    protected function isNotificationDismissed(string $name, array $savedState): bool
    {
        if (!isset($savedState[$name])) {
            return false;
        }
        return $savedState[$name]['dismissed'];
    }

    /**
     * @since ZC v1.5.6
     */
    protected function isNotificationInDate(array $notification, DateTimeInterface $currentDatetime): bool
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

    /**
     * @since ZC v1.5.6
     */
    protected function isNotificationInCountry(array $notification): bool
    {
        if (!isset($notification['countries'])) {
            return true;
        }
        $iso3 = $this->getStoreCountryIso3();
        if (!in_array($iso3, $notification['countries'], true)) {
            return false;
        }
        return true;
    }

    /**
     * @since ZC v1.5.6
     */
    protected function getStoreCountryIso3(): string
    {
        global $db;

        $sql = "SELECT countries_iso_code_3 from " . TABLE_COUNTRIES . " WHERE countries_id = " . (int)zen_config('STORE_COUNTRY');
        $result = $db->Execute($sql);
        return $result->fields['countries_iso_code_3'] ?? '';
    }

    /**
     * @since ZC v1.5.6
     */
    protected function getSavedState(int $adminId): array
    {
        global $db;

        $savedState = [];
        $sql = "SELECT * FROM " . TABLE_ADMIN_NOTIFICATIONS . " WHERE admin_id = :adminId:";
        $sql = $db->bindVars($sql, ':adminId:', $adminId, 'integer');
        $results = $db->Execute($sql);
        foreach ($results as $result) {
            $savedState[$result['notification_key']]['dismissed'] = $result['dismissed'];
        }
        return $savedState;
    }

    /**
     * @since ZC v1.5.6
     */
    protected function getCurrentDate(): DateTime
    {
        return new DateTime('now');
    }

    /**
     * @since ZC v1.5.6
     */
    protected function pruneSavedState($notificationList): void
    {
        global $db;

        $keys = array_keys($notificationList);
        $keys = implode(',', $keys);
        $sql = "DELETE FROM " . TABLE_ADMIN_NOTIFICATIONS . " WHERE notification_key NOT IN (:keys:)";
        $sql = $db->bindVars($sql, ':keys:', $keys, 'inConstructString');
        $db->Execute($sql);
    }
}
