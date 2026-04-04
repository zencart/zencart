<?php

namespace Tests\Support\InProcess;

use RuntimeException;

class InProcessRedirectException extends RuntimeException
{
    public function __construct(
        public readonly string $url,
        public readonly int $statusCode = 302,
    ) {
        parent::__construct('In-process storefront redirect captured.');
    }
}
