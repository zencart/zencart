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
 * @package ZenCart\Admin\Services
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
    public function updateExecuteStandard()
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
        parent::updateExecuteStandard();
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
        $tableList = array(
            array(
                'table' => TABLE_CATEGORIES_DESCRIPTION,
                'orderBy' => 'categories_id',
                'fields' => array(
                    'categories_id' => 'integer',
                    'categories_name' => 'string',
                    'categories_description' => 'string'
                )
            ),
            array(
                'table' => TABLE_PRODUCTS_DESCRIPTION,
                'orderBy' => 'products_id',
                'fields' => array(
                    'products_id' => 'integer',
                    'products_name' => 'string',
                    'products_description' => 'string',
                    'products_url' => 'string',
                    'products_viewed' => 'integer'
                )
            ),
            array(
                'table' => TABLE_META_TAGS_PRODUCTS_DESCRIPTION,
                'orderBy' => 'products_id',
                'fields' => array(
                    'products_id' => 'integer',
                    'metatags_title' => 'string',
                    'metatags_keywords' => 'string',
                    'metatags_description' => 'string'
                )
            ),
            array(
                'table' => TABLE_METATAGS_CATEGORIES_DESCRIPTION,
                'orderBy' => 'categories_id',
                'fields' => array(
                    'categories_id' => 'integer',
                    'metatags_title' => 'string',
                    'metatags_keywords' => 'string',
                    'metatags_description' => 'string'
                )
            ),
            array(
                'table' => TABLE_PRODUCTS_OPTIONS,
                'orderBy' => 'products_options_id',
                'fields' => array(
                    'products_options_id' => 'integer',
                    'products_options_name' => 'string',
                    'products_options_sort_order' => 'integer',
                    'products_options_type' => 'integer',
                    'products_options_length' => 'integer',
                    'products_options_comment' => 'string',
                    'products_options_size' => 'integer',
                    'products_options_images_per_row' => 'integer',
                    'products_options_images_style' => 'integer',
                    'products_options_rows' => 'integer'
                )
            ),
            array(
                'table' => TABLE_PRODUCTS_OPTIONS_VALUES,
                'orderBy' => 'products_options_values_id',
                'fields' => array(
                    'products_options_values_id' => 'integer',
                    'products_options_values_name' => 'string',
                    'products_options_values_sort_order' => 'integer'
                )
            ),
            array(
                'table' => TABLE_MANUFACTURERS_INFO,
                'languageKeyField' => 'languages_id',
                'orderBy' => 'manufacturers_id',
                'fields' => array(
                    'manufacturers_id' => 'integer',
                    'manufacturers_url' => 'string',
                    'url_clicked' => 'integer',
                    'date_last_click' => 'date'
                )
            ),
            array(
                'table' => TABLE_ORDERS_STATUS,
                'orderBy' => 'orders_status_id',
                'fields' => array(
                    'orders_status_id' => 'integer',
                    'orders_status_name' => 'string'
                )
            ),
            array(
                'table' => TABLE_COUPONS_DESCRIPTION,
                'orderBy' => 'coupon_id',
                'fields' => array(
                    'coupon_id' => 'integer',
                    'coupon_name' => 'string',
                    'coupon_description' => 'string'
                )
            ),
             array(
                 'table' => TABLE_RECORD_ARTISTS_INFO,
                 'languageKeyField' => 'languages_id',
                 'orderBy' => 'artists_id',
                 'fields' => array(
                     'artists_id' => 'integer',
                     'artists_url' => 'string',
                 )
             ),
            array(
                'table' => TABLE_RECORD_COMPANY_INFO,
                'languageKeyField' => 'languages_id',
                'orderBy' => 'record_company_id',
                'fields' => array(
                    'record_company_id' => 'integer',
                    'record_company_url' => 'string',
                )
            ),
       );
        $this->updateLanguageTables($tableList, $insertId);
    }

    /**
     * @param $tableList
     * @param $insertId
     */
    public function updateLanguageTables($tableList, $insertId)
    {
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
}
