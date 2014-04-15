<?php
class someSanitizerClass extends base
{
  public function sanitizerTestSanitizer($value, $parameters)
  {
    $result = preg_replace($parameters ['regex'], '', $value);
    return $result;
  }
}
