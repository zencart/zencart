<?php
/**
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2022 May 05 New in v1.5.8-alpha $
 */

namespace Zencart\Traits;

use Zencart\Events\EventDto;

trait NotifierManager
{
    /**
     * @var array of aliases
     */
    private array $observerAliases = [
        // this one is an alias to accommodate an old misspelling:
        'NOTIFIY_ORDER_CART_SUBTOTAL_CALCULATE' => 'NOTIFY_ORDER_CART_SUBTOTAL_CALCULATE',
    ];

    public function getRegisteredObservers(): array
    {
        return EventDto::getInstance()->getObservers();
    }

    /**
     * Notify observers that an event has occurred in the notifier object
     * ("Publish" in pub/sub terminology, or "fire event" in "event listener" terminology)
     *
     * Can optionally pass parameters and variables to the observer, useful for passing stuff which is outside of the 'scope' of the observed class.
     * Any of params 2-9 can be passed by reference, and will be updated in the calling location if the observer "update" function also receives them by reference
     *
     * @param string $eventID The event ID to notify/publish.
     * @param mixed|array|null $param1 passed as value only. Usually an array of data, or just a variable, or null if unused.
     * @param mixed|null $param2 passed by reference.
     * @param mixed|null $param3 passed by reference.
     * @param mixed|null $param4 passed by reference.
     * @param mixed|null $param5 passed by reference.
     * @param mixed|null $param6 passed by reference.
     * @param mixed|null $param7 passed by reference.
     * @param mixed|null $param8 passed by reference.
     * @param mixed|null $param9 passed by reference.
     *
     * NOTE: The $param1 is not received-by-reference, but params 2-9 are.
     * NOTE: The $param1 value CAN be an array, and is sometimes typecast to be an array, but can also safely be a string or int etc if the notifier sends such and the observer class expects same.
     */
    public function notify(
        string $eventID,
        mixed $param1 = [],
        mixed &$param2 = null,
        mixed &$param3 = null,
        mixed &$param4 = null,
        mixed &$param5 = null,
        mixed &$param6 = null,
        mixed &$param7 = null,
        mixed &$param8 = null,
        mixed &$param9 = null
    ): void
    {
        // first log that the notifier was triggered:
        $this->logNotifier($eventID, $param1, $param2, $param3, $param4, $param5, $param6, $param7, $param8, $param9);

        $observers = $this->getRegisteredObservers();

        if (empty($observers)) {
            return;
        }

        foreach ($observers as $key => $obs) {
            // identify the event
            $actualEventId = $eventID;
            $matchMap = [$eventID, '*'];

            // Adjust for aliases

            // if the event fired by the notifier is old and has an alias registered
            $hasAlias = $this->eventIdHasAlias($obs['eventID']);
            if ($hasAlias) {
                // then lookup the correct new event name
                $eventAlias = $this->substituteAlias($eventID);
                // use the substituted event name in the list of matches
                $matchMap = [$eventAlias, '*'];
                // and set the Actual event to the name that was originally attached to in the observer class
                $actualEventId = $obs['eventID'];
            }
            // check whether the looped observer's eventID is a match to the event or alias
            if (!in_array($obs['eventID'], $matchMap, true)) {
                continue;
            }

            // Notify the listening observers that this event has been triggered

            $methodsToCheck = [];
            // Check for a snake_cased method name of the notifier Event, ONLY IF it begins with "NOTIFY_" or "NOTIFIER_"
            $snake_case_method = strtolower($actualEventId);
            if (preg_match('/^notif(y|ier)_/', $snake_case_method) && method_exists($obs['obs'], $snake_case_method)) {
                $methodsToCheck[] = $snake_case_method;
            }
            // alternates are a camelCased version starting with "update" ie: updateNotifierNameCamelCased(), or just "update()"
            $methodsToCheck[] = 'update' . \base::camelize(strtolower($actualEventId), true);
            $methodsToCheck[] = 'update';

            foreach ($methodsToCheck as $method) {
                if (method_exists($obs['obs'], $method)) {
                    $obs['obs']->{$method}($this, $actualEventId, $param1, $param2, $param3, $param4, $param5, $param6, $param7, $param8, $param9);
                    continue 2;
                }
            }
            // If no update handler method exists then trigger an error so the problem is logged
            $className = (is_object($obs['obs'])) ? get_class($obs['obs']) : $obs['obs'];
            trigger_error('WARNING: No update() method (or matching alternative) found in the ' . $className . ' class for event ' . $actualEventId, E_USER_WARNING);
        }
    }

    /**
     * Requires a define page, and calls regisistered Observers for content output.
     *
     * @param string $eventID
     * @param mixed|array|null $param1 $param1
     * @param mixed ...$params
     * @return void
     */
    function insertContent(string $eventID, $param1 = [], &...$params)
    {
        global $l;

        // Output any 'define_' page, e.g. CONTENT_FOO will output define_foo.php
        $define_page_name = strtolower(str_replace('CONTENT_', 'DEFINE_', $eventID));
        $this->outputDefinePage($define_page_name);

        $observers = $this->getRegisteredObservers();
        if (empty($observers)) {
            return;
        }
        foreach ($observers as $key => $obs) {
            // Skip observer if it's not registered for a CONTENT_ event.
            if (!str_starts_with($obs['eventID'], 'CONTENT_')) {
                continue;
            }

            // If there is a mapping provided on the observer from event name to define page, use it and continue.
            // This allows easy output of define pages with a different name to the CONTENT_* eventID with no extra coding.
            // e.g. public array $contentDefinePageMap = [
            //  'CONTENT_SOMETHING' => 'define_example_content'
            // ];
            if (!empty($obs['obs']->contentDefinePageMap) && array_key_exists($eventID, $obs['obs']->contentDefinePageMap)) {
                $this->outputDefinePage($obs['obs']->contentDefinePageMap[$eventID]);
                continue;
            }

            // Notify the listening observer that this event has been triggered
            $snake_case_method = strtolower($eventID);
            if (method_exists($obs['obs'], $snake_case_method)) {
                $obs['obs']->{$snake_case_method}($this, $eventID, $params);
            } else {
                // If no update handler method exists then trigger an error so the problem is logged
                $className = (is_object($obs['obs'])) ? get_class($obs['obs']) : $obs['obs'];
                trigger_error('WARNING: No update() method (or matching alternative) found in the ' . $className . ' class for event ' . $actualEventId, E_USER_WARNING);
            }
        }
    }

    /**
     * Write out a named define page, wrapping it in a <div class="define-page"> with extra
     * classes and params defined by arguments.
     *
     * @param string $define_page_name The name of the define page, e.g. define_foo_bar.php
     * @param array $classList Any classes to be added to the wrapper e.g. [ 'warning' ]
     * @param string $params Any other params to be inserted into the <div> e.g. "id='foobar'"
     * @return bool true if output was generated, else false (usually an error)
     */
    protected function outputDefinePage (string $define_page_name, array $classList = [], string $params = null): bool {
        $define_page = zen_get_define_page_content($define_page_name);
        if (empty($define_page)) {
            return false;
        }
        if (empty($classList)) {
            $classList = [];
        }
        if (!empty($params)) {
            $params = ' ' . $params;
        }
        $classList[] = 'define-page';
        // Add a class named after the define page for custom styling, e.g. define-contact-us
        $classList[] = str_replace('_', '-', $define_page_name);
        echo '<div class="' . implode(' ', $classList) . '"' . $params . '>' .
            $define_page .
            '</div>';
        return true;
    }

    protected function logNotifier($eventID, $param1, $param2, $param3, $param4, $param5, $param6, $param7, $param8, $param9): void
    {
        if (!defined('NOTIFIER_TRACE') || empty(NOTIFIER_TRACE) || NOTIFIER_TRACE === 'false' || NOTIFIER_TRACE === 'Off') {
            return;
        }
        global $zcDate;

        $file = DIR_FS_LOGS . '/notifier_trace.log';
        $paramArray = (is_array($param1) && count($param1) === 0) ? [] : ['param1' => $param1];
        for ($i = 2; $i < 10; $i++) {
            $param_n = "param$i";
            if ($$param_n !== null) {
                $paramArray[$param_n] = $$param_n;
            }
        }

        global $this_is_home_page, $PHP_SELF;
        $main_page = (IS_ADMIN_FLAG) ? basename($PHP_SELF) : ($_GET['main_page'] ?? '');
        if (!empty($this_is_home_page)) {
            $main_page = 'index-home';
        }

        $output = '';
        if (count($paramArray)) {
            $output = ', ';
            if (NOTIFIER_TRACE === 'var_export' || NOTIFIER_TRACE === 'var_dump' || NOTIFIER_TRACE === 'true') {
                $output .= var_export($paramArray, true);
            } elseif (NOTIFIER_TRACE === 'print_r' || NOTIFIER_TRACE === 'On' || NOTIFIER_TRACE === true) {
                $output .= print_r($paramArray, true);
            }
        }
        error_log($zcDate->output("%Y-%m-%d %H:%M:%S") . ' [main_page=' . $main_page . '] ' . $eventID . $output . "\n", 3, $file);
    }

    private function eventIdHasAlias($eventId): bool
    {
        return array_key_exists($eventId, $this->observerAliases);
    }

    private function substituteAlias($eventId): bool|int|string
    {
        return array_search($eventId, $this->observerAliases, true);
    }

    public function registerObserverAlias(string $oldEventId, string $newEventId): void
    {
        if ($this->eventIdHasAlias($oldEventId)) {
            return;
        }

        $this->observerAliases[$oldEventId] = $newEventId;
    }
}
