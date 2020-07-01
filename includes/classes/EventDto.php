<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */

namespace Zencart\Events;

use Zencart\Traits\Singleton;

class EventDto
{
    use Singleton;

    private $observers = [];

    public function getObservers()
    {
        return $this->observers;
    }

    public function setObserver($eventHash, $eventParameters)
    {
        $this->observers[$eventHash] = $eventParameters;
    }

    public function removeObserver($eventHash)
    {
        if (isset($this->observers[$eventHash])) {
            unset($this->observers[$eventHash]);
        }
    }
}
