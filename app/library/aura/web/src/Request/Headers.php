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
 * A read-only representation of HTTP headers.
 *
 * @package Aura.Web
 *
 */
class Headers
{
    /**
     *
     * The headers data.
     *
     * @var array
     *
     */
    protected $data = array();

    /**
     *
     * Constructor.
     *
     * @param array $server $_SERVER values.
     *
     */
    public function __construct(array $server)
    {
        foreach ($server as $label => $value) {
            if (substr($label, 0, 5) == 'HTTP_') {
                // remove the HTTP_* prefix and normalize to lowercase
                $label = strtolower(substr($label, 5));
                // convert underscores to dashes
                $label = str_replace('_', '-', strtolower($label));
                // retain the header label and value
                $this->data[$label] = $value;
            }
        }

        // these two headers are not prefixed with 'HTTP_'
        $rfc3875 = array(
            'CONTENT_TYPE' => 'content-type',
            'CONTENT_LENGTH' => 'content-length',
        );
        foreach ($rfc3875 as $key => $label) {
            if (isset($server[$key])) {
                $this->data[$label] = $server[$key];
            }
        }

        // further sanitize headers to remove HTTP_X_JSON headers
        unset($this->data['HTTP_X_JSON']);
    }

    /**
     *
     * Returns the value of a particular header, or an alternative value if
     * the header is not present.
     *
     * @param string $key The header value to return.
     *
     * @param string $alt The alternative value.
     *
     * @return mixed
     *
     */
    public function get($key = null, $alt = null)
    {
        if (! $key) {
            return $this->data;
        }

        $key = strtolower($key);
        if (array_key_exists($key, $this->data)) {
            return $this->data[$key];
        }

        return $alt;
    }
}
