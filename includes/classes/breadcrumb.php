<?php
/**
 * breadcrumb Class.
 *
 * @package   classes
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license   http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

/**
 * The following switch simply checks to see if the setting is already defined, and if not, sets it to true
 * If you desire to have the older behaviour of having all product and category items in the breadcrumb be shown as links
 * then you should add a define() for this item in the extra_datafiles folder and set it to 'false' instead of 'true':
 */
if (!defined('DISABLE_BREADCRUMB_LINKS_ON_LAST_ITEM')) {
  define('DISABLE_BREADCRUMB_LINKS_ON_LAST_ITEM', true);
}

/**
 * Generates breadcrumb links
 *
 * @package classes
 */
class breadcrumb implements Countable {
  /** @var string */
  const DEFAULT_SEPERATOR = '&nbsp;&nbsp;';
  
  /** @var array */
  private $links = array();
  
  /**
   * @param array $links           title => link
   * @param bool  $disableLastLink don't render the last item as an anchor link
   */
  public function __construct(array $links = array()) {
    foreach ($links as $title => $link) {
      $this->add($title, $link);
    }
  }
  
  /**
   * Clear the links array
   */
  public function reset() {
    $this->links = array();
  }
  
  /**
   * Add a breadcrumb link
   * 
   * @param  string $title the link title
   * @param  string $link  the link href
   * @throws InvalidArgumentException when either title or link are empty
   */
  public function add($title, $link) {
    if (empty($link) || empty($title)) {
      throw new InvalidArgumentException("Both title and link must not be empty.");
    }
    $this->links[] = array('title' => (string) $title, 'link' => (string) $link);
  }
  
  /**
   * Generate an html breadcrumb string
   * 
   * @param  string $seperator       the string that seperates each crumb
   * @param  bool   $disableLastLink don't render an html anchor tag for the last crumb
   * @return string
   */
  public function trail($seperator = self::DEFAULT_SEPERATOR, $disableLastLink = DISABLE_BREADCRUMB_LINKS_ON_LAST_ITEM) {
    $lastLinkDisabled = ($disableLastLink == 'true' || ($disableLastLink != 'false' && $disableLastLink));
    $trail            = '<nav class="breadcrumb">';
    $lastTitle        = $this->links[$this->count() - 1]['title'];

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
          $trail_string .= '  <a href="' . ($request_type != 'SSL' ? HTTP_SERVER . DIR_WS_CATALOG : HTTPS_SERVER . DIR_WS_HTTPS_CATALOG) . '">' . $this->_trail[$i]['title'] . '</a>';
        } else {
          $trail_string .= '  <span itemscope itemtype="http://data-vocabulary.org/Breadcrumb"><a itemprop="url" href="' . 
                           $this->_trail[$i]['link'] . '"><span itemprop="title">' . 
                           $this->_trail[$i]['title'] . '</span></a></span>';
        }
      } else {
        if ($i==($n-1)) $trail_string .= '  <span itemscope itemtype="http://data-vocabulary.org/Breadcrumb"><link itemprop="url" href="' . 
                                         $this->_trail[$i]['link'] . '" />' . 
                                         $this->_trail[$i]['title'] . '</span>';
      }

      if (($i+1) < $n) $trail_string .= $separator;
      $trail_string .= "\n";
    }

    return trim($trail, $seperator) . "</nav>\n";
  }
  
  /** @return int */
  public function count() {
    return count($this->links);
  }
  
  /** @return string */
  public function __toString() {
    return $this->trail();
  }
  
  /**
   * If the crumb title matches the catalog header title, use the appropriate site url
   * 
   * @param  array  $crumb
   * @return string
   */
  private function buildHref(array $crumb) {
    global $request_type;
    if ($crumb['title'] == HEADER_TITLE_CATALOG) {
      return ($request_type == 'SSL') ? HTTPS_SERVER . DIR_WS_HTTPS_CATALOG : HTTP_SERVER . DIR_WS_CATALOG;
    }
    return $crumb['link'];
  }
}
