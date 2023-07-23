<?php

use Spatie\Invade\Invader;

if (! function_exists('invade')) {
    /**
     * @template T of object
     *
     * @param T $object
     * @return Invader<T>
     */
    function invade(object $object): Invader
    {
        return new Invader($object);
    }
}
