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
 * A representation of $_FILES.
 *
 * @package Aura.Web
 *
 */
class Files extends Values
{
    /**
     *
     * Constructor.
     *
     * @param array $input The original $_FILES value.
     *
     */
    public function __construct(array $input = array())
    {
        $files = array();
        $this->init($input, $files);
        return parent::__construct($files);
    }

    /**
     *
     * Recursively initialize the data structure to be more like $_POST.
     *
     * @param array $src The source array.
     *
     * @param array $tgt Where we will store the restructured data.
     *
     * @return null
     *
     */
    protected function init($src, &$tgt)
    {
        // an array with these keys is a "target" for us (pre-sorted)
        $tgtkeys = array('error', 'name', 'size', 'tmp_name', 'type');

        // the keys of the source array (sorted so that comparisons work
        // regardless of original order)
        $srckeys = array_keys((array) $src);
        sort($srckeys);

        // is the source array a target?
        if ($srckeys == $tgtkeys) {
            // get error, name, size, etc
            foreach ($srckeys as $key) {
                if (is_array($src[$key])) {
                    // multiple file field names for each error, name, size, etc.
                    foreach ((array) $src[$key] as $field => $value) {
                        $tgt[$field][$key] = $value;
                    }
                } else {
                    // the key itself is error, name, size, etc., and the
                    // target is already the file field name
                    $tgt[$key] = $src[$key];
                }
            }
        } else {
            // not a target, create sub-elements and init them too
            foreach ($src as $key => $val) {
                $tgt[$key] = array();
                $this->init($val, $tgt[$key]);
            }
        }
    }
}
