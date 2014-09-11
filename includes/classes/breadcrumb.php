<?php
/**
 * Breadcrumb Class
 *
 * @package   classes
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license   http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

/**
 * Generates breadcrumb links
 *
 * @package classes
 */
class Breadcrumb implements Countable
{
    /**
     * @var string
     */
    const DEFAULT_SEPARATOR = '&nbsp;&nbsp;';

    /**
     * @var array title: string => link: string
     */
    private $links = array();

    /**
     * @param array $links title: string => link: string
     */
    public function __construct(array $links = array())
    {
        foreach ($links as $title => $link) {
            $this->add($title, $link);
        }
    }

    /**
     * Add a breadcrumb link
     *
     * @param  string $title the link title
     * @param  string $link  the link href
     * @throws InvalidArgumentException when either title or link are empty
     */
    public function add($title, $link = '#')
    {
        if (empty($title) || empty($link)) {
            throw new InvalidArgumentException("Both title and link must not be empty.");
        }
        $this->links[(string) $title] = (string) $link;
    }

    /**
     * Generate an html breadcrumb string
     *
     * @param  string $separator the string that separates each crumb
     * @return string
     */
    public function trail($separator = self::DEFAULT_SEPARATOR)
    {
        $trail     = '<nav class="breadcrumb">';
        $titles    = array_keys($this->links);
        $lastTitle = end($titles);

        foreach ($this->links as $title => $link) {
            $trail .= '<span itemscope itemtype="http://data-vocabulary.org/Breadcrumb" class="crumb">';
            $href   = $this->buildHref($title, $link);
            $label  = '<span itemprop="title" class="title">' . $title . '</span>';

            $trail .= ($title == $lastTitle)
                ? '<link itemprop="url" href="' . $href . '" class="link" />' . $label
                : '<a itemprop="url" href="' . $href . '" class="link">' . $label . '</a>';

            $trail .= '</span>' . $separator;
        }

        return rtrim($trail, $separator) . "</nav>\n";
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->links);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->trail();
    }

    /**
     * If the crumb title matches the catalog header title, use the appropriate site url
     *
     * @param  string $title the crumb title
     * @param  string $link  the default href
     * @return string
     */
    private function buildHref($title, $link)
    {
        global $request_type;
        if ($title == HEADER_TITLE_CATALOG) {
            return ($request_type == 'SSL') ? HTTPS_SERVER . DIR_WS_HTTPS_CATALOG : HTTP_SERVER . DIR_WS_CATALOG;
        }
        return $link;
    }
}
