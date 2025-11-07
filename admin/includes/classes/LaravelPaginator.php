<?php
/**
 * LaravelPaginator Class.
 *
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2025 Sep 18 Modified in v2.2.0 $
 */

namespace Zencart\Paginator;

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

/**
 * @since ZC v1.5.8
 */
class LaravelPaginator extends \splitPageResults
{

    protected $cmd;
    protected $page_name;

    /* class constructor */
    function __construct($paginatorResults, $letterGroupColumn = '', $letterGroupLength = 0)
    {
        $this->cmd = isset($_GET['cmd']) ? $_GET['cmd'] : 'home';
        $this->page_name = $paginatorResults->getPageName();
        $this->current_page_number = $paginatorResults->currentPage();
        $this->number_of_rows_per_page = $paginatorResults->perPage();
        $this->number_of_rows = $paginatorResults->total();

        $this->num_pages = (int)ceil($this->number_of_rows / $this->number_of_rows_per_page);
        if ($this->current_page_number > $this->num_pages) {
            $this->current_page_number = $this->num_pages;
        }
        $this->paginateByLetter = true;
        $this->letterGroupLength = 1;
        for ($i = 1; $i <= $this->num_pages; $i++) {
            $this->pages_array[] = ['id' => $i, 'text' => $i];
        }
        if ($this->current_page_number > 1) $this->previousPage = $this->current_page_number - 1;
        if ($this->current_page_number < $this->num_pages) $this->nextPage = $this->current_page_number + 1;
    }
}
