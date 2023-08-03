<?php
/**
 * @copyright Copyright 2003-2023 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2022 Oct 16 Modified in v1.5.8a $
 */

namespace Zencart\PluginSupport;

class SqlPatchInstaller
{

    /**
     * $dbConn is a database object 
     * @var object
     */
    protected $dbConn;
    /**
     * $errorContainer is a PluginErrorContainer object
     * @var object
     */
    protected $errorContainer;
    /**
     * $sqlFunctionMap is a list of acceptable SQL 
     * @var array
     */
    protected $sqlFunctionMap = [
        ['find' => 'DROP TABLE IF EXISTS ', 'length' => 21, 'method' => 'basic', 'tableParamsOffset' => 4],
        ['find' => 'DROP TABLE ', 'length' => 11, 'method' => 'basic', 'tableParamsOffset' => 2],
        ['find' => 'CREATE TABLE IF NOT EXISTS ', 'length' => 27, 'method' => 'basic', 'tableParamsOffset' => 5],
        ['find' => 'CREATE TABLE ', 'length' => 13, 'method' => 'basic', 'tableParamsOffset' => 2],
        ['find' => 'TRUNCATE TABLE ', 'length' => 15, 'method' => 'basic', 'tableParamsOffset' => 2],
        ['find' => 'REPLACE INTO ', 'length' => 13, 'method' => 'basic', 'tableParamsOffset' => 2],
        ['find' => 'INSERT INTO ', 'length' => 12, 'method' => 'basic', 'tableParamsOffset' => 2],
        ['find' => 'INSERT IGNORE INTO ', 'length' => 19, 'method' => 'basic', 'tableParamsOffset' => 3],
        ['find' => 'ALTER TABLE ', 'length' => 12, 'method' => 'basic', 'tableParamsOffset' => 2],
        ['find' => 'RENAME TABLE ', 'length' => 13, 'method' => 'renameTable', 'tableParamsOffset' => 2],
        ['find' => 'UPDATE ', 'length' => 7, 'method' => 'basic', 'tableParamsOffset' => 1],
        ['find' => 'DELETE FROM ', 'length' => 12, 'method' => 'basic', 'tableParamsOffset' => 2],
        ['find' => 'DROP INDEX ', 'length' => 11, 'method' => 'index', 'tableParamsOffset' => 2],
        ['find' => 'CREATE INDEX ', 'length' => 13, 'method' => 'index', 'tableParamsOffset' => 2],
        ['find' => 'SELECT ', 'length' => 7, 'method' => 'select', 'tableParamsOffset' => 1],
    ];


    public function __construct($dbConn, $errorContainer)
    {
        $this->dbConn = $dbConn;
        $this->errorContainer = $errorContainer;
    }

    public function parse($lines)
    {
        $builtLines = $this->getFullLines($lines);
        $paramLines = [];
        foreach ($builtLines as $line) {
            $paramLines[] = $this->processLine($line);
        }
        return $paramLines;
    }

    public function executePatchSql($paramLines)
    {
        $this->dbConn->dieOnErrors = false;
        foreach ($paramLines as $line) {
            $sql = implode(' ', $line) . ';';
            $this->dbConn->execute($sql);
            if ($this->dbConn->error_number !== 0) {
                $this->errorContainer->addError(0, ERROR_SQL_PATCH . $this->dbConn->error_text . '<br>' . $sql, true);
                break;
            }
        }
        $this->dbConn->dieOnErrors = true;
    }

    protected function getFullLines($lines)
    {
        $fullLine = '';
        $builtLines = [];
        foreach ($lines as $line) {
            $line = str_replace('`', '', trim($line));
            $fullLine .= ' ' . $line;
            if (substr($line, -1) == ';') {
                $builtLines[] = ltrim($fullLine);
                $fullLine = '';
            }
        }
        return $builtLines;
    }

    protected function processLine($line)
    {
        $params = explode(" ", (substr($line, -1) == ';') ? substr($line, 0, strlen($line) - 1) : $line);
        $type = $this->findSqlLineType(strtoupper($line));

        if (count($type) === 0) {
             $this->errorContainer->addError(0, ERROR_NOT_FOUND_IN_SQL_FUNCTIONS_MAP. $line, true);
            return [];
        }
        $method = 'processLine' . ucfirst($type['method']);
        $newParams = $this->$method($params, $type);
        /*
         * if empty the line could not be correctly parsed
         */
        if (empty($newParams)) {
             $this->errorContainer->addError(0, ERROR_INVALID_SYNTAX . $line, true);            
        }
        return $newParams;
    }

    protected function findSqlLineType($line)
    {
        $result = [];
        foreach ($this->sqlFunctionMap as $entry) {
            if (substr($line, 0, $entry['length']) != $entry['find']) {
                continue;
            }
            $result = $entry;
            break;
        }
        return $result;
    }

    protected function processLineBasic($params, $typeEntry)
    {
        $params[$typeEntry['tableParamsOffset']] = DB_PREFIX . $params[$typeEntry['tableParamsOffset']];
        return $params;
    }

    protected function processLineSelect($params, $typeEntry)
    {
        $fromKey = array_search('FROM', $params);
        if ($fromKey === false) {
            return [];
        }
        $params[$fromKey + 1] = DB_PREFIX . $params[$fromKey + 1];
        $joinKeys = array_keys($params, 'JOIN');
        if (!empty($joinKeys)) {
            foreach ($joinKeys as $fromKey) {
                $params[$fromKey + 1] = DB_PREFIX . $params[$fromKey + 1];
            }
        }
        return $params;
    }
    
    protected function processLineIndex($params, $typeEntry)
    {
        $fromKey = array_search('ON', $params);
        if ($fromKey === false) {
            return [];
        }
        $params[$fromKey + 1] = DB_PREFIX . $params[$fromKey + 1];
        return $params;
    }
    
    protected function processLineRenameTable($params, $typeEntry)
    {
        $params[$typeEntry['tableParamsOffset']] = DB_PREFIX . $params[$typeEntry['tableParamsOffset']];
        $params[$typeEntry['tableParamsOffset'] + 2] = DB_PREFIX . $params[$typeEntry['tableParamsOffset'] + 2];
        return $params;
    }
}
