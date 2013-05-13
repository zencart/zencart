<?php
/**
 * messageStack Class.
 *
 * @package classes
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: message_stack.php 14169 2009-08-15 23:58:05Z drbyte $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
/**
 * messageStack Class.
 * This class is used to manage messageStack alerts
 *
 * @package classes
 */
class messageStack extends base {

  // class constructor
  function messageStack() {

    $this->messages = array();

    if (isset($_SESSION['messageToStack']) && $_SESSION['messageToStack']) {
      $messageToStack = $_SESSION['messageToStack'];
      for ($i=0, $n=sizeof($messageToStack); $i<$n; $i++) {
        $this->add($messageToStack[$i]['class'], $messageToStack[$i]['text'], $messageToStack[$i]['type']);
      }
      $_SESSION['messageToStack']= '';
    }
  }

  function add($class, $message, $type = 'error') {
    global $template, $current_page_base;
    $message = trim($message);
    $duplicate = false;
    if (strlen($message) > 0) {
      if ($type == 'error') {
        $theAlert = array('params' => 'class="messageStackError larger"', 'class' => $class, 'icon' => zen_image($template->get_template_dir(ICON_IMAGE_ERROR, DIR_WS_TEMPLATE, $current_page_base,'images/icons'). '/' . ICON_IMAGE_ERROR, ICON_ERROR_ALT), 'text' => $message);
      } elseif ($type == 'warning') {
        $theAlert = array('params' => 'class="messageStackWarning larger"', 'class' => $class, 'icon' => zen_image($template->get_template_dir(ICON_IMAGE_WARNING, DIR_WS_TEMPLATE, $current_page_base,'images/icons'). '/' . ICON_IMAGE_WARNING, ICON_WARNING_ALT), 'text' => $message);
      } elseif ($type == 'success') {
        $theAlert = array('params' => 'class="messageStackSuccess larger"', 'class' => $class, 'icon' => zen_image($template->get_template_dir(ICON_IMAGE_SUCCESS, DIR_WS_TEMPLATE, $current_page_base,'images/icons'). '/' . ICON_IMAGE_SUCCESS, ICON_SUCCESS_ALT), 'text' => $message);
      } elseif ($type == 'caution') {
        $theAlert = array('params' => 'class="messageStackCaution larger"', 'class' => $class, 'icon' => zen_image($template->get_template_dir(ICON_IMAGE_WARNING, DIR_WS_TEMPLATE, $current_page_base,'images/icons'). '/' . ICON_IMAGE_WARNING, ICON_WARNING_ALT), 'text' => $message);
      } else {
        $theAlert = array('params' => 'class="messageStackError larger"', 'class' => $class, 'icon' => $message);
      }

      for ($i=0, $n=sizeof($this->messages); $i<$n; $i++) {
        if ($theAlert['text'] == $this->messages[$i]['text'] && $theAlert['class'] == $this->messages[$i]['class']) $duplicate = true;
      }
      if (!$duplicate) $this->messages[] = $theAlert;
    }
  }

  function add_session($class, $message, $type = 'error') {

    if (!$_SESSION['messageToStack']) {
      $messageToStack = array();
    } else {
      $messageToStack = $_SESSION['messageToStack'];
    }

    $messageToStack[] = array('class' => $class, 'text' => $message, 'type' => $type);
    $_SESSION['messageToStack'] = $messageToStack;
    $this->add($class, $message, $type);
  }

  function reset() {
    $this->messages = array();
  }

  function output($class) {
    global $template, $current_page_base;

    $output = array();
    for ($i=0, $n=sizeof($this->messages); $i<$n; $i++) {
      if ($this->messages[$i]['class'] == $class) {
        $output[] = $this->messages[$i];
      }
    }

    // remove duplicates before displaying
//    $output = array_values(array_unique($output));

    require($template->get_template_dir('tpl_message_stack_default.php',DIR_WS_TEMPLATE, $current_page_base,'templates'). '/tpl_message_stack_default.php');
  }

  function size($class) {
    $count = 0;

    for ($i=0, $n=sizeof($this->messages); $i<$n; $i++) {
      if ($this->messages[$i]['class'] == $class) {
        $count++;
      }
    }

    return $count;
  }
}
?>