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
 * Represents the response content.
 *
 * @package Aura.Web
 *
 */
class Content
{
    /**
     *
     * The response body content.
     *
     * @var string
     *
     */
    protected $content = null;

    /**
     *
     * The response headers.
     *
     * @var Headers
     *
     */
    protected $headers;

    /**
     *
     * The content character set.
     *
     * @var string
     *
     */
    protected $charset;

    /**
     *
     * The content type.
     *
     * @var string
     *
     */
    protected $type;

    /**
     *
     * Constructor.
     *
     * @param Headers $headers The response headers.
     *
     */
    public function __construct(Headers $headers)
    {
        $this->headers = $headers;
    }

    /**
     *
     * Sets the content of the response.
     *
     * @param mixed $content The content of the response.
     *
     * @return null
     *
     */
    public function set($content)
    {
        $this->content = $content;
    }

    /**
     *
     * Gets the content of the response.
     *
     * @return mixed The body content of the response.
     *
     */
    public function get()
    {
        return $this->content;
    }

    /**
     *
     * Sets the character set.
     *
     * @param string $charset The character set.
     *
     * @return null
     *
     */
    public function setCharset($charset)
    {
        $this->charset = $charset;
        $this->setContentType();
    }

    /**
     *
     * Sets the content type.
     *
     * @param string $type The content type.
     *
     * @return null
     *
     */
    public function setType($type)
    {
        $this->type = $type;
        $this->setContentType();
    }

    /**
     *
     * Gets the content type.
     *
     * @return string
     *
     */
    public function getType()
    {
        $parts = explode(';', $this->headers->get('Content-Type'));
        $type = array_shift($parts);
        return trim($type);
    }

    /**
     *
     * Gets the character set.
     *
     * @return string
     *
     */
    public function getCharset()
    {
        $parts = explode(';', $this->headers->get('Content-Type'));
        array_shift($parts); // remove $type
        $charset = array_shift($parts);
        $charset = str_replace(' ', '', $charset);
        $charset = str_replace('charset=', '', $charset);
        return $charset;
    }

    /**
     *
     * Sets the content type into the headers.
     *
     * @return null
     *
     */
    protected function setContentType()
    {
        if (! $this->type) {
            $this->headers->set('Content-Type', null);
            return;
        }

        $value = $this->type;
        if ($this->charset) {
            $value .= "; charset={$this->charset}";
        }
        $this->headers->set('Content-Type', $value);
    }

    /**
     *
     * Sets the content encoding.
     *
     * @param string $encoding The content encoding.
     *
     */
    public function setEncoding($encoding)
    {
        $this->headers->set('Content-Encoding', $encoding);
    }

    /**
     *
     * Gets the content encoding.
     *
     * @return string
     *
     */
    public function getEncoding()
    {
        return $this->headers->get('Content-Encoding');
    }

    /**
     *
     * Set the Content-Disposition header.
     *
     * @param string $disposition The disposition, typically 'inline' or
     * 'attachment'.
     *
     * @param string $filename The suggested filename for the content, if any.
     *
     * @return null
     *
     */
    public function setDisposition($disposition, $filename = null)
    {
        if ($disposition && $filename) {
            $filename = basename($filename);
            $disposition .='; filename="'. rawurlencode($filename) . '"';
        }
        $this->headers->set('Content-Disposition', $disposition);
    }
}
