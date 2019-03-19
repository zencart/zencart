<?php
namespace ZenCart\AdminUser;

use App\Model\ModelFactory;

class AdminUser extends \base
{

    public function __construct($session, ModelFactory $modelFactory, $notifications)
    {
        $this->model = $modelFactory->make('admin');
        $this->id = $session->get('admin_id');
        $this->notifications = $notifications;
        $this->setUserDetails();
    }

    private function setUserDetails()
    {
        $this->admins = $this->model->find($this->id);
        $this->adminName = $this->admins->admin_name;
        $this->adminEmail = $this->admins->admin_email;
        $this->adminGravatar = $this->getGravatar($this->admins->admin_email, 90);

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

    public function getModel()
    {
        return $this->admins;
    }


    public function getNotifications()
    {
        return $this->notifications;
    }
}
