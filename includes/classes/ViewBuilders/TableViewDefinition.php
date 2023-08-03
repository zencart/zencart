<?php declare(strict_types=1);
/**
 * @copyright Copyright 2003-2023 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2022 Oct 16 Modified in v1.5.8a $
 */

namespace Zencart\ViewBuilders;

class TableViewDefinition
{
    /**
     * $definition is an array holding the table definition
     * @var array
     */
    protected $definition = [];
    public function __construct(array $definition = [])
    {
        $this->definition = $definition;
        $this->setDefaults();
    }

    public function getDefinition() : array
    {
        return $this->definition;
    }

    public function setParameter(string $field, $definition) : TableViewDefinition
    {
        $this->definition[$field] = $definition;
        return $this;
    }

    public function getParameter(string $field)
    {
        $def = $this->definition[$field] ?? null;
        return $def;
    }

    public function addButtonAction($definition) : TableViewDefinition
    {
        $this->definition['buttonActions'][] = $definition;
        return $this;
    }

    public function addRowAction($definition) : TableViewDefinition
    {
        $this->definition['rowActions'][] = $definition;
        return $this;
    }

    public function addColumn(string $field, $definition) : TableViewDefinition
    {
        $this->definition['columns'][$field] = $definition;
        return $this;
    }

    public function addColumnBefore($index, $newKey, $data) : TableViewDefinition
    {
        $columns = $this->definition['columns'];
        $columns = $this->insertBefore($columns, $index, $newKey, $data);
        $this->definition['columns'] = $columns;
        return $this;
    }

    public function addColumnAfter($index, $newKey, $data) : TableViewDefinition
    {
        $columns = $this->definition['columns'];
        $columns = $this->insertAfter($columns, $index, $newKey, $data);
        $this->definition['columns'] = $columns;
        return $this;
    }

    public function getHeaders()
    {
        $headers = collect($this->definition['columns'])->pluck('title');
        return $headers;
    }

    public function isPaginated() : bool
    {
        return ($this->definition['paginated']);
    }

    public function colKeyName() : string
    {
        return $this->definition['colKeyName'];
    }

    public function hasRowActions() : bool
    {
        return (count($this->definition['rowActions']) >0);
    }

    public function getRowActions() : array
    {
        return $this->definition['rowActions'];
    }

    public function getButtonActions() : array
    {
        return $this->definition['buttonActions'];
    }

    protected function setDefaults()
    {
        $this->definition['paginated'] = $this->definition['paginated'] ?? true;
        $this->definition['columns'] = $this->definition['columns'] ?? [];
        $this->definition['buttonActions'] = $this->definition['buttonActions'] ?? [];
        $this->definition['rowActions'] = $this->definition['rowActions'] ?? [];
        $this->definition['maxRowCount'] = $this->definition['maxRowCount'] ?? 10;
        $this->definition['colKeyName'] = $this->definition['colKeyName'] ?? 'colKey';
        $this->definition['pagerVariable'] = $this->definition['pagerVariable'] ?? 'page';
        $this->definition['colKey'] = $this->definition['colKey'] ?? 'id';
    }

    protected function addDefinitions($original, $addition)
    {
        return $original + $addition;
    }

    protected function insertBefore($input, $index, $newKey, $element) {
        if (!array_key_exists($index, $input)) {
            return $input;
        }
        $tmpArray = array();
        foreach ($input as $key => $value) {
            if ($key === $index) {
                $tmpArray[$newKey] = $element;
            }
            $tmpArray[$key] = $value;
        }
        return $tmpArray;
    }

    protected function insertAfter($input, $index, $newKey, $element) {
        if (!array_key_exists($index, $input)) {
            return $input;
        }
        $tmpArray = array();
        foreach ($input as $key => $value) {
            $tmpArray[$key] = $value;
            if ($key === $index) {
                $tmpArray[$newKey] = $element;
            }
        }
        return $tmpArray;
    }
}
