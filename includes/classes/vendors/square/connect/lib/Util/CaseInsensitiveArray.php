<?php

namespace SquareConnect\Util;

final class CaseInsensitiveArray extends \ArrayObject {
  private $lower_dict = [];

  public function __construct($input = []) {
    foreach($input as $i => $v) {
      $this->lower_dict[strtolower($i)] = $v;
    }
    parent::__construct($input);
  }

  public function offsetSet($i, $v) {
    $this->lower_dict[strtolower($i)] = $v;
    parent::offsetSet($i, $v);
  }

  public function offsetUnset($i) {
    $this->lower_dict[strtolower($i)] = null;
    parent::offsetUnset($i);
  }

  public function offsetGet($i) {
    if ($this->lower_dict[strtolower($i)]) {
      return $this->lower_dict[strtolower($i)];
    } else if (parent::offsetGet($i)) {
      return parent::offsetGet($i);
    } else {
      return null;
    }
  }
}