<?php declare(strict_types=1);
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: $
 */

namespace Zencart\ViewBuilders;

use Illuminate\Database\Eloquent\Builder;
use Zencart\Request\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Zencart\Traits\NotifierManager;

abstract class DataTableDataSource
{
    use NotifierManager;

    public function __construct(TableViewDefinition $tableViewDefinition)
    {
        $this->tableDefinition = $tableViewDefinition;
        $this->notify('NOTIFY_DATASOURCE_CONSTRUCTOR_END');
    }

    abstract protected function buildInitialQuery() : Builder;

    public function processRequest(Request $request) : Builder
    {
        $query = $this->buildInitialQuery($request);
        $this->notify('NOTIFY_DATASOURCE_PROCESSREQUEST', [], $query);
        return $query;
    }

    public function processQuery($query) : Paginator
    {
        if ($this->tableDefinition->isPaginated())
        {
            $results = $query->paginate($this->tableDefinition->getParameter('maxRowCount'));
        } else {
            $results = $query->paginate(100000); // icwtodo @todo bit of a hack here to force the return type
        }
        return $results;
    }

    public function getTableDefinition(): TableViewDefinition
    {
        return $this->tableDefinition;
    }

    public function setTableDefinition(TableViewDefinition $tableDefinition)
    {
        $this->tableDefinition = $tableDefinition;
    }
}
