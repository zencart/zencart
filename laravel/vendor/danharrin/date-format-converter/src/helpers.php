<?php

use DanHarrin\DateFormatConverter\Converter;

if (! function_exists('convert_date_format')) {
    function convert_date_format($format)
    {
        return new Converter($format);
    }
}