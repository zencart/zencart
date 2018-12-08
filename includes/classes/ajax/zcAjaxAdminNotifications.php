<?php
/**
 * zcAjaxAdminNotifications
 *
 * @package templateSystem
 * @copyright Copyright 2003-2018 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Zcwilt Thu Nov 1 17:28:42 2018 +0000 New in v1.5.6 $
 */
class zcAjaxAdminNotifications extends base
{

    public function forget()
    {
        global $db;

        if (!isset($_POST['key'])) {
            return (array(
                'data' => false
            ));
        }

        $sql = "INSERT INTO " . TABLE_ADMIN_NOTIFICATIONS . "(notification_key, admin_id, dismissed) VALUE (:nKey:,:adminId:, 1) 
               ON DUPLICATE KEY UPDATE notification_key = :nKey:, admin_id = :adminId:, dismissed = 1";

        $sql = $db->bindVars($sql, ':adminId:', $_POST['admin_id'], 'integer');
        $sql = $db->bindVars($sql, ':nKey:', $_POST['key'], 'string');
        $result = $db->execute($sql);

        return (array(
            'data' => $result
        ));
    }

}
