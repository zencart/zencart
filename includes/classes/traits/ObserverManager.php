<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Oct 16 Modified in v2.1.0 $
 */

namespace Zencart\Traits;

use Zencart\Events\EventDto;

trait ObserverManager
{
    private static array $deprecatedNotifications = [
        'NOTIFY_GET_PRODUCT_DETAILS' => 'NOTIFY_GET_PRODUCT_OBJECT_DETAILS',
    ];

    /**
     * Attach an observer to the notifier object
     * ("Subscribe" in pub/sub terminology, or "listener" in "event listener" terminology)
     *
     * NB. We have to get a little sneaky here to stop session based classes adding events ad infinitum
     * To do this we first concatenate the class name with the event id, as a class is only ever going to attach to an
     * event id once, this provides a unique key. To ensure there are no naming problems with the array key, we md5 the
     * unique name to provide a unique hashed key.
     *
     * @param object $observer Reference to the observer class
     * @param array $eventIDArray Array of eventId's to observe
     */
    public function attach(&$observer, array $eventIDArray): void
    {
        foreach ($eventIDArray as $eventID) {

            // handle deprecations
            if (array_key_exists($eventID, self::$deprecatedNotifications)) {
                trigger_error("Use of deprecated notification '$eventID'.  Consider using '" . self::$deprecatedNotifications[$eventID] . "' instead.", E_USER_WARNING);
                continue;
            }

            // handle attach
            $nameHash = hash('md5', get_class($observer) . $eventID);
            EventDto::getInstance()->setObserver($nameHash, ['obs' => &$observer, 'eventID' => $eventID]);
        }
    }

    /**
     * Detach an observer from the notifier object
     *
     * @param object $observer
     * @param array $eventIDArray
     */
    public function detach($observer, array $eventIDArray): void
    {
        foreach ($eventIDArray as $eventID) {
            $nameHash = hash('md5', get_class($observer) . $eventID);
            EventDto::getInstance()->removeObserver($nameHash);
        }
    }

    public function registerDeprecatedEvent(string $oldEventId, string $newEventId): void
    {
        if (array_key_exists($oldEventId, self::$deprecatedNotifications)) {
            return;
        }

        self::$deprecatedNotifications[$oldEventId] = $newEventId;
    }
}
