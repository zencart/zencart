<?php declare(strict_types=1);
/**
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2025 Sep 18 Modified in v2.2.0 $
 */

namespace Zencart\ViewBuilders;

use Zencart\Request\Request;
use Zencart\Traits\NotifierManager;

/**
 * @since ZC v1.5.8
 */
abstract class DataTableDataSource
{
    use NotifierManager;
    
    protected $tableDefinition;

    public function __construct(TableViewDefinition $tableViewDefinition)
    {
        $this->tableDefinition = $tableViewDefinition;
        $this->notify('NOTIFY_DATASOURCE_CONSTRUCTOR_END');
    }

    /**
     * @since ZC v1.5.8
     */
    abstract protected function buildInitialQuery();

    /**
     * @since ZC v1.5.8
     */
    public function processRequest(Request $request)
    {
        $query = $this->buildInitialQuery($request);
        $this->notify('NOTIFY_DATASOURCE_PROCESSREQUEST', [], $query);
        return $query;
    }

    /**
     * @since ZC v1.5.8
     */
    public function processQuery($query): NativePaginator
    {
        $maxRows = $this->tableDefinition->isPaginated()
            ? (int)$this->tableDefinition->getParameter('maxRowCount')
            : 100000;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($page < 1) {
            $page = 1;
        }

        if (is_array($query)) {
            $total = count($query);
            $offset = ($page - 1) * $maxRows;
            if ($offset < 0) {
                $offset = 0;
            }
            $slice = array_slice($query, $offset, $maxRows);
            return new NativePaginator($slice, $total, $maxRows, $page, 'page');
        }

        if (is_object($query) && method_exists($query, 'paginate')) {
            /** @var NativePaginator $results */
            $results = $query->paginate($maxRows, '*', 'page', $page);
            return $results;
        }

        return new NativePaginator([], 0, $maxRows, $page, 'page');
    }

    /**
     * @since ZC v1.5.8
     */
    public function getTableDefinition(): TableViewDefinition
    {
        return $this->tableDefinition;
    }

    /**
     * @since ZC v1.5.8
     */
    public function setTableDefinition(TableViewDefinition $tableDefinition)
    {
        $this->tableDefinition = $tableDefinition;
    }
}
