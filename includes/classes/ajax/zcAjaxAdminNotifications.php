<?php
/**
 * zcAjaxAdminNotifications
 *
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2025 Sep 18 Modified in v2.2.0 $
 * @since ZC v1.5.6
 */
class zcAjaxAdminNotifications extends base
{

    /**
     * @since ZC v1.5.6
     */
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
