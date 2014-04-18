<?php
class someSanitizerClass extends base
{
  public static function sanitizerTestSanitizer($value, $parameters)
  {
    $result = preg_replace($parameters ['regex'], '', $value);
    return $result;
  }
}
