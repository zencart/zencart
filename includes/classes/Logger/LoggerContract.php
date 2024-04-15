<?php

namespace Zencart\Logger;

interface LoggerContract
{
    public function pushHandlers(array $handlerOptions);
}
