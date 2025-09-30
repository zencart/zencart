<?php
/**
 * breadcrumb Class.
 *
 * @copyright Copyright 2003-2023 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2023 Mar 08 Modified in v1.5.8a $
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

/**
 * The following switch simply checks to see if the setting is already defined, and if not, sets it to true
 * If you desire to have the older behaviour of having all product and category items in the breadcrumb be shown as links
 * then you should add a define() for this item in the extra_datafiles folder and set it to 'false' instead of 'true':
 */
if (!defined('DISABLE_BREADCRUMB_LINKS_ON_LAST_ITEM')) define('DISABLE_BREADCRUMB_LINKS_ON_LAST_ITEM', 'true');

/**
 * Handle page breadcrumbs
 * @since ZC v1.0.3
 */
class breadcrumb extends base
{
    protected $_trail = [];

    function __construct()
    {
        $this->reset();
    }

    /**
     * @since ZC v1.0.3
     */
    function reset()
    {
        $this->_trail = [];
    }

    /**
     * @since ZC v1.0.3
     */
    function add($title, $link = '')
    {
        $this->_trail[] = ['title' => $title, 'link' => $link];
    }

    /**
     * @since ZC v1.0.3
     */
    function trail($separator = '&nbsp;&nbsp;', $prefix = '', $suffix = '')
    {
        $trail_string = '';

        for ($i = 0, $n = count($this->_trail); $i < $n; $i++) {
        // echo 'breadcrumb ' . $i . ' of ' . $n . ': ' . $this->_trail[$i]['title'] . '<br>';
            $skip_link = false;
            if ($i == ($n - 1) && DISABLE_BREADCRUMB_LINKS_ON_LAST_ITEM == 'true') {
                $skip_link = true;
            }
            if (!empty($this->_trail[$i]['link']) && !$skip_link) {
                // this line simply sets the "Home" link to be the domain/url, not main_page=index?blahblah:
                if ($this->_trail[$i]['title'] == HEADER_TITLE_CATALOG) {
                    $trail_string .= '  ' . $prefix . '<a href="' . zen_href_link('/', '', 'SSL', false, true, true) . '">' . $this->_trail[$i]['title'] . '</a>' . $suffix;
                } else {
                    $trail_string .= '  ' . $prefix . '<a href="' . $this->_trail[$i]['link'] . '">' . $this->_trail[$i]['title'] . '</a>' . $suffix;
                }
            } else {
                if (isset($this->_trail[$i]['title'])) {
                    $trail_string .= $prefix . $this->_trail[$i]['title'] . $suffix;
                }
            }

            if (($i + 1) < $n) $trail_string .= $separator;
            $trail_string .= "\n";
        }

        return $trail_string;
    }

    /**
     * @since ZC v1.0.3
     */
    function last()
    {
        $trail_size = count($this->_trail);
        return $this->_trail[$trail_size - 1]['title'];
    }

    /**
     * @since ZC v1.5.7c
     */
    function removeLast()
    {
        $trail_size = count($this->_trail);
        unset($this->_trail[$trail_size - 1]);
    }

    /**
     * @since ZC v1.5.7c
     */
    function replaceLast($title = null, $link = null)
    {
        if ($title === null && $link === null) return $this->removeLast();

        $trail_size = count($this->_trail);
        if ($title !== null) {
            $this->_trail[$trail_size - 1]['title'] = $title;
        }
        if ($link !== null) {
            $this->_trail[$trail_size - 1]['link'] = $link;
        }
    }

    /**
     * @since ZC v1.5.7
     */
    function isEmpty()
    {
        return empty($this->_trail);
    }

    /**
     * @since ZC v1.5.7
     */
    function count()
    {
        return count($this->_trail);
    }
    /**
     * @since ZC v1.5.8a
     */
    function getTrail()
    {
       return $this->_trail;
    }
}
