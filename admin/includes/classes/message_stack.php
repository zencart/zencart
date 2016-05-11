<?php
/**
 * @package admin
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Author: zcwilt  Wed Apr 8 19:58:15 2015 +0100 Modified in v1.5.5 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

/*
  Example usage:

  $messageStack = new messageStack();
  $messageStack->add('Error: Error 1', 'error');
  $messageStack->add('Error: Error 2', 'warning');
  if ($messageStack->size > 0) echo $messageStack->output();
*/

  class messageStack extends boxTableBlock {
    var $size = 0;

    function __construct() {

      $this->errors = array();

      if (isset($_SESSION['messageToStack']) && is_array($_SESSION['messageToStack'])) {
        for ($i = 0, $n = sizeof($_SESSION['messageToStack']); $i < $n; $i++) {
          $this->add($_SESSION['messageToStack'][$i]['text'], $_SESSION['messageToStack'][$i]['type']);
        }
        $_SESSION['messageToStack'] = '';
      }
    }

    function add($message, $type = 'error') {
      if ($type == 'error') {
        $this->errors[] = array('params' => 'class="messageStackAlert alert alert-danger" role="alert"', 'text' => '<i class="fa fa-2x fa-exclamation-circle"></i> ' . $message);
      } elseif ($type == 'warning') {
        $this->errors[] = array('params' => 'class="messageStackAlert alert alert-warning" role="alert"', 'text' => '<i class="fa fa-2x fa-question-circle"></i> ' . $message);
      } elseif ($type == 'info') {
        $this->errors[] = array('params' => 'class="messageStackAlert alert alert-info" role="alert"', 'text' => '<i class="fa fa-2x fa-info-circle"></i> ' . $message);
      } elseif ($type == 'success') {
        $this->errors[] = array('params' => 'class="messageStackAlert alert alert-success" role="alert"', 'text' => '<i class="fa fa-2x fa-check-circle"></i> ' . $message);
      } elseif ($type == 'caution') {
        $this->errors[] = array('params' => 'class="messageStackAlert alert alert-warning" role="alert"', 'text' => '<i class="fa fa-2x fa-hand-stop-o"></i> ' . $message);
      } else {
        $this->errors[] = array('params' => 'class="messageStackAlert alert alert-danger" role="alert"', 'text' => $message);
      }


      $this->size++;
    }

    function add_session($message, $type = 'error') {

      if (!$_SESSION['messageToStack']) {
        $_SESSION['messageToStack'] = array();
      }

      $_SESSION['messageToStack'][] = array('text' => $message, 'type' => $type);
    }

    function reset() {
      $this->errors = array();
      $this->size = 0;
    }

    function output() {
      $this->table_data_parameters = 'class="messageBox"';
      return $this->tableBlock($this->errors);
    }
  }
