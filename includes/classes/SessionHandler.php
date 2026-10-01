<?php

declare(strict_types=1);
/**
 * Zen Cart Database Session Handler
 *
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte  Modified in v2.3.0 $
 */

namespace Zencart;

use RuntimeException;
use SessionHandlerInterface;

/**
 * @since ZC v2.0.0
 */
class SessionHandler implements SessionHandlerInterface
{
    /**
     * @inheritDoc
     * @since ZC v2.0.0
     */
    public function close(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     * @since ZC v2.0.0
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
     * @since ZC v2.0.0
     */
    public function gc(int $max_lifetime): int|false
    {
        global $db;
        $sql = "DELETE FROM " . TABLE_SESSIONS . " WHERE expiry < " . time();
        $result = $db->Execute($sql);

        if ($result->resource === false) {
            return false;
        }

        return $db->affectedRows();
    }

    /**
     * @inheritDoc
     * @since ZC v2.0.0
     */
    public function open(string $path, string $name): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     * @since ZC v2.0.0
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
     * @since ZC v2.0.0
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

    /**
     * Create a new session ID.
     * When session.use_strict_mode is turned on (which it always should be, and is default since PHP 8.6),
     * if a session id provided by the client doesn't exist on the server, create_sid() is called
     * to generate a new session id.
     *
     * Deliberately does NOT write a record for the new id: PHP validates a freshly created id
     * via validateId() to detect collisions (e.g. in session_regenerate_id()), so a pre-written
     * row would be reported as a collision and PHP would retry, leaving orphaned rows behind.
     * The record is persisted by write() at the end of the request.
     *
     * @since ZC v2.3.0
     */
    public function create_sid(): string
    {
        return session_create_id() ?: throw new RuntimeException('Unable to create a session ID.');
    }

    /**
     * Validates session ID when session.use_strict_mode is on,
     * by determining whether the session record actually does exist.
     *
     * @link https://www.php.net/manual/sessionupdatetimestamphandlerinterface.validateid
     *
     * @since ZC v2.3.0
     */
    public function validateId(string $id): bool
    {
        global $db;
        if (!is_object($db)) {
            return false;
        }

        $sql = "SELECT sesskey
                FROM " . TABLE_SESSIONS . "
                WHERE sesskey = '" . zen_db_input($id) . "'
                AND expiry > '" . time() . "'";

        $result = $db->Execute($sql);

        return !$result->EOF;
    }
}
