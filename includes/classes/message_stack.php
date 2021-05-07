<?php
/**
 * messageStack Class.
 *
 * @copyright Copyright 2003-2021 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Leonard 2021 Jan 15 Modified in v1.5.8 $
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

if (!defined('MESSAGESTACK_ICON_ERROR')) define('MESSAGESTACK_ICON_ERROR',zen_image($template->get_template_dir(ICON_IMAGE_ERROR, DIR_WS_TEMPLATE, $current_page_base,'images/icons'). '/' . ICON_IMAGE_ERROR, ICON_ERROR_ALT));
if (!defined('MESSAGESTACK_ICON_WARNING')) define('MESSAGESTACK_ICON_WARNING',zen_image($template->get_template_dir(ICON_IMAGE_WARNING, DIR_WS_TEMPLATE, $current_page_base,'images/icons'). '/' . ICON_IMAGE_WARNING, ICON_WARNING_ALT));
if (!defined('MESSAGESTACK_ICON_CAUTION')) define('MESSAGESTACK_ICON_CAUTION',zen_image($template->get_template_dir(ICON_IMAGE_WARNING, DIR_WS_TEMPLATE, $current_page_base,'images/icons'). '/' . ICON_IMAGE_WARNING, ICON_WARNING_ALT));
if (!defined('MESSAGESTACK_ICON_SUCCESS')) define('MESSAGESTACK_ICON_SUCCESS',zen_image($template->get_template_dir(ICON_IMAGE_SUCCESS, DIR_WS_TEMPLATE, $current_page_base,'images/icons'). '/' . ICON_IMAGE_SUCCESS, ICON_SUCCESS_ALT));

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
                    'text' => MESSAGESTACK_ICON_ERROR . '  ' . $message
                );
            } elseif ($type == 'warning') {
                $theAlert = array(
                    'params' => 'class="messageStackWarning larger"', 
                    'class' => $class, 
                    'text' => MESSAGESTACK_ICON_WARNING . '  ' . $message
                );
            } elseif ($type == 'success') {
                $theAlert = array(
                    'params' => 'class="messageStackSuccess larger"', 
                    'class' => $class, 
                    'text' => MESSAGESTACK_ICON_SUCCESS . '  ' . $message
                );
            } elseif ($type == 'caution') {
                $theAlert = array(
                    'params' => 'class="messageStackCaution larger"', 
                    'class' => $class, 
                    'text' => MESSAGESTACK_ICON_CAUTION . '  ' . $message
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

        // -----
        // Reset the session-based messages, now that message-output has been requested for
        // at least one $class.  This implies that the 'templating' phase of a page's
        // rendering is in progress and that all applicable messages will be output at this
        // time.
        //
        $_SESSION['messageToStack'] = array();

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

        $count = 0;

        foreach ($this->messages as $next_message) {
            if ($next_message['class'] == $class) {
                $count++;
            }
        }

        return $count;
    }
}
