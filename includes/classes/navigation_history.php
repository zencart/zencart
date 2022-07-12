<?php
/**
 * Navigation_history Class.
 *
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2022 May 05 Modified in v1.5.8-alpha $
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}
/**
 * Navigation_history Class.
 * This class is used to manage navigation snapshots
 *
 */
class navigationHistory extends base
{
    public
        $path,
        $snapshot;

    public function __construct()
    {
        $this->reset();
    }

    public function reset()
    {
        $this->path = [];
        $this->snapshot = [];
    }

    public function add_current_page()
    {
        // check whether there are pages which should be blacklisted against entering navigation history
        if (preg_match('|ajax\.php$|', $_SERVER['SCRIPT_NAME']) && $_GET['act'] !== '') {
            return;
        }

        global $request_type, $cPath;
        $get_vars = $_GET;
        unset($get_vars['main_page']);

        $set = 'true';
        for ($i = 0, $n = count($this->path); $i < $n; $i++) {
            if (isset($_GET['main_page']) && $this->path[$i]['page'] === $_GET['main_page']) {
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
                            if ($exit_loop == true) {
                                break;
                            }
                        }
                    }
                } else {
                    array_splice($this->path, ($i));
                    $set = 'true';
                    break;
                }
            }
        }

        if ($set === 'true') {
            $page = (isset($_GET['main_page'])) ? $_GET['main_page'] : FILENAME_DEFAULT;
             $this->path[] = [
                'page' => $page,
                'mode' => $request_type,
                'get' => $get_vars,
                'post' => [] /*$_POST*/
            ];
        }
    }

    public function remove_current_page()
    {
        $last_entry_position = count($this->path) - 1;
        if (isset($this->path[$last_entry_position]['page']) && isset($_GET['main_page']) && $this->path[$last_entry_position]['page'] === $_GET['main_page']) {
            unset($this->path[$last_entry_position]);
        }
    }

    public function set_snapshot($page = '')
    {
        global $request_type;
        if (is_array($page)) {
            $this->snapshot = array_merge(['get' => [], 'post' => []], $page);
        } else {
            $get_vars = $_GET;
            unset($get_vars['main_page']);
            $page = (isset($_GET['main_page'])) ? $_GET['main_page'] : FILENAME_DEFAULT;
            $this->snapshot = [
                'page' => $page,
                'mode' => $request_type,
                'get' => $get_vars,
                'post' => [] /*$_POST*/
            ];
        }
    }

    public function clear_snapshot()
    {
        $this->snapshot = [];
    }

    public function set_path_as_snapshot($history = 0)
    {
        $pos = count($this->path) -1 -$history;
        $this->snapshot = [
            'page' => $this->path[$pos]['page'],
            'mode' => $this->path[$pos]['mode'],
            'get' => $this->path[$pos]['get'],
            'post' => $this->path[$pos]['post']
        ];
    }

    public function debug()
    {
        for ($i = 0, $n = count($this->path); $i < $n; $i++) {
            echo $this->path[$i]['page'] . '?';
            foreach ($this->path[$i]['get'] as $key => $value) {
                echo $key . '=' . $value . '&';
            }
            if (count($this->path[$i]['post']) !== 0) {
                echo '<br>';
                foreach ($this->path[$i]['post'] as $key => $value) {
                    echo '&nbsp;&nbsp;<strong>' . $key . '=' . $value . '</strong><br>';
                }
            }
            echo '<br>';
        }

        if (count($this->snapshot) !== 0) {
            echo '<br><br>';

            echo $this->snapshot['mode'] . ' ' . $this->snapshot['page'] . '?' . zen_array_to_string($this->snapshot['get'], [zen_session_name()]) . '<br>';
        }
    }

    public function unserialize($broken)
    {
        foreach ($broken as $kv) {
            $key = $kv['key'];
            if (gettype($this->$key) !== 'user function') {
                $this->$key = $kv['value'];
            }
        }
    }
}
