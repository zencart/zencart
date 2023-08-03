<?php
/**
 * messageStack Class.
 *
 * @copyright Copyright 2003-2023 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2022 Oct 16 Modified in v1.5.8a $
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

/**
 * Manage messageStack alerts
 *
 */
class messageStack extends base
{
    /** to override these, call setMessageFormatting() and pass it an array of the desired formats, similar to what getDefaultFormats() returns */
    private $formats = [];
    /** array of messages to be displayed */
    public $messages = [];

    function __construct()
    {
        $this->messages = [];

        if (empty($this->formats)) {
            $this->setMessageFormatting($this->getDefaultFormats());
        }
    }

    function add($class, $message, $type = 'error')
    {
        $message = trim($message);
        $duplicate = false;

        if (strlen($message) > 0) {

            $theAlert['class'] = $class;

            if (!in_array($type, ['success', 'error', 'warning', 'caution'])) {
                $type = 'default';
            }

            if (isset($this->formats[$type]['params'])) {
                $theAlert['params'] = $this->formats[$type]['params'];
            }

            if (isset($this->formats[$type]['icon'])) {
                $theAlert['text'] = $this->formats[$type]['icon'] . '  ' . $message;
            } else {
                $theAlert['text'] = $message;
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
            $messageToStack = [];
        } else {
            $messageToStack = $_SESSION['messageToStack'];
        }

        $messageToStack[] = [
            'class' => $class,
            'text' => $message,
            'type' => $type
        ];
        $_SESSION['messageToStack'] = $messageToStack;
        $this->add($class, $message, $type);
    }

    function reset()
    {
        $this->messages = [];
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
        $_SESSION['messageToStack'] = [];

        if ($this->size($class) === 0) {
            return;
        }

        $output = [];
        foreach ($this->messages as $next_message) {
            if ($next_message['class'] == $class) {
                $output[] = $next_message;
            }
        }

        // remove duplicates before displaying
        // $output = array_values(array_unique($output));

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

    /**
     * @param array $formattingArray
     */
    function setMessageFormatting($formattingArray = [])
    {
        foreach ($formattingArray as $messageType => $keys) {
            foreach ($keys as $key => $value) {
                if ($key == 'params') {
                    $this->formats[$messageType]['params'] = $value;
                    continue;
                }
                if ($key == 'icon') {
                    $this->formats[$messageType]['icon'] = $value;
                }
            }
        }
    }

    /**
     * @return array
     */
    function getDefaultFormats()
    {
        global $template, $current_page_base;

        return [
            'error' => [
                'params' => 'class="messageStackError larger"',
                'icon' => zen_image($template->get_template_dir(ICON_IMAGE_ERROR, DIR_WS_TEMPLATE, $current_page_base,'images/icons'). '/' . ICON_IMAGE_ERROR, ICON_ERROR_ALT),
//                'params' => 'class="alert alert-danger"',
//                'icon' => '<i class="fas fa-exclamation-triangle fa-lg" data-title="' . ICON_ERROR_ALT . '"></i>  ',
            ],
            'success' => [
                'params' => 'class="messageStackSuccess larger"',
                'icon' => zen_image($template->get_template_dir(ICON_IMAGE_SUCCESS, DIR_WS_TEMPLATE, $current_page_base,'images/icons'). '/' . ICON_IMAGE_SUCCESS, ICON_SUCCESS_ALT),
//                'params' => 'class="alert alert-success"',
//                'icon' => '<i class="fas fa-exclamation-circle fa-lg" data-title="' . ICON_SUCCESS_ALT . '"></i>  ',
            ],
            'warning' => [
                'params' => 'class="messageStackWarning larger"',
                'icon' => zen_image($template->get_template_dir(ICON_IMAGE_WARNING, DIR_WS_TEMPLATE, $current_page_base,'images/icons'). '/' . ICON_IMAGE_WARNING, ICON_WARNING_ALT),
//                'params' => 'class="alert alert-warning"',
//                'icon' => '<i class="fas fa-exclamation-circle fa-lg" data-title="' . ICON_WARNING_ALT . '"></i>  ',
            ],
            'caution' => [
                'params' => 'class="messageStackCaution larger"',
                'icon' => zen_image($template->get_template_dir(ICON_IMAGE_WARNING, DIR_WS_TEMPLATE, $current_page_base,'images/icons'). '/' . ICON_IMAGE_WARNING, ICON_WARNING_ALT),
//                'params' => 'class="alert alert-info"',
//                'icon' => '<i class="fas fa-exclamation-circle fa-lg" data-title="' . ICON_CAUTION_ALT . '"></i>  ',
            ],
            'default' => [
                'params' => 'class="messageStackError larger"'],
//                'params' => 'class="alert alert-danger"'],
            ];
    }
}
