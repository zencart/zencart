<?php declare(strict_types=1);
/**
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2025 Sep 18 Modified in v2.2.0 $
 */

namespace Zencart\ViewBuilders;

/**
 * @since ZC v1.5.8
 */
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

    /**
     * @since ZC v1.5.8
     */
    public function getDefinition() : array
    {
        return $this->definition;
    }

    /**
     * @since ZC v1.5.8
     */
    public function setParameter(string $field, $definition) : TableViewDefinition
    {
        $this->definition[$field] = $definition;
        return $this;
    }

    /**
     * @since ZC v1.5.8
     */
    public function getParameter(string $field)
    {
        $def = $this->definition[$field] ?? null;
        return $def;
    }

    /**
     * @since ZC v1.5.8
     */
    public function addButtonAction($definition) : TableViewDefinition
    {
        $this->definition['buttonActions'][] = $definition;
        return $this;
    }

    /**
     * @since ZC v1.5.8
     */
    public function addRowAction($definition) : TableViewDefinition
    {
        $this->definition['rowActions'][] = $definition;
        return $this;
    }

    /**
     * @since ZC v1.5.8
     */
    public function addColumn(string $field, $definition) : TableViewDefinition
    {
        $this->definition['columns'][$field] = $definition;
        return $this;
    }

    /**
     * @since ZC v1.5.8
     */
    public function addColumnBefore($index, $newKey, $data) : TableViewDefinition
    {
        $columns = $this->definition['columns'];
        $columns = $this->insertBefore($columns, $index, $newKey, $data);
        $this->definition['columns'] = $columns;
        return $this;
    }

    /**
     * @since ZC v1.5.8
     */
    public function addColumnAfter($index, $newKey, $data) : TableViewDefinition
    {
        $columns = $this->definition['columns'];
        $columns = $this->insertAfter($columns, $index, $newKey, $data);
        $this->definition['columns'] = $columns;
        return $this;
    }

    /**
     * @since ZC v1.5.8
     */
    public function getHeaders()
    {
        $headers = [];
        foreach ($this->definition['columns'] as $column) {
            $headers[] = $column['title'] ?? '';
        }
        return $headers;
    }

    /**
     * @since ZC v1.5.8
     */
    public function isPaginated() : bool
    {
        return ($this->definition['paginated']);
    }

    /**
     * @since ZC v1.5.8
     */
    public function colKeyName() : string
    {
        return $this->definition['colKeyName'];
    }

    /**
     * @since ZC v1.5.8
     */
    public function hasRowActions() : bool
    {
        return (count($this->definition['rowActions']) >0);
    }

    /**
     * @since ZC v1.5.8
     */
    public function getRowActions() : array
    {
        return $this->definition['rowActions'];
    }

    /**
     * @since ZC v1.5.8
     */
    public function getButtonActions() : array
    {
        return $this->definition['buttonActions'];
    }

    /**
     * @since ZC v1.5.8
     */
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

    /**
     * @since ZC v1.5.8
     */
    protected function addDefinitions($original, $addition)
    {
        return $original + $addition;
    }

    /**
     * @since ZC v1.5.8
     */
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

    /**
     * @since ZC v1.5.8
     */
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
