<?php
class zcURLTestObserver extends base
{
  static $CHANGE_NOTHING = 0;
  static $CHANGE_CONNECTION = 1;
  static $CHANGE_PAGE = 2;
  static $CHANGE_PARAMETERS = 4;
  static $CHANGE_STATIC = 8;

  public $mode;

  function __construct()
  {
    $this->attach($this, array('NOTIFY_HANDLE_HREF_LINK'));
    $this->mode = 0;
  }

  function update(&$class, $eventID, $paramsArray = array(), &$page, &$parameters, &$connection, &$static)
  {
    if($this->mode & zcURLTestObserver::$CHANGE_CONNECTION)
    {
      if($connection == 'SSL')
      {
        $connection = 'NONSSL';
      } else {
        $connection = 'SSL';
      }
    }

    if($this->mode & zcURLTestObserver::$CHANGE_PAGE)
    {
      $page = 'dummy_page';
    }

    if($this->mode & zcURLTestObserver::$CHANGE_PARAMETERS)
    {
      $parameters = array('changed' => 'parameters');
    }

    if($this->mode & zcURLTestObserver::$CHANGE_STATIC)
    {
      $static = !$static;
    }
  }
}
