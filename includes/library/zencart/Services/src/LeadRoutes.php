<?php
/**
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: Ian Wilson  Fri Aug 17 17:42:37 2012 +0100 New in v1.5.1 $
 */
namespace ZenCart\Services;

/**
 * Class LeadRoutes
 * @package ZenCart\Admin\Services
 */
class LeadRoutes extends LeadService
{

    /**
     *
     */
    public function updateExecute()
    {
        if (isset($this->listingQuery['language']) && $this->listingQuery['language'] === true) {
            $this->updateExecuteWithLanguage();
        } else {
            $this->updateExecuteStandard();
        }
        if (isset($this->outputLayout['hasImageUpload']) && $this->outputLayout['hasImageUpload'] == true) {
            $this->manageImageUploads();
        }
    }

    /**
     *
     */
    public function updateExecuteStandard()
    {
        $mainTableFkeyField = $this->listingQuery['mainTable']['fkeyFieldLeft'];
        $sql = "UPDATE " . $this->listingQuery['mainTable']['table'] . " SET ";
        foreach ($this->request->all('post') as $key => $value) {
            $realKey = str_replace('entry_field_', '', $key);
            if ($this->checkValidUpdateKey($key, $realKey)) {

                $fieldType = $this->outputLayout['fields'][$realKey]['bindVarsType'];
                $sql .= ':' . str_replace('entry_field_', '', $key) . ': = ';
                $sql = $this->dbConn->bindVars($sql, ':' . $realKey . ':', $realKey, 'noquotestring');
                $sql .= ':' . $realKey . ':, ';
                $sql = $this->dbConn->bindVars($sql, ':' . $realKey . ':', $value, $fieldType);
            }
        }
        $sql = $this->doAutomapSql($sql);
        $sql = substr($sql, 0, strlen($sql) - 2);
        $sql .= " WHERE " . $this->listingQuery['mainTable']['fkeyFieldLeft'] . ' = :' . $this->listingQuery['mainTable']['fkeyFieldLeft'] . ':';
        $fieldType = $this->outputLayout['fields'][$mainTableFkeyField]['bindVarsType'];
        $sql = $this->dbConn->bindVars($sql, ':' . $mainTableFkeyField . ':',
            $this->request->readPost('entry_field_' . $mainTableFkeyField), $fieldType);
        $this->dbConn->execute($sql);
    }

    /**
     *
     */
    public function updateExecuteWithLanguage()
    {
        $languages = $this->getLanguageList();
        $pushedLanguageFields = array();
        $mainTableFkeyField = $this->listingQuery['mainTable']['fkeyFieldLeft'];
        $sql = "UPDATE " . $this->listingQuery['mainTable']['table'] . " SET ";
        $foundKey = false;
        foreach ($this->request->all('post') as $key => $value) {
            $realKey = str_replace('entry_field_', '', $key);
            if ($this->checkValidUpdateKey($key, $realKey)) {
                $pushedLanguageFields[$realKey] = $value;
                if (!isset($this->outputLayout['fields'][$realKey]['language'])) {
                    $foundKey = true;
                    unset($pushedLanguageFields[$realKey]);
                    $fieldType = $this->outputLayout['fields'][$realKey]['bindVarsType'];
                    $sql .= ':' . str_replace('entry_field_', '', $key) . ': = ';
                    $sql = $this->dbConn->bindVars($sql, ':' . $realKey . ':', $realKey, 'noquotestring');
                    $sql .= ':' . $realKey . ':, ';
                    $sql = $this->dbConn->bindVars($sql, ':' . $realKey . ':', $value, $fieldType);
                }
            }
        }
        $sql = $this->doAutomapSql($sql);
        if ($foundKey) {
            $sql = substr($sql, 0, strlen($sql) - 2);
            $fieldType = $this->outputLayout['fields'][$mainTableFkeyField]['bindVarsType'];
            $sql .= " WHERE " . $mainTableFkeyField . ' = :' . $mainTableFkeyField . ':';
            $sql = $this->dbConn->bindVars($sql, ':' . $mainTableFkeyField . ':',
                $this->request->readPost('entry_field_' . $mainTableFkeyField), $fieldType);
            $this->dbConn->execute($sql);
        }
        if (count($pushedLanguageFields) > 0) {
            $this->doPushedLanguageFields($pushedLanguageFields, $languages,
                $this->request->readPost('entry_field_' . $mainTableFkeyField));
        }
    }

    /**
     * @return mixed
     */
    public function insertExecute()
    {
        if (isset($this->listingQuery['language']) && $this->listingQuery['language'] === true) {
            $insertId = $this->insertExecuteWithLanguage();
        } else {
            $insertId = $this->insertExecuteStandard();
        }
        if (isset($this->outputLayout['hasImageUpload']) && $this->outputLayout['hasImageUpload'] == true) {
            $this->manageImageUploads($insertId);
        }

        return $insertId;
    }

    /**
     * @return mixed
     */
    public function insertExecuteStandard()
    {
        $fieldKeyEntries = $fieldValues = '';
        foreach ($this->request->all('post') as $key => $value) {
            $realKey = str_replace('entry_field_', '', $key);
            if ($this->outputLayout['fields'][$realKey]['fieldType'] != 'display' && strpos($key,
                    'entry_field_') === 0 && !isset($this->outputLayout['fields'][$realKey]['parentTable']) && !preg_match('/file_select$/',
                    $key)
            ) {
                $fieldType = $this->outputLayout['fields'][$realKey]['bindVarsType'];
                $fieldKey = $fieldKeyEntry = ':' . $realKey . ':, ';
                $fieldKeyEntry = $this->dbConn->bindVars($fieldKeyEntry, $fieldKeyEntry, $realKey, 'noquotestring');
                $value = $this->dbConn->bindVars($fieldKey, $fieldKey, $value, $fieldType);
                $fieldValues .= $value . ', ';
                $fieldKeyEntries .= $fieldKeyEntry . ", ";
            }
        }
        list($fieldValues, $fieldKeyEntries) = $this->manageAutoMapAdd($fieldValues, $fieldKeyEntries);
        $fieldKeyEntries = substr($fieldKeyEntries, 0, strlen($fieldKeyEntries) - 2);
        $fieldValues = substr($fieldValues, 0, strlen($fieldValues) - 2);
        $sql = "INSERT INTO " . $this->listingQuery['mainTable']['table'] . " (" . $fieldKeyEntries . ") VALUES (" . $fieldValues . ")";
        $this->dbConn->execute($sql);
        $insertId = $this->dbConn->insert_ID();

        return $insertId;

    }


    /**
     * @return mixed
     */
    public function insertExecuteWithLanguage()
    {
        $languages = $this->getLanguageList();
        $fieldKeyEntries = $fieldValues = '';
        $pushedLanguageFields = array();
        $mainTableFkeyField = $this->listingQuery['mainTable']['fkeyFieldLeft'];
        foreach ($this->request->all('post') as $key => $value) {
            $realKey = str_replace('entry_field_', '', $key);
            if ($this->checkValidUpdateKey($key, $realKey)) {
                if (!isset($this->outputLayout['fields'][$realKey]['language'])) {
                    $fieldType = $this->outputLayout['fields'][$realKey]['bindVarsType'];
                    $fieldKey = $fieldKeyEntry = ':' . $realKey . ':, ';
                    $fieldKeyEntry = $this->dbConn->bindVars($fieldKeyEntry, $fieldKeyEntry, $realKey, 'noquotestring');
                    $value = $this->dbConn->bindVars($fieldKey, $fieldKey, $value, $fieldType);
                    $fieldValues .= $value . ', ';
                    $fieldKeyEntries .= $fieldKeyEntry . ", ";
                } else {
                    $pushedLanguageFields[$realKey] = $value;
                }
            }
        }
        list($fieldValues, $fieldKeyEntries) = $this->manageAutoMapAdd($fieldValues, $fieldKeyEntries);
        $fieldKeyEntries = substr($fieldKeyEntries, 0, strlen($fieldKeyEntries) - 2);
        $fieldValues = substr($fieldValues, 0, strlen($fieldValues) - 2);
        if ($fieldKeyEntries == "") {
            $fieldKeyEntries = $mainTableFkeyField;
            $fieldValues = 'null';
        }
        $sql = "INSERT INTO " . $this->listingQuery['mainTable']['table'] . " (" . $fieldKeyEntries . ") VALUES (" . $fieldValues . ")";
        $this->dbConn->execute($sql);
        $insertId = $this->dbConn->insert_ID();
        if (count($pushedLanguageFields) > 0) {
            $this->doPushedLanguageFields($pushedLanguageFields, $languages, $insertId);
        }

        return $insertId;
    }

    /**
     * @return array|bool
     */
    public function deleteExecute()
    {
        $deleteCheck = true;
        if (method_exists($this, 'deleteCheck')) {
            list ($deleteCheck, $errorMessage) = $this->deleteCheck();
        }
        if (!$deleteCheck) {
            $retVal = array('error' => true, 'errorType' => "CUSTOM_ALERT_ERROR", 'errorMessage' => $errorMessage);
            return $retVal;
        }
        $sql = "DELETE FROM " . $this->listingQuery['mainTable']['table'] . " WHERE " . $this->listingQuery['mainTable']['fkeyFieldLeft'] . " = :id:";
        $sql = $this->dbConn->bindVars($sql, ':id:', $this->request->readPost('id'),
            $this->outputLayout['fields'][$this->listingQuery['mainTable']['fkeyFieldLeft']]['bindVarsType']);
        $this->dbConn->execute($sql);
        if (isset($this->outputLayout['language']) && $this->outputLayout['language'] === true) {
            $sql = "DELETE FROM " . $this->outputLayout['languageInfoTable'] . " WHERE " . $this->outputLayout['mainTableFkeyField'] . " = :id:";
            $sql = $this->dbConn->bindVars($sql, ':id:', $this->request->readPost('id'),
                $this->outputLayout['fields'][$this->listingQuery['mainTable']['fkeyFieldLeft']]['bindVarsType']);
            $this->dbConn->execute($sql);
        }

        return true;
    }

    /**
     * @return array|bool
     */
    public function multiDeleteExecute()
    {
        $deleteCheck = true;
        if (method_exists($this, 'multiDeleteCheck')) {
            list ($deleteCheck, $errorMessage) = $this->multiDeleteCheck();
        }
        if (!$deleteCheck) {
            $retVal = array('error' => true, 'errorType' => "CUSTOM_ALERT_ERROR", 'errorMessage' => $errorMessage);
            return $retVal;
        }
        $bindVarType = 'inConstruct' . ucfirst($this->outputLayout['fields'][$this->listingQuery['mainTable']['fkeyFieldLeft']]['bindVarsType']);
        $idList = implode(',', $this->request->readPost('selected'));
        $sql = "DELETE FROM " . $this->listingQuery['mainTable']['table'] . " WHERE " . $this->listingQuery['mainTable']['fkeyFieldLeft'] . " IN (:idList:)";
        $sql = $this->dbConn->bindVars($sql, ':idList:', $idList, $bindVarType);
        $this->dbConn->execute($sql);
        if (isset($this->outputLayout['language']) && $this->outputLayout['language'] === true) {
            $sql = "DELETE FROM " . $this->outputLayout['languageInfoTable'] . " WHERE " . $this->listingQuery['mainTable']['fkeyFieldLeft'] . " IN (:idList:)";
            $sql = $this->dbConn->bindVars($sql, ':idList:', $idList, $bindVarType);
            $this->dbConn->execute($sql);
        }

        return true;
    }

    /**
     * @return array
     */
    public function autoCompleteExecute()
    {
        $dataTable = $this->request->readGet('dataTable');
        $dataSearchField = $this->request->readGet('dataSearchField');
        $dataResponse = $this->request->readGet('dataResponse');
        $valueResponse = $this->request->readGet('valueResponse');
        $search = $this->request->readGet('term');
        $sql = "SELECT :dataResponse:, :valueResponse: FROM :dataTable: WHERE :dataSearchField: LIKE ':search:%'";
        if ($this->request->has('extraWhere') && isset($this->outputLayout['fields'][$this->request->readGet('extraWhere')])) {
            $sql .= ' AND :extraWhereField: = :extraWhereValue:';
            $sql = $this->dbConn->bindVars($sql, ':extraWhereField:', $this->request->readGet('extraWhere'),
                'noquotestring');
            $bindVarsType = $this->outputLayout['fields'][$this->request->readGet('extraWhere')]['bindVarsType'];
            $sql = $this->dbConn->bindVars($sql, ':extraWhereValue:', $this->request->readGet('extraWhereVal'),
                $bindVarsType);
        }
        $sql = $this->dbConn->bindVars($sql, ':dataResponse:', $dataResponse, 'noquotestring');
        $sql = $this->dbConn->bindVars($sql, ':valueResponse:', $valueResponse, 'noquotestring');
        $sql = $this->dbConn->bindVars($sql, ':dataSearchField:', $dataSearchField, 'noquotestring');
        $sql = $this->dbConn->bindVars($sql, ':dataTable:', $dataTable, 'noquotestring');
        $sql = $this->dbConn->bindVars($sql, ':search:', $search, 'noquotestring');
        $dbResults = $this->dbConn->execute($sql);
        $retVal = array('results' => array());
        if ($this->request->has('addAllResponse') && $this->request->readGet('addAllResponse') == true) {
            $retVal['results'][] = array(
                'text' => $this->request->readGet('addAllResponseText'),
                'id' => $this->request->readGet('addAllResponseValue')
            );
        }
        foreach ($dbResults as $result) {
            $retVal['results'][] = array(
                'text' => $result[$valueResponse],
                'id' => $result[$dataResponse]
            );
        }
        return $retVal;
    }
}
