<?php
/**
 * Class IndexRoute
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id:$
 */
namespace ZenCart\Services;

use Zencart\Controllers\AbstractController as Controller;
use ZenCart\Request\Request as Request;

/**
 * Class LeadService
 * @package ZenCart\Services
 */
class LeadService extends AbstractService
{

    /**
     * @var
     */
    protected $outputLayout;
    /**
     * @var
     */
    protected $listingQuery;
    /**
     * @var
     */
    protected $queryBuilder;

    /**
     * @param $servicePrefix
     * @param $serviceSuffix
     * @param Controller $listener
     * @param Request $request
     * @param $dbConn
     * @return mixed
     */
    public static function factory($servicePrefix, $serviceSuffix, Controller $listener, Request $request, $dbConn)
    {
        $classname = get_class($listener);
        if (preg_match('@\\\\([\w]+)$@', $classname, $matches)) {
            $classname = $matches[1];
        }
        $testClass = __NAMESPACE__ . '\\' . $servicePrefix . $classname . $serviceSuffix;
        if (class_exists($testClass)) {
            return new $testClass($listener, $request, $dbConn);
        } else {
            $serviceName = __NAMESPACE__ . '\\' . $servicePrefix . $serviceSuffix;

            return new $serviceName($listener, $request, $dbConn);
        }
    }

    /**
     *
     */
    public function updateField()
    {
        $sql = "UPDATE " . $this->listingQuery['mainTable']['table'] . " SET :field: = :value: WHERE :pkey: = :pkeyvalue:";
        $sql = $this->dbConn->bindVars($sql, ':field:', $this->request->readPost('field'), 'noquotestring');
        $sql = $this->dbConn->bindVars($sql, ':value:', $this->request->readPost('value'),
            $this->outputLayout['fields'][$this->request->readPost('field')]['bindVarsType']);
        $sql = $this->dbConn->bindVars($sql, ':pkey:', $this->request->readPost('pkey'), 'noquotestring');
        $sql = $this->dbConn->bindVars($sql, ':pkeyvalue:', $this->request->readPost('pkeyValue'),
            $this->outputLayout['fields'][$this->request->readPost('pkey')]['bindVarsType']);
        $this->dbConn->execute($sql);
    }

    /**
     *
     */
    public function doFilter()
    {
        $this->manageLanguageJoin();
        foreach ($this->request->all('post') as $key => $value) {
            if (strpos($key, 'entry_field_') !== 0 || $value == "") {
                continue;
            }
            $field = str_replace('entry_field_', '', $key);
            $defaultLayout = issetorArray($this->outputLayout['fields'][$field]['layout'], 'common', array());
            $actualLayout = issetorArray($this->outputLayout['fields'][$field]['layout'], 'list', array());
            $layout = array_merge($defaultLayout, $actualLayout);
            $this->manageFilterTypes($layout, $field, $value);
        }
    }

    /**
     *
     */
    public function setEditQueryParts()
    {
        $queryBuilderParts = $this->queryBuilder->getParts();
        $queryBuilderParts['whereClauses'][] = array(
            'table' => $this->listingQuery['mainTable']['table'],
            'field' => $this->listingQuery['mainTable']['fkeyFieldLeft'],
            'value' => ':indexId:',
            'type' => 'AND'
        );
        $queryBuilderParts['bindVars'][] = array(
            ':indexId:',
            $this->request->readGet($this->listingQuery['mainTable']['fkeyFieldLeft']),
            $this->outputLayout['fields'][$this->listingQuery['mainTable']['fkeyFieldLeft']]['bindVarsType']
        );
        $this->queryBuilder->setParts($queryBuilderParts);
    }

    /**
     * @return array
     */
    public function prepareLanguageTplVars()
    {
        $languages = array();
        if (isset($this->listingQuery['language'])) {
            $sql = "SELECT * FROM " . TABLE_LANGUAGES;
            $results = $this->dbConn->execute($sql);
            foreach ($results as $result) {
                $languages[$result['languages_id']] = $result;
            }
        }

        return $languages;
    }

    /**
     * @param null $insertId
     * @return bool
     */
    public function manageImageUploads($insertId = null)
    {
        foreach ($_FILES as $uploadKey => $uploadEntry) {
            $destination = DIR_FS_CATALOG_IMAGES . $this->request->readPost($uploadKey . '_file_select');
            $upload = new \upload($uploadKey);
            $upload->set_destination($destination);
            if (!$upload->parse() || !$upload->save()) {
                return false;
            }
            $realKey = str_replace('entry_field_', '', $uploadKey);
            $sql = "UPDATE " . $this->listingQuery['mainTable']['table'] . " SET :imageField: = :imageValue: WHERE " . $this->listingQuery['mainTable']['fkeyFieldLeft'] . ' = :fkeyId:';
            $sql = $this->dbConn->bindVars($sql, ':imageField:', $realKey, 'noquotestring');
            $fKeyId = isset($insertId) ? $insertId : $this->request->readPost('entry_field_' . $this->listingQuery['mainTable']['fkeyFieldLeft']);
            $sql = $this->dbConn->bindVars($sql, ':fkeyId:', $fKeyId, 'integer');
            $sql = $this->dbConn->bindVars($sql, ':imageValue:',
                $this->request->readPost($uploadKey . '_file_select') . $uploadEntry['name'], 'string');
            $this->dbConn->Execute($sql);
        }

        return true;
    }

    /**
     *
     */
    public function manageLanguageJoin()
    {
        if (!$this->listingQuery['language'] === true) {
            return;
        }
        $singleTable = issetorArray($this->listingQuery, 'singleTable', false);
        $queryBuilderParts = $this->queryBuilder->getParts();
        $queryBuilderParts['whereClauses'][] = array(
            'custom' => 'AND ' . $this->listingQuery['languageKeyField'] . ' = ' . (int)$_SESSION['languages_id']
        );
        if (!$singleTable) {
            $queryBuilderParts = $this->queryBuilder->getParts();
            $queryBuilderParts['joinTables']['languageInfoTable'] = array(
                'table' => $this->listingQuery['languageInfoTable'],
                'alias' => 'lit',
                'type' => 'left',
                'fkeyFieldLeft' => $this->listingQuery['mainTable']['fkeyFieldLeft'],
                'fkeyFieldRight' => $this->listingQuery['mainTable']['fkeyFieldLeft'],
                'addColumns' => true
            );
            $queryBuilderParts['whereClauses'][] = array(
                'custom' => 'AND ' . 'lit.' . $this->listingQuery['languageKeyField'] . ' = ' . (int)$_SESSION['languages_id']
            );
        }
        $this->queryBuilder->setParts($queryBuilderParts);
    }

    /**
     * @param $fieldValues
     * @param $fieldKeyEntries
     * @return array
     */
    protected function manageAutoMapAdd($fieldValues, $fieldKeyEntries)
    {
        if (!isset($this->outputLayout['autoMap']['add'])) {
            return array($fieldValues, $fieldKeyEntries);
        }
        foreach ($this->outputLayout['autoMap']['add'] as $entry) {
            $fieldType = $entry['bindVarsType'];
            $fieldKey = $fieldKeyEntry = ':' . $entry['field'] . ':, ';
            $fieldKeyEntry = $this->dbConn->bindVars($fieldKeyEntry, $fieldKeyEntry, $entry['field'],
                'noquotestring');
            $value = $this->dbConn->bindVars($fieldKey, $fieldKey, $entry['value'], $fieldType);
            $fieldValues .= $value . ', ';
            $fieldKeyEntries .= $fieldKeyEntry . ", ";
        }

        return array($fieldValues, $fieldKeyEntries);
    }

    /**
     * @param $layout
     * @param $field
     * @param $value
     */
    protected function manageFilterTypes($layout, $field, $value)
    {
        $queryBuilderParts = $this->queryBuilder->getParts();
        $table = issetorArray($this->outputLayout['fields'][$field], 'parentTable',
            $this->listingQuery['mainTable']['table']);
        switch ($layout['type']) {
            case 'text':
                if (isset($this->outputLayout['fields'][$field]['language'])) {
                    $table = $this->listingQuery['languageInfoTable'];
                }
                $queryBuilderParts['whereClauses'][] = array(
                    'table' => $table,
                    'field' => $field,
                    'value' => "'%:" . $field . ":%'",
                    'type' => 'AND',
                    'test' => 'LIKE'
                );
                $queryBuilderParts['bindVars'][] = array(
                    ':' . $field . ':',
                    $value,
                    'noquotestring'
                );
                break;
            case 'select':
                $queryBuilderParts['whereClauses'][] = array(
                    'table' => $table,
                    'field' => $field,
                    'value' => ":" . $field . ":",
                    'type' => 'AND',
                    'test' => '='
                );
                $queryBuilderParts['bindVars'][] = array(
                    ':' . $field . ':',
                    $value,
                    $this->outputLayout['fields'][$field]['bindVarsType']
                );
                break;
        }
        $this->queryBuilder->setParts($queryBuilderParts);
    }

    /**
     * @param $key
     * @param $value
     */
    public function updateDefaultConfigurationSetting($key, $value)
    {
        $sql = "UPDATE " . TABLE_CONFIGURATION . "
                        set configuration_value = :value:
                        where configuration_key = :key:";
        $sql = $this->dbConn->bindVars($sql, ':key:', $key, 'string');
        $sql = $this->dbConn->bindVars($sql, ':value:', $value, 'string');
        $this->dbConn->execute($sql);
    }

    /**
     * @return array
     */
    public function getLanguageList()
    {
        $sql = "SELECT * FROM " . TABLE_LANGUAGES;
        $results = $this->dbConn->execute($sql);
        $languages = array();
        foreach ($results as $result) {
            $languages[$result['languages_id']] = $result;
        }
        return $languages;
    }

    /**
     * @param $key
     * @param $realKey
     * @return bool
     */
    public function checkValidUpdateKey($key, $realKey)
    {
        if (strpos($key, 'entry_field_') !== 0 || preg_match('/file_select$/', $key)) {
            return false;
        }
        if ($this->outputLayout['fields'][$realKey]['fieldType'] == 'display' || $key == 'entry_field_' . $this->listingQuery['mainTable']['fkeyFieldLeft']) {
            return false;
        }
        if (isset($this->outputLayout['fields'][$realKey]['parentTable'])) {
            return false;
        }
        if (isset($this->outputLayout['fields'][$realKey]['upload']) && $this->outputLayout['fields'][$realKey]['upload']) {
            return false;
        }

        return true;
    }

    /**
     * @param $mainKey
     * @param $languages
     * @param $resultItems
     */
    public function populateLanguageKeys($mainKey, $languages, $resultItems)
    {
        $tplVars = $this->listener->getTplVars();
        if (!isset($this->outputLayout['fields'][$mainKey]['language'])) {
            return;
        }
        unset($tplVars['leadDefinition']['fields'][$mainKey]['value']);
        foreach ($languages as $language) {
            $sql = "SELECT * FROM " . $this->listingQuery['languageInfoTable'] .
                " WHERE " . $this->listingQuery['mainTable']['fkeyFieldLeft'] . " = " .
                $this->request->readGet($this->listingQuery['mainTable']['fkeyFieldLeft']) .
                " AND " . $this->listingQuery['languageKeyField'] . " = " . $language['languages_id'];
            $lresult = $this->dbConn->execute($sql);
            $tplVars['leadDefinition']['fields'][$mainKey]['value'][$language['languages_id']] = $lresult->fields[$mainKey];
        }
        $this->listener->setTplVars($tplVars);
    }

    /**
     * @return array
     */
    public function getEditHiddenField()
    {
        return array(
            'field' => 'entry_field_' . $this->listingQuery['mainTable']['fkeyFieldLeft'],
            'value' => $this->request->readGet($this->listingQuery['mainTable']['fkeyFieldLeft'])
        );
    }

    /**
     * @param $sql
     * @return string
     */
    public function doAutomapSql($sql)
    {
        if (isset($this->outputLayout['autoMap']['edit'])) {
            foreach ($this->outputLayout['autoMap']['edit'] as $entry) {
                $fieldType = $entry['bindVarsType'];
                $sql .= ':' . $entry['field'] . ': = ';
                $sql = $this->dbConn->bindVars($sql, ':' . $entry['field'] . ':', $entry['field'], 'noquotestring');
                $sql .= ':' . $entry['field'] . ':, ';
                $sql = $this->dbConn->bindVars($sql, ':' . $entry['field'] . ':', $entry['value'], $fieldType);
            }
        }

        return $sql;
    }


    /**
     * @param $pushedLanguageFields
     * @param $languages
     * @param $queryListId
     */
    public function doPushedLanguageFields($pushedLanguageFields, $languages, $queryListId)
    {
        $languageKeyField = issetorArray($this->listingQuery, 'languageKeyField', 'language_id');
        $queryList = array();
        foreach ($languages as $languageKey => $languageValue) {
            $fieldKeyEntries = $fieldKey = $fieldValues = $setValues = '';
            foreach ($pushedLanguageFields as $realKey => $value) {
                $sql = 'INSERT INTO ' . $this->listingQuery['languageInfoTable'] . ' (:insertKeys:) VALUES (:insertValues:) ON DUPLICATE KEY UPDATE :setValues:';
                $fieldType = $this->outputLayout['fields'][$realKey]['bindVarsType'];
                $fieldKey = $fieldKeyEntry = ':' . $realKey . ':, ';
                $fieldKeyEntry = $this->dbConn->bindVars($fieldKeyEntry, $fieldKeyEntry, $realKey, 'noquotestring');
                $bindValue = $this->dbConn->bindVars($fieldKey, $fieldKey, $value[$languageKey], $fieldType);
                $fieldValues .= $bindValue . ', ';
                $fieldKeyEntries .= $fieldKeyEntry . ", ";
                $setValues .= $fieldKeyEntry . ' = ' . $bindValue . ', ';
                $queryList[] = array(
                    'sql' => $sql,
                    'setValues' => $setValues,
                    'fieldKeyEntries' => $fieldKeyEntries,
                    'fieldValues' => $fieldValues,
                    'languageKey' => $languageKey
                );
            }
        }
        foreach ($queryList as $query) {
            $sql = $query['sql'];
            $fieldKeyEntries = $query['fieldKeyEntries'] .= ' ' . $languageKeyField;
            $fieldKeyEntries .= ', ' . $this->listingQuery['mainTable']['fkeyFieldLeft'];
            $fieldValues = $query['fieldValues'] .= $query['languageKey'];
            $fieldValues .= ', ' . $queryListId;
            $setValues = substr($query['setValues'], 0, strlen($query['setValues']) - 2);
            $sql = $this->dbConn->bindVars($sql, ':setValues:', $setValues, 'passthru');
            $sql = $this->dbConn->bindVars($sql, ':insertKeys:', $fieldKeyEntries, 'passthru');
            $sql = $this->dbConn->bindVars($sql, ':insertValues:', $fieldValues, 'passthru');
            $this->dbConn->execute($sql);
        }
    }

    /**
     * @param $listingBox
     */
    public function setListingBox($listingBox)
    {
        $this->outputLayout = $listingBox->getOutputLayout();
        $this->listingQuery = $listingBox->getListingQuery();
    }

    /**
     * @param $queryBuilder
     */
    public function setQueryBuilder($queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }
}
