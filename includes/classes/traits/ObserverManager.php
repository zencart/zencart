<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2019 Jun 04 Modified in v1.5.7 $
 */

namespace Zencart\Traits;

use Zencart\Events\EventDto;

trait ObserverManager
{
    /**
     * method used to an attach an observer to the notifier object
     *
     * NB. We have to get a little sneaky here to stop session based classes adding events ad infinitum
     * To do this we first concatenate the class name with the event id, as a class is only ever going to attach to an
     * event id once, this provides a unique key. To ensure there are no naming problems with the array key, we md5 the
     * unique name to provide a unique hashed key.
     *
     * @param object Reference to the observer class
     * @param array An array of eventId's to observe
     */
    function attach(&$observer, $eventIDArray)
    {
        foreach ($eventIDArray as $eventID) {
            $nameHash = md5(get_class($observer) . $eventID);
            EventDto::getInstance()->setObserver($nameHash, array('obs' => &$observer, 'eventID' => $eventID));
        }
    }

    /**
     * method used to detach an observer from the notifier object
     * @param object
     * @param array
     */
    function detach($observer, $eventIDArray)
    {
        foreach ($eventIDArray as $eventID) {
            $nameHash = md5(get_class($observer) . $eventID);
            EventDto::getInstance()->removeObserver($nameHash);
        }
    }

}
