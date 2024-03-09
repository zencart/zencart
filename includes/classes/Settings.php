<?php

/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Mar 07 New in v2.0.0-rc1 $
 */

abstract class Settings implements ArrayAccess, Countable
{
    /**
     * If $this->settings[$key] not found, look for a defined($key) constant.
     */
    protected bool $includeConstants = false;

    /**
     * Stores all the "set" settings
     */
    protected array $settings = [];

    /**
     * Tracks requested type-casting instructions
     */
    protected array $types = [];

    public function __construct(array $settings = [])
    {
        $this->setFromArray($settings);
    }

    /**
     * Set multiple settings via an array.
     * The array can be entries of $key=>$value and/or $key=>['value'=>$value, 'type'=>$type]
     * If $overwrite is false then existing $keys will be ignored.
     *
     * @param array|null $settings_array
     * @param bool $overwrite
     * @return void
     */
    public function setFromArray(?array $settings_array = null, bool $overwrite = false): void
    {
        if (empty($settings_array)) {
            return;
        }

        foreach ($settings_array as $key => $value) {
            // caveat: offsetExists() also checks for constants if the flag is enabled; bypass by calling setType() instead.
            if (!$overwrite && $this->offsetExists($key)) {
                continue;
            }

            $this->offsetSet($key, $value);
        }
    }

    /**
     * Specify a PHP data type to be cast to when accessing a setting as a class property
     *
     */
    public function setType(string $key, ?string $type = null): void
    {
        if (!in_array($type, ['string', 'boolean', 'bool', 'int', 'integer', 'double', 'real', 'float', 'array', null], true)) {
            throw new TypeError('Invalid type specified: ' . $type);
        }

        if ($this->offsetExists($key)) {
            $this->types[$key] = $type;
        }
    }

    /**
     * Cast a value to a desired type
     */
    protected function returnCastValue(mixed $value, ?string $cast_to): mixed
    {
        if ($cast_to === null) {
            return $value;
        }

        // Handle boolean strings if boolean requested
        if (is_string($value) && str_starts_with($cast_to, 'bool') && in_array($value, ['true', 'TRUE', 'false', 'FALSE',])) {
            return match ($value) {
                'true', 'TRUE' => true,
                'false', 'FALSE' => false,
            };
        }

        return match ($cast_to) {
            'string' => (string)$value,
            'boolean', 'bool' => (bool)$value,
            'int', 'integer' => (int)$value,
            'double', 'real', 'float' => (float)$value,
            'array' => (is_array($value)) ? $value : [$value],
            default => $value,
        };
    }

    protected function globalConstantExists(string $constant_name): bool
    {
        return defined($constant_name);
    }

    protected function getGlobalConstant(string $constant_name): mixed
    {
        return defined($constant_name) ? constant($constant_name) : null;
    }

    public function __isset($key)
    {
        return $this->offsetExists($key);
    }

    /**
     * @implements ArrayAccess
     */
    public function offsetExists(mixed $offset): bool
    {
        if ($this->includeConstants) {
            return isset($this->settings[$offset]) || $this->globalConstantExists($offset);
        }

        return isset($this->settings[$offset]);
    }

    public function __set($setting, $value)
    {
        $this->offsetSet($setting, $value);
    }

    /**
     * @implements ArrayAccess
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (is_null($offset)) {
            throw new TypeError('Key must not be null.');
        }

        if (isset($value['value'], $value['type'])) {
            $this->settings[$offset] = $value['value'];
            $this->types[$offset] = $value['type'];
        } elseif (isset($value['value'])) {
            $this->settings[$offset] = $value['value'];
        } elseif (isset($value['type'])) {
            $this->types[$offset] = $value['type'];
        } else {
            $this->settings[$offset] = $value;
        }
    }

    public function __unset(string $key)
    {
        $this->offsetUnset($key);
    }

    /**
     * @implements ArrayAccess
     */
    public function offsetUnset(mixed $offset): void
    {
        if ($this->offsetExists($offset)) {
            unset($this->settings[$offset], $this->types[$offset]);
        }
    }

    public function __get(string $key)
    {
        if (!$this->offsetExists($key)) {
            return null;
        }

        if ($this->includeConstants) {
            return $this->returnCastValue($this->settings[$key] ?? $this->getGlobalConstant($key), $this->types[$key] ?? null);
        }

        return $this->returnCastValue($this->settings[$key], $this->types[$key] ?? null);
    }

    /**
     * @implements ArrayAccess
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->offsetExists($offset) ? $this->__get($offset) : null;
    }

    /**
     * @implements Countable
     */
    public function count(): int
    {
        return count($this->settings);
    }
}
