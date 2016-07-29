<?php
namespace ZenCart\AdminNotifications;

/**
 * Created by PhpStorm.
 * User: wilt
 * Date: 23/04/16
 * Time: 14:58
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
