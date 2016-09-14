<?php
namespace ZenCart\AdminUser;

use ZenCart\Model\ModelFactory;

class AdminUser extends \base
{

    public function __construct($session, ModelFactory $modelFactory, $notifications)
    {
        $this->model = $modelFactory->factory('admin');
        $this->id = $session->get('admin_id');
        $this->notifications = $notifications;
        $this->setUserDetails();
    }

    private function setUserDetails()
    {
        $admins = $this->model->find($this->id);
        $this->adminName = $admins->admin_name;
        $this->adminEmail = $admins->admin_email;
        $this->adminGravatar = $this->getGravatar($admins->admin_email, 90);

//        $sql = "SELECT * FROM " . TABLE_ADMIN . " WHERE admin_id = :adminId:";
//        $sql = $this->dbConn->bindVars($sql, ':adminId:', $this->id, 'integer');
//        $result = $this->dbConn->execute($sql);
//        $this->adminName = $result->fields['admin_name'];
//        $this->adminEmail = $result->fields['admin_email'];
//        $this->adminGravatar = $this->getGravatar($this->adminEmail, 90);
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
