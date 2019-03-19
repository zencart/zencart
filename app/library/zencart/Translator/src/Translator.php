<?php

namespace ZenCart\Translator;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;


class Translator
{

    protected $parsed;
    protected $loaded;
    protected $locale;
    protected $fallback;

    public function __construct($loader, $locale, $fallback)
    {
        $this->locale = $locale;
        $this->fallback = $fallback;
        $this->loader = $loader;
    }

    public function trans($key, array $replace = [], $locale = null)
    {
        if (defined($key)) {
            return constant($key);
        }
        return $this->get($key, $replace, $locale);
    }


    public function get($key, array $replace = [], $locale = null, $useFallback = true)
    {
        $locale = isset($locale)
            ? $locale
            : $this->locale;

        $fallback = $useFallback
            ? $this->fallback
            : null;


        list($namespace, $group, $item) = $this->parseKey($key);

        $locales = $fallback ? [$locale, $fallback]
            : [$locale];

        foreach ($locales as $locale) {
                if (! is_null($line = $this->getLine($namespace, $group, $locale, $item, $replace))) {
                    break;
                }
        }

        $translated = isset($line)
            ? $line
            : $item;

        return $translated;
    }

    protected function parseKey($key)
    {
        if (isset($this->parsed[$key])) {
            return $this->parsed[$key];
        }
        if (strpos($key, '::') === false) {
            $segments = explode('.', $key);

            $parsed = $this->parseBasicSegments($segments);
        } else {
            $parsed = $this->parseNamespacedSegments($key);
        }
        return $parsed;
    }

    protected function parseBasicSegments(array $segments)
    {
        // The first segment in a basic array will always be the group, so we can go
        // ahead and grab that segment. If there is only one total segment we are
        // just pulling an entire group out of the array and not a single item.
        $group = $segments[0];

        // If there is more than one segment in this group, it means we are pulling
        // a specific item out of a group and will need to return this item name
        // as well as the group so we know which item to pull from the arrays.
        $item = count($segments) === 1
            ? null
            : implode('.', array_slice($segments, 1));

        return [null, $group, $item];
    }

    /**
     * Parse an array of namespaced segments.
     *
     * @param  string $key
     * @return array
     */
    protected function parseNamespacedSegments($key)
    {

        list($namespace, $item) = explode('::', $key);

        // First we'll just explode the first segment to get the namespace and group
        // since the item should be in the remaining segments. Once we have these
        // two pieces of data we can proceed with parsing out the item's value.
        $itemSegments = explode('.', $item);

        $groupAndItem = array_slice(
            $this->parseBasicSegments($itemSegments), 1
        );

        return array_merge([$namespace], $groupAndItem);
    }

    protected function getLine($namespace, $group, $locale, $item, array $replace)
    {
        $this->load($namespace, $group, $locale);
        $line = Arr::get($this->loaded[$namespace][$group][$locale], $item);

        if (count($replace) && $line) {
            $line = $this->makeReplacements($line, $replace);
        }

        return $line;
    }

    public function load($namespace, $group, $locale)
    {
        if ($this->isLoaded($namespace, $group, $locale)) {
            return;
        }

        $line = null;
        // The loader is responsible for returning the array of language lines for the
        // given namespace, group, and locale. We'll set the lines in this array of
        // lines that have already been loaded so that we can easily access them.
        $line = $this->loader->load($locale, $group, $namespace);

        $this->loaded[$namespace][$group][$locale] = $line;
    }

    protected function isLoaded($namespace, $group, $locale)
    {
        return isset($this->loaded[$namespace][$group][$locale]);
    }

    protected function makeReplacements($line, $replace)
    {
        if (empty($replace)) {
            return $line;
        }

        foreach ($replace as $key => $value) {
            $line = str_replace(
                [':'.$key, ':'.Str::upper($key), ':'.Str::ucfirst($key)],
                [$value, Str::upper($value), Str::ucfirst($value)],
                $line
            );
        }

        return $line;
    }
}