<?php
/**
 * messageStack Class.
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Zcwilt 2020 Apr 07 Modified in v1.5.7 $
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}
/**
 * messageStack Class.
 * This class is used to manage messageStack alerts
 *
 */
class messageStack extends base 
{
    // class constructor
    function __construct() 
    {
        $this->messages = array();
    }

    function add($class, $message, $type = 'error') 
    {
        global $template, $current_page_base;
        $message = trim($message);
        $duplicate = false;
        if (strlen($message) > 0) {
            if ($type == 'error') {
                $theAlert = array(
                    'params' => 'class="messageStackError larger"', 
                    'class' => $class, 
                    'text' => zen_image($template->get_template_dir(ICON_IMAGE_ERROR, DIR_WS_TEMPLATE, $current_page_base,'images/icons'). '/' . ICON_IMAGE_ERROR, ICON_ERROR_ALT) . '  ' . $message
                );
            } elseif ($type == 'warning') {
                $theAlert = array(
                    'params' => 'class="messageStackWarning larger"', 
                    'class' => $class, 
                    'text' => zen_image($template->get_template_dir(ICON_IMAGE_WARNING, DIR_WS_TEMPLATE, $current_page_base,'images/icons'). '/' . ICON_IMAGE_WARNING, ICON_WARNING_ALT) . '  ' . $message
                );
            } elseif ($type == 'success') {
                $theAlert = array(
                    'params' => 'class="messageStackSuccess larger"', 
                    'class' => $class, 
                    'text' => zen_image($template->get_template_dir(ICON_IMAGE_SUCCESS, DIR_WS_TEMPLATE, $current_page_base,'images/icons'). '/' . ICON_IMAGE_SUCCESS, ICON_SUCCESS_ALT) . '  ' . $message
                );
            } elseif ($type == 'caution') {
                $theAlert = array(
                    'params' => 'class="messageStackCaution larger"', 
                    'class' => $class, 
                    'text' => zen_image($template->get_template_dir(ICON_IMAGE_WARNING, DIR_WS_TEMPLATE, $current_page_base,'images/icons'). '/' . ICON_IMAGE_WARNING, ICON_WARNING_ALT) . '  ' . $message
                );
            } else {
                $theAlert = array(
                    'params' => 'class="messageStackError larger"', 
                    'class' => $class, 
                    'text' => $message
                );
            }

            foreach ($this->messages as $next_message) {
                if ($theAlert['text'] == $next_message['text'] && $theAlert['class'] == $next_message['class']) {
                    $duplicate = true;
                    break;
                }
            }
            if (!$duplicate) {
                $this->messages[] = $theAlert;
            }
        }
    }

    function add_session($class, $message, $type = 'error') 
    {
        if (empty($_SESSION['messageToStack'])) {
            $messageToStack = array();
        } else {
            $messageToStack = $_SESSION['messageToStack'];
        }

        $messageToStack[] = array(
            'class' => $class, 
            'text' => $message, 
            'type' => $type
        );
        $_SESSION['messageToStack'] = $messageToStack;
        $this->add($class, $message, $type);
    }

    function reset() 
    {
        $this->messages = array();
    }

    function output($class) 
    {
        global $template, $current_page_base;

        if ($this->size($class) === 0) {
            return;
        }
        
        $output = array();
        foreach ($this->messages as $next_message) {
            if ($next_message['class'] == $class) {
                $output[] = $next_message;
            }
        }

    // remove duplicates before displaying
//    $output = array_values(array_unique($output));

        require $template->get_template_dir('tpl_message_stack_default.php', DIR_WS_TEMPLATE, $current_page_base, 'templates') . '/tpl_message_stack_default.php';
    }

    function size($class) 
    {
        if (!empty($_SESSION['messageToStack'])) {
            foreach ($_SESSION['messageToStack'] as $next_message) {
                $this->add($next_message['class'], $next_message['text'], $next_message['type']);
            }
        }

        $_SESSION['messageToStack'] = array();

        $count = 0;

        foreach ($this->messages as $next_message) {
            if ($next_message['class'] == $class) {
                $count++;
            }
        }

        return $count;
    }
}
