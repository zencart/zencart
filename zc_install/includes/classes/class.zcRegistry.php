<?php
/**
 * registry class.
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

/**
 * Registry Class
 *
 * The registry class is used as a store for concrete objects and other variables
 *
 * Its main use is to help remove the need for accessing
 * objects/variables using globals in any class that needs access to an object/variable.
 * Where possible needed objects/variables should be passed to other objects as function parameters
 * however this is somtimes not possible as we never know the depth of an object to object relationship
 * so as long as an object has been added to the registry we can access it by first getting a singleton instance of the
 * registry, and then accessing the object from there,
 * BIG PROBLEM: namespaces : for core code this is not a problem as we control the naming of objects
 * however it is possible that 3rd party code will produce namespace clashes. Can only be overcome by a naming standard
 * for 3rd party objects.
 *
 */
class zcRegistry extends base
{
    /**
     * array used to hold registry values
     */
    public static array $values;

    /**
     * getter method to return a registry entry
     */
    public static function getValue(string $keyName): mixed
    {
        if (isset(self::$values[$keyName])) {
            return self::$values[$keyName];
        }

        throw new zcGeneralException('zcRegistry key not set ' . $keyName, 0);
    }

    public static function getValueDefault(string $keyName, mixed $default = ''): mixed
    {
        return self::$values[$keyName] ?? $default;
    }

    /**
     * method to set a registry entry
     */
    public static function setValue(string $keyName, mixed $keyValue): void
    {
        self::$values[$keyName] = $keyValue;
    }

    /**
     * method to determine if registry entry has been set
     */
    public static function isValueSet(string $keyName): bool
    {
        if (isset(self::$values[$keyName])) {
            return true;
        }

        return false;
    }

    public static function unSetValue($keyName): void
    {
        if (isset(self::$values[$keyName])) {
            unset(self::$values[$keyName]);
        } else {
            throw new zcGeneralException('zcRegistry key not set ' . $keyName, 0);
        }
    }

    public static function getRawValues(): array
    {
        return self::$values;
    }
}
