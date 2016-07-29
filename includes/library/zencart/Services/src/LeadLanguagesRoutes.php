<?php
/**
 * Class LeadLanguagesRoutes
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: Ian Wilson  Fri Aug 17 17:42:37 2012 +0100 New in v1.5.1 $
 */
namespace ZenCart\Services;

/**
 * Class LeadLanguagesRoutes
 * @package ZenCart\Services
 */
class LeadLanguagesRoutes extends LeadRoutes
{
    /**
     * @return array
     */
    public function deleteCheck()
    {
        $retVal = array(true, '');
        $sql = "SELECT code FROM " . TABLE_LANGUAGES . " WHERE languages_id = :id:";
        $sql = $this->dbConn->bindVars($sql, ':id:', $this->request->readPost('id'), 'integer');
        $result = $this->dbConn->execute($sql);
        if ($result->fields ['code'] == DEFAULT_LANGUAGE) {
            $retVal = array(false, ERROR_REMOVE_DEFAULT_LANGUAGE);
        }
        return $retVal;
    }

    /**
     * @return array
     */
    public function multiDeleteCheck()
    {
        $retVal = array(true, '');
        $sql = "SELECT languages_id FROM " . TABLE_LANGUAGES . " WHERE code = :code:";
        $sql = $this->dbConn->bindVars($sql, ':code:', DEFAULT_LANGUAGE, 'string');
        $result = $this->dbConn->execute($sql);
        if (in_array($result->fields ['languages_id'], $this->request->readPost('selected'))) {
            $retVal = array(false, ERROR_REMOVE_DEFAULT_LANGUAGE_MULTI);
        }
        return $retVal;
    }

    /**
     *
     */
    public function doUpdateExecute()
    {
        $sql = "SELECT languages_id, code FROM " . TABLE_LANGUAGES . " WHERE languages_id = :id:";
        $sql = $this->dbConn->bindVars($sql, ':id:', $this->request->readPost('entry_field_languages_id'), 'integer');
        $result = $this->dbConn->execute($sql);
        $changingDefaultLang = (DEFAULT_LANGUAGE != $result->fields ['code']) ? true : false;
        $defaultNeedsUpdate = (DEFAULT_LANGUAGE != $this->request->readPost('entry_field_code')) ? false : true;
        $defaultLangChange = ($defaultNeedsUpdate && $changingDefaultLang) ? true : false;
        if ($_SESSION ['languages_code'] != $result->fields ['code'] && $_SESSION ['languages_code'] != $this->request->readPost('entry_field_code')) {
            $_SESSION ['languages_code'] = $this->request->readPost('entry_field_code');
        }
        if ($_SESSION ['language'] != $result->fields ['directory'] && $_SESSION ['language'] != $this->request->readPost('entry_field_directory')) {
            $_SESSION ['language'] = $this->request->readPost('entry_field_directory');
        }
        if ($_SESSION ['languages_id'] != $result->fields ['languages_id'] && $_SESSION ['languages_id'] != $this->request->readPost('entry_field_languages_id')) {
            $_SESSION ['languages_id'] = $this->request->readPost('entry_field_languages_id');
        }
        if ($this->request->has('entry_field_setAsDefault', 'post') || $defaultLangChange === true) {
            $this->updateDefaultConfigurationSetting('DEFAULT_LANGUAGE', $this->request->readPost('entry_field_code'));
        }
        parent::doUpdateExecute();
    }

    /**
     *
     */
    public function insertExecute()
    {
        $insertId = parent::insertExecute();
        $this->postProcessInsert($insertId);
    }

    /**
     *
     */
    public function postProcessInsert($insertId)
    {
        if ($this->request->has('entry_field_setAsDefault', 'post') && $this->request->has('entry_field_code', 'post')) {
            $sql = "UPDATE " . TABLE_CONFIGURATION . "
                        set configuration_value = :code:
                        where configuration_key = 'DEFAULT_LANGUAGE'";
            $sql = $this->dbConn->bindVars($sql, ':code:', $this->request->readPost('entry_field_code'), 'string');
            $this->dbConn->execute($sql);
        }
        $this->updateLanguageTables($insertId);
    }

    /**
     * @param $insertId]
     */
    public function updateLanguageTables($insertId)
    {
        $tableList = $this->listener->getTableList();
        if (count($tableList) == 0) {
            return;
        }
        foreach ($tableList as $tableEntry) {
            $languageKeyField = issetorArray($tableEntry, 'languageKeyField', 'language_id');
            $sql = " INSERT IGNORE INTO :table: (";
            $sql = $this->dbConn->bindVars($sql, ':table:', $tableEntry ['table'], 'noquotestring');
            $sql .= $languageKeyField. ", ";
            $fieldNames = "";
            foreach ($tableEntry[fields] as $fieldName => $fieldType) {
                $fieldNames .= $fieldName . ", ";
            }
            $fieldNames = substr($fieldNames, 0, strLen($fieldNames) - 2);
            $sql .=  $fieldNames . ") ";
            $sql .= "SELECT " . $insertId . " AS " . $languageKeyField ;
            $sql .= ", " . $fieldNames;
            $sql .= " FROM :table:  WHERE " . $languageKeyField . " = " . $_SESSION ['languages_id'];
            $sql = $this->dbConn->bindVars($sql, ':table:', $tableEntry ['table'], 'noquotestring');
            $sql .= " ORDER BY " . $tableEntry['orderBy'] . ", " . $languageKeyField;
            $this->dbConn->execute($sql);
        }
    }

    /**
     *
     */
    protected function deleteTableEntry()
    {
        $tableList = $this->listener->getTableList();
        if (count($tableList) == 0) {
            return;
        }
        foreach ($tableList as $tableEntry) {
            $languageKeyField = issetorArray($tableEntry, 'languageKeyField', 'language_id');
            $sql = "DELETE FROM :tableName: WHERE :languageField: = :id:" ;
            $sql = $this->dbConn->bindVars($sql, ':tableName:', $tableEntry ['table'], 'noquotestring');
            $sql = $this->dbConn->bindVars($sql, ':languageField:', $languageKeyField, 'noquotestring');
            $sql = $this->dbConn->bindVars($sql, ':id:', $this->request->readPost('id'), 'integer');
            $this->dbConn->execute($sql);
        }
        $sql = "DELETE FROM " . TABLE_LANGUAGES . " WHERE languages_id = :id:";
        $sql = $this->dbConn->bindVars($sql, ':id:', $this->request->readPost('id'), 'integer');
        $this->dbConn->execute($sql);
    }
}
