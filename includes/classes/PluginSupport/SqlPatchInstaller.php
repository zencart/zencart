<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Zcwilt 2020 May 20 New in v1.5.7 $
 */

namespace Zencart\PluginSupport;

class SqlPatchInstaller
{
    protected $dbConn;
    protected $errors = [];
    protected $sqlFunctionMap = [
        ['find' => 'DROP TABLE IF EXISTS ', 'length' => 21, 'method' => 'basic', 'tableParamsOffset' => 1],
        ['find' => 'DROP TABLE ', 'length' => 11, 'method' => 'basic', 'tableParamsOffset' => 1],
        ['find' => 'CREATE TABLE IF NOT EXISTS ', 'length' => 27, 'method' => 'basic', 'tableParamsOffset' => 5],
        ['find' => 'CREATE TABLE ', 'length' => 13, 'method' => 'basic', 'tableParamsOffset' => 2],
        ['find' => 'TRUNCATE TABLE ', 'length' => 15, 'method' => 'basic', 'tableParamsOffset' => 2],
        ['find' => 'REPLACE INTO ', 'length' => 13, 'method' => 'replaceinto', 'tableParamsOffset' => 1],
        ['find' => 'INSERT INTO ', 'length' => 12, 'method' => 'basic', 'tableParamsOffset' => 2],
        ['find' => 'INSERT IGNORE INTO ', 'length' => 19, 'method' => 'basic', 'tableParamsOffset' => 3],
        ['find' => 'ALTER TABLE ', 'length' => 12, 'method' => 'basic', 'tableParamsOffset' => 1],
        ['find' => 'RENAME TABLE ', 'length' => 13, 'method' => 'renameTable', 'tableParamsOffset' => 1],
        ['find' => 'UPDATE ', 'length' => 7, 'method' => 'basic', 'tableParamsOffset' => 1],
        ['find' => 'DELETE FROM ', 'length' => 12, 'method' => 'basic', 'tableParamsOffset' => 2],
        ['find' => 'DROP INDEX ', 'length' => 11, 'method' => 'dropIndex', 'tableParamsOffset' => 1],
        ['find' => 'CREATE INDEX ', 'length' => 13, 'method' => 'createIndex', 'tableParamsOffset' => 1],
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
            if ($this->errorContainer->hasErrors()) {
                break;
            }
        }
        return $paramLines;
    }

    public function executePatchSql($paramLines)
    {
        $this->dbConn->dieOnErrors = false;
        foreach ($paramLines as $line) {
            $sql = implode($line, ' ') . ';';
            $this->dbConn->execute($sql);
            if ($this->dbConn->error_number !== 0) {
                $this->errorContainer->addError(0, $this->dbConn->error_text);
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
            $this->errors[] = 'NOT FOUND ' . $line;
            return [];
        }
        $method = 'processLine' . ucfirst($type['method']);
        $newParams = $this->$method($params, $type);
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
        $params[$fromKey + 1] = DB_PREFIX . $params[$fromKey + 1];
        if ($fromKey = array_search('JOIN', $params)) {
            $params[$fromKey + 1] = DB_PREFIX . $params[$fromKey + 1];
        }
        return $params;
    }
}