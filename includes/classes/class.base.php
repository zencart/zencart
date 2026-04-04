<?php
/**
 * This \base class is sometimes used as a parent class for other classes, but it is not intended to be instantiated on its own.
 * It provides some common functionality that can be shared to child classes, such as the ability to manage observers and notifiers.
 * The class includes two traits, NotifierManager and ObserverManager, which provide methods for managing observers and notifiers respectively.
 * The class also includes a static method called camelize, which takes a string as input and converts it to camel case format. The method can optionally capitalize the first letter of the resulting string.
 * The camelize method uses a regular expression to find all occurrences of underscores or hyphens followed by a lowercase letter, and replaces them with the uppercase version of that letter. This allows for easy conversion of strings like "my_variable_name" to "myVariableName" or "MyVariableName" depending on the value of the $camelFirst parameter.
 * Overall, the \base class serves as a foundation for other classes in the Zen Cart application, providing common functionality and utility methods that can be used across the application.
 *
 * However, in practice, it is better to avoid using the \base class as a parent class for other classes,
 * and instead to directly use the NotifierManager and ObserverManager traits in the classes that need respective functionality.
 * In the vast majority of cases, most classes are likely to only need ObserverManager because they're only listening for notifiers that are fired from elsewhere in the core code.
 * However, if a class needs to call ->notify() to fire notifiers, then it needs to use the NotifierManager trait as well.
 *
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2026 Mar 18 Modified in v2.2.1 $
 * @since ZC v1.3.0
 */
use Zencart\Traits\NotifierManager;
use Zencart\Traits\ObserverManager;

class base
{
    use NotifierManager;
    use ObserverManager;

    /**
     * @since ZC v1.5.2
     */
    public static function camelize($rawName, $camelFirst = false)
    {
        if ($rawName == "")
            return $rawName;
        if ($camelFirst) {
            $rawName[0] = strtoupper($rawName[0]);
        }
        return preg_replace_callback('/[_-]([0-9,a-z])/', function ($matches) {
            return strtoupper($matches[1]);
        }, $rawName);
    }
}
