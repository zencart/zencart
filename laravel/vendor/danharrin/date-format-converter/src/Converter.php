<?php

namespace DanHarrin\DateFormatConverter;

class Converter
{
    public $format;

    public function __construct($format)
    {
        $this->format = $format;
    }

    public function to($standard)
    {
        $format = '';

        $escape = false;
        
        foreach (str_split($this->format) as $token) {
            if ($token === '[') {
                $escape = true;
            }
            
            if ($escape) {
                if ($token === ']') {
                    $escape = false;
                }
                
                $format .= $token;
                
                continue;
            }

            $format .= array_key_exists($token, DATE_FORMAT_STANDARDS) ?
                DATE_FORMAT_STANDARDS[$token][$standard] :
                $token;
        }

        return $format;
    }
}
