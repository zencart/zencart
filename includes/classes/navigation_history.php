<?php
/**
 * Navigation_history Class.
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2019 Sep 16 Modified in v1.5.7 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
/**
 * Navigation_history Class.
 * This class is used to manage navigation snapshots
 *
 */
class navigationHistory extends base {
  var $path, $snapshot;

  function __construct() {
    $this->reset();
  }

  function reset() {
    $this->path = array();
    $this->snapshot = array();
  }

  function add_current_page() {
      // check whether there are pages which should be blacklisted against entering navigation history
    if (preg_match('|ajax\.php$|', $_SERVER['SCRIPT_NAME']) && $_GET['act'] != '') return;

    global $request_type, $cPath;
    $get_vars = array();

    if (is_array($_GET)) {
      foreach ($_GET as $key => $value) {
        if ($key != 'main_page') {
          $get_vars[$key] = $value;
        }
      }
    }

    $set = 'true';
    for ($i=0, $n=sizeof($this->path); $i<$n; $i++) {
      if ( ($this->path[$i]['page'] == $_GET['main_page']) ) {
        if (isset($cPath)) {
          if (!isset($this->path[$i]['get']['cPath'])) {
            continue;
          } else {
            if ($this->path[$i]['get']['cPath'] == $cPath) {
              array_splice($this->path, ($i+1));
              $set = 'false';
              break;
            } else {
              $old_cPath = explode('_', $this->path[$i]['get']['cPath']);
              $new_cPath = explode('_', $cPath);

              $exit_loop = false;
              for ($j=0, $n2=sizeof($old_cPath); $j<$n2; $j++) {
                if ($old_cPath[$j] != $new_cPath[$j]) {
                  array_splice($this->path, ($i));
                  $set = 'true';
                  $exit_loop = true;
                  break;
                }
              }
              if ($exit_loop == true) break;
            }
          }
        } else {
          array_splice($this->path, ($i));
          $set = 'true';
          break;
        }
      }
    }

    if ($set == 'true') {
      if ($_GET['main_page']) {
        $page = $_GET['main_page'];
      } else {
        $page = 'index';
      }
      $this->path[] = array('page' => $page,
                            'mode' => $request_type,
                            'get' => $get_vars,
                            'post' => array() /*$_POST*/);
    }
  }

  function remove_current_page() {

    $last_entry_position = sizeof($this->path) - 1;
    if (isset($this->path[$last_entry_position]['page']) && $this->path[$last_entry_position]['page'] == $_GET['main_page']) {
      unset($this->path[$last_entry_position]);
    }
  }

  function set_snapshot($page = '') {
    global $request_type;
    $get_vars = array();
    if (is_array($page)) {
      $this->snapshot = array('page' => $page['page'],
                              'mode' => $page['mode'],
                              'get' => $page['get'],
                              'post' => $page['post']);
    } else {
      foreach ($_GET as $key => $value) {
        if ($key != 'main_page') {
          $get_vars[$key] = $value;
        }
      }
      if ($_GET['main_page']) {
        $page = $_GET['main_page'];
      } else {
        $page = 'index';
      }
      $this->snapshot = array('page' => $page,
                              'mode' => $request_type,
                              'get' => $get_vars,
                              'post' => array()/*$_POST*/);
    }
  }

  function clear_snapshot() {
    $this->snapshot = array();
  }

  function set_path_as_snapshot($history = 0) {
    $pos = (sizeof($this->path)-1-$history);
    $this->snapshot = array('page' => $this->path[$pos]['page'],
                            'mode' => $this->path[$pos]['mode'],
                            'get' => $this->path[$pos]['get'],
                            'post' => $this->path[$pos]['post']);
  }

  function debug() {
    for ($i=0, $n=sizeof($this->path); $i<$n; $i++) {
      echo $this->path[$i]['page'] . '?';
      foreach($this->path[$i]['get'] as $key => $value) {
        echo $key . '=' . $value . '&';
      }
      if (sizeof($this->path[$i]['post']) > 0) {
        echo '<br />';
        foreach($this->path[$i]['post'] as $key => $value) {
          echo '&nbsp;&nbsp;<strong>' . $key . '=' . $value . '</strong><br />';
        }
      }
      echo '<br />';
    }

    if (sizeof($this->snapshot) > 0) {
      echo '<br /><br />';

      echo $this->snapshot['mode'] . ' ' . $this->snapshot['page'] . '?' . zen_array_to_string($this->snapshot['get'], array(zen_session_name())) . '<br />';
    }
  }

  function unserialize($broken) {
    foreach($broken as $kv) {
      $key=$kv['key'];
      if (gettype($this->$key)!="user function")
      $this->$key=$kv['value'];
    }
  }
}
