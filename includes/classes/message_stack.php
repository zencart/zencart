<?php
/**
 * messageStack Class.
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Nick Fenwick 2023 Jul 05 Modified in v2.0.0-alpha1 $
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

/**
 * Manage messageStack alerts
 */
class messageStack extends base
{
    /** to override these, call setMessageFormatting() and pass it an array of the desired formats, similar to what getDefaultFormats() returns */
    private array $formats = [];
    /** array of messages to be displayed */
    public array $messages = [];

    public function __construct()
    {
        $this->messages = [];

        if (empty($this->formats)) {
            $this->setMessageFormatting($this->getDefaultFormats());
        }
    }

    public function add($class, $message, $type = 'error')
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

    public function add_session(string $class, string $message, string $type = 'error'): void
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

    public function reset(): void
    {
        $this->messages = [];
    }

    public function output(string $class = 'default')
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
            if ($next_message['class'] === $class) {
                $output[] = $next_message;
            }
        }

        // remove duplicates before displaying
        // $output = array_values(array_unique($output));

        require $template->get_template_dir('tpl_message_stack_default.php', DIR_WS_TEMPLATE, $current_page_base, 'templates') . '/tpl_message_stack_default.php';
    }

    public function size(string $class): int
    {
        if (!empty($_SESSION['messageToStack'])) {
            foreach ($_SESSION['messageToStack'] as $next_message) {
                $this->add($next_message['class'], $next_message['text'], $next_message['type']);
            }
        }

        $count = 0;

        foreach ($this->messages as $next_message) {
            if ($next_message['class'] === $class) {
                $count++;
            }
        }

        return $count;
    }

    public function setMessageFormatting(array $formattingArray = []): void
    {
        foreach ($formattingArray as $messageType => $keys) {
            foreach ($keys as $key => $value) {
                if ($key === 'params') {
                    $this->formats[$messageType]['params'] = $value;
                    continue;
                }
                if ($key === 'icon') {
                    $this->formats[$messageType]['icon'] = $value;
                }
            }
        }
    }

    /**
     * @return array
     */
    public function getDefaultFormats(): array
    {
        global $template, $current_page_base;

        return [
            'error' => [
                'params' => 'class="messageStackError larger"',
                'icon' => zen_image($template->get_template_dir(ICON_IMAGE_ERROR, DIR_WS_TEMPLATE, $current_page_base,'images/icons'). '/' . ICON_IMAGE_ERROR, ICON_ERROR_ALT),
            ],
            'success' => [
                'params' => 'class="messageStackSuccess larger"',
                'icon' => zen_image($template->get_template_dir(ICON_IMAGE_SUCCESS, DIR_WS_TEMPLATE, $current_page_base,'images/icons'). '/' . ICON_IMAGE_SUCCESS, ICON_SUCCESS_ALT),
            ],
            'warning' => [
                'params' => 'class="messageStackWarning larger"',
                'icon' => zen_image($template->get_template_dir(ICON_IMAGE_WARNING, DIR_WS_TEMPLATE, $current_page_base,'images/icons'). '/' . ICON_IMAGE_WARNING, ICON_WARNING_ALT),
            ],
            'caution' => [
                'params' => 'class="messageStackCaution larger"',
                'icon' => zen_image($template->get_template_dir(ICON_IMAGE_WARNING, DIR_WS_TEMPLATE, $current_page_base,'images/icons'). '/' . ICON_IMAGE_WARNING, ICON_WARNING_ALT),
            ],
            'default' => [
                'params' => 'class="messageStackError larger"'],
            ];
    }
}
