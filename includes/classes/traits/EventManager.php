<?php


namespace zencart\Traits;


trait EventManager
{

    public function notify($eventID, $param1 = array(), & $param2 = null, & $param3 = null, & $param4 = null, & $param5 = null, & $param6 = null, & $param7 = null, & $param8 = null, & $param9 = null)
    {
        $this->logNotifier($eventID, $param1, $param2, $param3, $param4, $param5, $param6, $param7, $param8, $param9);
        $observers = & self::getStaticObserver();
        if (is_null($observers)) {
            return;
        }
        foreach($observers as $key=>$obs) {
            if ($obs['eventID'] == $eventID || $obs['eventID'] === '*') {
                $method = 'update';
                $testMethod = $method . self::camelize(strtolower($eventID), TRUE);
                if (method_exists($obs['obs'], $testMethod))
                    $method = $testMethod;
                $obs['obs']->{$method}($this, $eventID, $param1,$param2,$param3,$param4,$param5,$param6,$param7,$param8,$param9);
            }
        }
    }
    protected function & getStaticObserver() {
        return self::getStaticProperty('observer');
    }

    protected function & getStaticProperty($var)
    {
        static $staticProperty;
        return $staticProperty;
    }

    protected function logNotifier($eventID, $param1, $param2, $param3, $param4, $param5, $param6, $param7, $param8, $param9)
    {
        if (!defined('NOTIFIER_TRACE') || NOTIFIER_TRACE == '' || NOTIFIER_TRACE == 'false' || NOTIFIER_TRACE == 'Off') {
            return;
        }
        $file = DIR_FS_LOGS . '/notifier_trace.log';
        $paramArray = (is_array($param1) && sizeof($param1) == 0) ? array() : array('param1' => $param1);
        for ($i = 2; $i < 10; $i++) {
            $param_n = "param$i";
            if ($$param_n !== NULL) {
                $paramArray[$param_n] = $$param_n;
            }
        }
        global $this_is_home_page, $PHP_SELF;
        $main_page = (isset($this_is_home_page) && $this_is_home_page) ? 'index-home' : ((IS_ADMIN_FLAG) ? basename($PHP_SELF) : (isset($_GET['main_page']) ? $_GET['main_page'] : ''));
        $output = '';
        if (count($paramArray)) {
            $output = ', ';
            if (NOTIFIER_TRACE == 'var_export' || NOTIFIER_TRACE == 'var_dump' || NOTIFIER_TRACE == 'true') {
                $output .= var_export($paramArray, true);
            } elseif (NOTIFIER_TRACE == 'print_r' || NOTIFIER_TRACE == 'On' || NOTIFIER_TRACE === TRUE) {
                $output .= print_r($paramArray, true);
            }
        }
        error_log( strftime("%Y-%m-%d %H:%M:%S") . ' [main_page=' . $main_page . '] ' . $eventID . $output . "\n", 3, $file);
    }
}
