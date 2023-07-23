<?php declare(strict_types=1);

namespace Restive\Facades;

use Illuminate\Support\Facades\Facade;

class Restive extends Facade
{
    protected static function getFacadeAccessor() : string
    {
        return 'restive';
    }
}
