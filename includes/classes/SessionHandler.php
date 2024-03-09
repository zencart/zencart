<?php
/**
 * Zen Cart Database Session Handler
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Mar 07 New in v2.0.0-rc1 $
 */

namespace Zencart;

class SessionHandler implements \SessionHandlerInterface
{

    /**
     * @inheritDoc
     */
    public function close(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function destroy(string $id): bool
    {
        global $db;
        $sql = "DELETE FROM " . TABLE_SESSIONS . " WHERE sesskey = '" . zen_db_input($id) . "'";
        $db->Execute($sql);

        return true;
    }

    /**
     * @inheritDoc
     */
    public function gc(int $max_lifetime): int|false
    {
        global $db;
        $sql = "DELETE FROM " . TABLE_SESSIONS . " WHERE expiry < " . time();
        $db->Execute($sql);

        return $db->affectedRows() ?? false;
    }

    /**
     * @inheritDoc
     */
    public function open(string $path, string $name): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function read(string $id): string|false
    {
        global $db;
        $qid = "SELECT value
                FROM " . TABLE_SESSIONS . "
                WHERE sesskey = '" . zen_db_input($id) . "'
                AND expiry > '" . time() . "'";

        $value = $db->Execute($qid);

        if (!empty($value->fields['value'])) {
            $value->fields['value'] = base64_decode($value->fields['value']);
            return $value->fields['value'];
        }

        return '';
    }

    /**
     * @inheritDoc
     */
    public function write(string $id, string $data): bool
    {
        global $db;
        if (!is_object($db)) {
            return false;
        }
        $data = base64_encode($data);

        global $SESS_LIFE;
        $expiry = time() + $SESS_LIFE;

        $sql = "INSERT INTO " . TABLE_SESSIONS . " (sesskey, expiry, `value`)
                VALUES (:zkey, :zexpiry, :zvalue)
                ON DUPLICATE KEY UPDATE `value`=:zvalue, expiry=:zexpiry";

        $sql = $db->bindVars($sql, ':zkey', $id, 'string');
        $sql = $db->bindVars($sql, ':zexpiry', $expiry, 'integer');
        $sql = $db->bindVars($sql, ':zvalue', $data, 'string');
        $result = $db->Execute($sql);

        return !empty($result->resource);
    }
}
