<?php
/**
 *
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 *
 */
namespace Aura\Web\Request;

/**
 *
 * Representation of the request content.
 *
 * @package Aura.Web
 *
 */
class Content
{
    /**
     *
     * Content decoder callables.
     *
     * @var array
     *
     */
    protected $decoders = array(
        'application/json' => 'json_decode',
        'application/x-www-form-urlencoded' => 'parse_str',
    );

    /**
     *
     * The value of the Content-Type header.
     *
     * @var string
     *
     */
    protected $type;

    /**
     *
     * The value of the Content-Length header.
     *
     * @var int
     *
     */
    protected $length;

    /**
     *
     * The value of the Content-MD5 header.
     *
     * @var int
     *
     */
    protected $md5;

    /**
     *
     * The decoded content.
     *
     * @var mixed
     *
     */
    protected $value;

    /**
     *
     * The raw content.
     *
     * @var mixed
     *
     */
    protected $raw;

    /**
     *
     * Constructor.
     *
     * @param array $server An array of $_SERVER values.
     *
     * @param array $decoders Additional content decoder callables.
     *
     */
    public function __construct(
        array $server,
        array $decoders = array()
    ) {
        $this->setTypeAndCharset($server);
        $this->setLength($server);

        $this->md5 = isset($server['HTTP_CONTENT_MD5'])
                   ? strtolower($server['HTTP_CONTENT_MD5'])
                   : null;

        $this->decoders = array_merge($this->decoders, $decoders);
    }

    /**
     *
     * Sets the content type.
     *
     * @param array $server A copy of $_SERVER.
     *
     * @return null
     *
     */
    protected function setTypeAndCharset($server)
    {
        // Catches the content values with "HTTP_" prefix. This addresses a bug
        // in the built in PHP server https://bugs.php.net/bug.php?id=66606
        $value = '';
        if (isset($server['CONTENT_TYPE'])) {
            $value = strtolower($server['CONTENT_TYPE']);
        } elseif (isset($server['HTTP_CONTENT_TYPE'])) {
            $value = strtolower($server['HTTP_CONTENT_TYPE']);
        }

        list($this->type, $this->charset) = $this->getTypeAndCharsetFromHeader($value);
    }

    /**
     *
     * Gets the content-type and related charset from the Content-Type header.
     *
     * @param string $value The Content-Type header value.
     *
     * @return array An array where element 0 is the conten type and element 1
     * is the charset (if any).
     *
     */
    protected function getTypeAndCharsetFromHeader($value)
    {
        $parts = explode(';', $value);
        $type = array_shift($parts);
        $charset = '';
        if ($parts) {
            $charset = $this->getCharsetFromHeader($parts);
        }
        return array($type, $charset);
    }

    /**
     *
     * Gets the charset value from the Content-Type header.
     *
     * @param array $parts The Content-Type header value exploded into its
     * constituent parts at the semicolons.
     *
     * @return string The charset value, if any.
     *
     */
    protected function getCharsetFromHeader($parts)
    {
        foreach ($parts as $part) {
            $part = str_replace(' ', '', $part);
            if (substr($part, 0, 8) == 'charset=') {
                return substr($part, 8);
            }
        }
    }

    /**
     *
     * Sets the content length.
     *
     * @param array $server A copy of $_SERVER.
     *
     * @return null
     *
     */
    protected function setLength($server)
    {
        // Catches the content values with "HTTP_" prefix. This addresses a bug
        // in the built in PHP server https://bugs.php.net/bug.php?id=66606
        if (isset($server['CONTENT_LENGTH'])) {
            $this->length = strtolower($server['CONTENT_LENGTH']);
        } elseif (isset($server['HTTP_CONTENT_LENGTH'])) {
            $this->length = strtolower($server['HTTP_CONTENT_LENGTH']);
        }
    }

    /**
     *
     * Request body after decoding it based on the content type.
     *
     * @return string The decoded request body.
     *
     */
    public function get()
    {
        if ($this->value === null) {
            $this->value = $this->getRaw();
            if (isset($this->decoders[$this->type])) {
                $decode = $this->decoders[$this->type];
                $this->value = $decode($this->value);
            }
        }

        return $this->value;
    }

    /**
     *
     * The raw request body.
     *
     * @return string Raw request body.
     *
     */
    public function getRaw()
    {
        if ($this->raw === null) {
            $this->raw = file_get_contents('php://input');
        }
        return $this->raw;
    }

    /**
     *
     * The content-type of the request body.
     *
     * @return string
     *
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     *
     * The charset of the request body.
     *
     * @return string
     *
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     *
     * The content-length of the request body.
     *
     * @return string
     *
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     *
     * The MD5 of the request body.
     *
     * @return string
     *
     */
    public function getMd5()
    {
        return $this->md5;
    }
}
