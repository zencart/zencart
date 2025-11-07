<?php
/**
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2025 Sep 18 Modified in v2.2.0 $
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

/**
 * @since ZC v1.0.3
 */
class messageStack extends boxTableBlock
{
    public int $size = 0;
    public array $errors = [];

    /**
     * @since ZC v1.0.3
     */
    public function add(string $message, string $type = 'error'): void
    {
        if ($type === 'error') {
            $this->errors[] = ['params' => 'messageStackAlert alert alert-danger', 'text' => '<i class="fa-solid fa-2x fa-circle-exclamation"></i> ' . $message];
        } elseif ($type === 'warning') {
            $this->errors[] = ['params' => 'messageStackAlert alert alert-warning', 'text' => '<i class="fa-solid fa-2x fa-circle-question"></i> ' . $message];
        } elseif ($type === 'info') {
            $this->errors[] = ['params' => 'messageStackAlert alert alert-info', 'text' => '<i class="fa-solid fa-2x fa-circle-info"></i> ' . $message];
        } elseif ($type === 'success') {
            $this->errors[] = ['params' => 'messageStackAlert alert alert-success', 'text' => '<i class="fa-solid fa-2x fa-circle-check"></i> ' . $message];
        } elseif ($type === 'caution') {
            $this->errors[] = ['params' => 'messageStackAlert alert alert-warning', 'text' => '<i class="fa-solid fa-2x fa-circle-xmark"></i> ' . $message];
        } else {
            $this->errors[] = ['params' => 'messageStackAlert alert alert-danger', 'text' => $message];
        }

        $this->size++;
    }

    /**
     * @since ZC v1.0.3
     */
    public function add_session(string $message, string $type = 'error'): void
    {
        if (!isset($_SESSION['messageToStack']) || !is_array($_SESSION['messageToStack'])) {
            $_SESSION['messageToStack'] = [];
        }

        $_SESSION['messageToStack'][] = ['text' => $message, 'type' => $type];
    }

    /**
     * @since ZC v1.5.7
     */
    public function add_from_session(): void
    {
        if (isset($_SESSION['messageToStack']) && is_array($_SESSION['messageToStack'])) {
            for ($i = 0, $n = count($_SESSION['messageToStack']); $i < $n; $i++) {
                $this->add($_SESSION['messageToStack'][$i]['text'], $_SESSION['messageToStack'][$i]['type']);
            }
            $_SESSION['messageToStack'] = '';
        }
    }

    /**
     * @since ZC v1.0.3
     */
    public function reset(): void
    {
        $this->errors = [];
        $this->size = 0;
    }

    /**
     * @since ZC v1.0.3
     */
    public function output(string $class='')
    {
        $this->table_data_parameters = 'class="messageBox"';
        return $this->tableBlock($this->errors);
    }
}
