<?php
/**
 * breadcrumb Class.
 *
 * @package classes
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: breadcrumb.php 3147 2006-03-10 00:43:57Z drbyte $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

/**
 * The following switch simply checks to see if the setting is already defined, and if not, sets it to true
 * If you desire to have the older behaviour of having all product and category items in the breadcrumb be shown as links
 * then you should add a define() for this item in the extra_datafiles folder and set it to 'false' instead of 'true':
 */
if (!defined('DISABLE_BREADCRUMB_LINKS_ON_LAST_ITEM')) define('DISABLE_BREADCRUMB_LINKS_ON_LAST_ITEM','true');

/**
 * breadcrumb Class.
 * Class to handle page breadcrumbs
 *
 * @package classes
 */
class breadcrumb extends base {
  var $_trail;

  function breadcrumb() {
    $this->reset();
  }

  function reset() {
    $this->_trail = array();
  }

  function add($title, $link = '') {
    $this->_trail[] = array('title' => $title, 'link' => $link);
  }

  function trail($separator = '&nbsp;&nbsp;') {
    global $request_type;
    $trail_string = '';

    for ($i=0, $n=sizeof($this->_trail); $i<$n; $i++) {
//    echo 'breadcrumb ' . $i . ' of ' . $n . ': ' . $this->_trail[$i]['title'] . '<br />';
      $skip_link = false;
      if ($i==($n-1) && DISABLE_BREADCRUMB_LINKS_ON_LAST_ITEM =='true') {
        $skip_link = true;
      }
      if (isset($this->_trail[$i]['link']) && zen_not_null($this->_trail[$i]['link']) && !$skip_link ) {
        // this line simply sets the "Home" link to be the domain/url, not main_page=index?blahblah:
        if ($this->_trail[$i]['title'] == HEADER_TITLE_CATALOG) {
          $trail_string .= '  <a ' . ($i==($n-1) ? 'itemprop="url" ' : '') . 'href="' . ($request_type != 'SSL' ? HTTP_SERVER . DIR_WS_CATALOG : HTTPS_SERVER . DIR_WS_HTTPS_CATALOG) . '">' . $this->_trail[$i]['title'] . '</a>';
        } else {
          $trail_string .= '  <a ' . ($i==($n-1) ? 'itemprop="url" ' : '') . 'href="' . $this->_trail[$i]['link'] . '">' . '<span itemprop="title">' . $this->_trail[$i]['title'] . '</span>' . '</a>';
        }
      } else {
        if ($i==($n-1)) $trail_string .= '  <link itemprop="url" href="' . $this->_trail[$i]['link'] . '" />';
        $trail_string .= $this->_trail[$i]['title'];
      }

      if (($i+1) < $n) $trail_string .= $separator;
      $trail_string .= "\n";
    }

    return $trail_string;
  }

  function last() {
    $trail_size = sizeof($this->_trail);
    return $this->_trail[$trail_size-1]['title'];
  }
}
