<?php

declare(strict_types=1);

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
    public const array ALLOWED_METHODS = [
        'forget',
    ];

    /**
     * @since ZC v1.5.6
     */
    public function forget(): array
    {
        global $db;

        /**
         * Deny access unless running under the admin.
         */
        if (!defined('IS_ADMIN_FLAG') || IS_ADMIN_FLAG !== true) {
            return ([
                'data' => false
            ]);
        }

        if (!isset($_POST['key'])) {
            return ([
                'data' => false
            ]);
        }

        $sql = "INSERT INTO " . TABLE_ADMIN_NOTIFICATIONS . "(notification_key, admin_id, dismissed) VALUE (:nKey:,:adminId:, 1)
                ON DUPLICATE KEY UPDATE notification_key = :nKey:, admin_id = :adminId:, dismissed = 1";

        $sql = $db->bindVars($sql, ':adminId:', $_SESSION['admin_id'], 'integer');
        $sql = $db->bindVars($sql, ':nKey:', $_POST['key'], 'string');
        $result = $db->Execute($sql);

        return ([
            'data' => $result,
        ]);
    }
}
