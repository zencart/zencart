<?php
namespace ZenCart\AdminUser;

/**
 * Created by PhpStorm.
 * User: wilt
 * Date: 23/04/16
 * Time: 14:58
 */
class AdminUser extends \base
{

    public function __construct($session, \queryFactory $db, $notifications)
    {
        $this->dbConn = $db;
        $this->id = $session->get('admin_id');
        $this->notifications = $notifications;
        $this->userDetails = $this->getUserDetails();
    }

    private function getUserDetails()
    {
        $sql = "SELECT * FROM " . TABLE_ADMIN . " WHERE admin_id = :adminId:";
        $sql = $this->dbConn->bindVars($sql, ':adminId:', $this->id, 'integer');
        $result = $this->dbConn->execute($sql);
        $this->adminName = $result->fields['admin_name'];
        $this->adminEmail = $result->fields['admin_email'];
        $this->adminGravatar = $this->getGravatar($this->adminEmail, 90);
    }

    protected function getGravatar( $email, $s = 20, $d = 'mm', $r = 'g', $img = false, $atts = array() )
    {
        $url = 'https://www.gravatar.com/avatar/';
        $url .= md5(strtolower(trim($email)));
        $url .= "?s=$s&d=$d&r=$r";
        if ($img) {
            $url = '<img src="' . $url . '"';
            foreach ($atts as $key => $val) {
                $url .= ' ' . $key . '="' . $val . '"';
            }
            $url .= ' />';
        }
        return $url;
    }

    public function getCurrentUser()
    {
        return (array)$this;
    }

    public function getNotifications()
    {
        return $this->notifications;
    }
}
