<?php
/**
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2025 Sep 18 Modified in v2.2.0 $
 */

namespace Zencart\Events;

use Zencart\Traits\Singleton;

/**
 * @since ZC v1.5.8
 */
class EventDto
{
    use Singleton;

    private $observers = [];

    /**
     * @since ZC v1.5.8
     */
    public function getObservers()
    {
        return $this->observers;
    }

    /**
     * @since ZC v1.5.8
     */
    public function setObserver($eventHash, $eventParameters)
    {
        $this->observers[$eventHash] = $eventParameters;
    }

    /**
     * @since ZC v1.5.8
     */
    public function removeObserver($eventHash)
    {
        if (isset($this->observers[$eventHash])) {
            unset($this->observers[$eventHash]);
        }
    }
}
