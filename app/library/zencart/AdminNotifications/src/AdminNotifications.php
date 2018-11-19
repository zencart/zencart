<?php
/**
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: $
 */

namespace ZenCart\AdminNotifications;

/**
 * Class AdminNotifications
 * @package ZenCart\AdminNotifications
 */
class AdminNotifications extends \base
{

    public function __construct($session, $db)
    {
        $this->session = $session;
        $this->db = $db;
        $this->notificationList = array();
        $this->initNotificationsList();
    }

    public function getNotificationList()
    {
        return $this->notificationList;
    }

    private function initNotificationsList()
    {
    }

    public function addNotification($notification)
    {
        $entryHash = md5($notification['text']);
        if ($this->notificationExists($notification)) {
            return;
        }
        $link = '#';
        if (isset($notification['link'])) {
            $link = $notification['link'];
        }
        $class = 'fa fa-user text-red';
        if (isset($notification['class'])) {
            $class = $notification['class'];
        }
        $pageKey = '';
        if (isset($notification['pageKey'])) {
            $pageKey = $notification['pageKey'];
        }


        $this->notificationList[$notification['type']][$entryHash] = array(
            'text' => $notification['text'],
            'link' => $link,
            'class' => $class,
            'pageKey' => $pageKey
       );

    }

    private function notificationExists($notification)
    {
        if (!isset($this->notificationList[$notification['type']])) {
            return false;
        }
        $entryHash = md5($notification['text']);
        if ($this->notificationList[$notification['type']]['entry'] != $entryHash) {
            return false;
        }
        return true;
    }
}
