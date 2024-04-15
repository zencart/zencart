<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license https://opensource.org/license/mit MIT License
 * @version 
 */

namespace Zencart\Logger;

interface LoggerContract
{
    public function pushHandlers(array $handlerOptions);
}
