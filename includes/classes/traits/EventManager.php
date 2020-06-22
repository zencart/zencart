<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Zcwilt 2020 May 23 New in v1.5.7 $
 */

namespace Zencart\Traits;

trait EventManager
{

    public function notify($eventID, $param1 = array(), & $param2 = null, & $param3 = null, & $param4 = null, & $param5 = null, & $param6 = null, & $param7 = null, & $param8 = null, & $param9 = null)
    {
        $this->logNotifier($eventID, $param1, $param2, $param3, $param4, $param5, $param6, $param7, $param8, $param9);


        $observers = &self::getStaticObserver();
        if (is_null($observers)) {
            return;
        }
        foreach ($observers as $key => $obs) {
            // identify the event
            $actualEventId = $eventID;
            $matchMap = [$eventID, '*'];
            if (!in_array($obs['eventID'], $matchMap)) {
                continue;
            }

            // Notify the listening observers that this event has been triggered

            // First we check for a snake_cased method name of the notifier Event, IF IT STARTS WITH "notify_" or "notifier_"
            $snake_case_method = strtolower($actualEventId);
            if (preg_match('/^notif(y|ier)_/', $snake_case_method) && method_exists($obs['obs'], $snake_case_method)) {
                $obs['obs']->{$snake_case_method}($this, $actualEventId, $param1, $param2, $param3, $param4, $param5, $param6, $param7, $param8, $param9);
                return;
            }
            // If the first check failed, we check for a camelCased version starting with "update" ie: updateNotifierNameCamelCased()
            // If the camelCased version eis not found, we call "update()".
            $method = 'update';
            $camelCaseMethod = 'update' . \base::camelize(strtolower($actualEventId), true);
            if (method_exists($obs['obs'], $camelCaseMethod)) {
                $method = $camelCaseMethod;
            }
            // If it doesn't exist then a PHP fatal error will occur.
            if (method_exists($obs['obs'], $method)) {
                $obs['obs']->{$method}($this, $actualEventId, $param1, $param2, $param3, $param4, $param5, $param6, $param7, $param8, $param9);
            } else {
                $className = (is_object($obs['obs'])) ? get_class($obs['obs']) : $obs['obs'];
                trigger_error('WARNING: No update() method (or matching alternative) found in the ' . $className . ' class for event ' . $actualEventId, E_USER_WARNING);
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
        $paramArray = (is_array($param1) && count($param1) == 0) ? array() : array('param1' => $param1);
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
            } elseif (NOTIFIER_TRACE == 'print_r' || NOTIFIER_TRACE == 'On' || NOTIFIER_TRACE === true) {
                $output .= print_r($paramArray, true);
            }
        }
        error_log( strftime("%Y-%m-%d %H:%M:%S") . ' [main_page=' . $main_page . '] ' . $eventID . $output . "\n", 3, $file);
    }
}
