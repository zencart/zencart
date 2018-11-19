<?php
/**
 *
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 *
 */
namespace Aura\Web\Response;

/**
 *
 * Headers to send with the response.
 *
 * @package Aura.Web
 *
 * @todo automatically handle dates
 *
 * @see https://en.wikipedia.org/wiki/List_of_HTTP_headers
 *
 */
class Headers
{
    /**
     *
     * The response headers.
     *
     * @var array
     *
     */
    protected $headers = array();

    /**
     *
     * Sets a header value in `$headers`.
     *
     * @param string $label The header label.
     *
     * @param string $value The value for the header; an empty-string/null/
     * false value will unset the header (although a zero will not).
     *
     * @return null
     *
     */
    public function set($label, $value)
    {
        $label = $this->sanitizeLabel($label);
        $value = $this->sanitizeValue($value);
        if ($value === '') {
            unset($this->headers[$label]);
            return;
        }
        $this->headers[$label] = $value;
    }

    /**
     *
     * Returns the value of a single header, or all headers.
     *
     * @param string $label The header name.
     *
     * @return string The header value.
     *
     */
    public function get($label = null)
    {
        if (! $label) {
            return $this->headers;
        }

        $label = $this->sanitizeLabel($label);
        if (isset($this->headers[$label])) {
            return $this->headers[$label];
        }
    }

    /**
     *
     * Normalizes and sanitizes a header label.
     *
     * @param string $label The header label to be sanitized.
     *
     * @return string The sanitized header label.
     *
     */
    protected function sanitizeLabel($label)
    {
        $label = preg_replace('/[^a-zA-Z0-9-]/', '', $label);
        $label = ucwords(strtolower(str_replace('-', ' ', $label)));
        $label = str_replace(' ', '-', $label);
        return $label;
    }

    /**
     *
     * Sanitizes a header value.
     *
     * @param string $value The header value to be sanitized.
     *
     * @return string The sanitized header value.
     *
     */
    protected function sanitizeValue($value)
    {
        return str_replace(array("\r", "\n"), '', trim($value));
    }
}
