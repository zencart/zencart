<?php
/**
 * @copyright Copyright 2003-2017 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  New in v1.6.0 $
 */
namespace App\Controllers;

use ZenCart\FormValidation\FormValidation;
use ZenCart\Page\Builder;
use ZenCart\QueryBuilder\QueryBuilder;
use ZenCart\Request\Request as Request;
use ZenCart\Paginator\Paginator as Paginator;
use ZenCart\QueryBuilder\PaginatorBuilder as PaginatorBuilder;
use ZenCart\Services\LeadRoutes as LeadService;
use ZenCart\AdminUser\AdminUser as User;
use Valitron\Validator;

/**
 * Class AbstractLeadController
 * @package App\Controllers
 */
abstract class AbstractLeadController extends AbstractListingController
{
    /**
     * @var string
     */
    protected $classPrefix = 'Lead';
    /**
     * @var string
     */
    protected $mainTemplate = 'adminLead';

    /**
     *
     */
    public function editExecute($formValidation = null)
    {
        $languages = $this->getLanguageListIfMulti();

        $this->tplVarManager->set('legendTitle', TEXT_LEAD_EDIT_ENTRY);
        $this->tplVarManager->set('pageDefinition', $this->pageDefinitionBuilder->getPageDefinition());
        $this->tplVarManager->set('pageDefinition.languages', $languages);
        $this->tplVarManager->set('pageDefinition.contentTemplate', 'partials/lead/addEditContent');
        $this->tplVarManager->set('pageDefinition.action', 'edit');
        $this->tplVarManager->set('pageDefinition.formAction', 'update');
        $this->tplVarManager->set('pageDefinition.cancelButtonAction',  zen_href_link($this->request->readGet('cmd'),
            zen_get_all_get_params(array('action'))));

        $this->setValidationErrors($formValidation, $languages);
        $this->tplVarManager->push('hiddenFields', $this->service->getEditHiddenField());
        $this->setEditQueryparts();
        $resultItems = $this->queryBuilderDefinition->getEditableFields($this->queryBuilder, $this->dbConn,
            new \ZenCart\QueryBuilder\DerivedItemManager);

        if (isset($formValidation)) {
            return;
        }
        foreach ($this->pageDefinitionBuilder->getPageDefinition()['fields'] as $key => $value) {
            $this->tplVarManager->set('pageDefinition.fields.' . $key . '.value' , $resultItems[0][$key]);
            $this->populateLanguageKeysFromDb($key, $languages);
        }
    }


    /**
     * @param $formValidation
     * @param $languages
     */
    protected function setValidationErrors($formValidation, $languages)
    {
        if (!isset($formValidation)) {
            return;
        }

        $errors = $formValidation->getErrors();
        $this->tplVarManager->set('validationErrors', $errors);
        foreach ($this->pageDefinitionBuilder->getPageDefinition()['fields'] as $key => $value) {
            $realKey = 'entry_field_' . $key;
            $this->tplVarManager->set('pageDefinition.fields.' . $key . '.value' , $this->request->readPost($realKey));
            $this->populateLanguageKeysFromPost($key, $languages);
        }
    }

    /**
     *
     */
    public function updateExecute()
    {
        if (!$this->hasPostsCheck()) {
            return;
        }
        $validationEntries = $this->buildValidationEntries();
        $formValidation = new FormValidation();
        $result = $formValidation->validate($validationEntries);
        if (!$result) {
            $this->editExecute($formValidation);
            return;
        }
        $this->service->updateExecute();
        $this->response['redirect'] = zen_href_link($this->request->readGet('cmd'), zen_get_all_get_params(array(
            'cmd',
            'action'
        )));
    }

    /**
     *
     */
    public function addExecute($formValidation = null)
    {
        $outputLayout = $this->queryBuilderDefinition->getOutputLayout();
        $languages = $this->getLanguageListIfMulti();
        $this->tplVarManager->set('legendTitle', TEXT_LEAD_ADD_ENTRY);
        $this->tplVarManager->set('pageDefinition', $this->pageDefinitionBuilder->getPageDefinition());
        $this->tplVarManager->set('pageDefinition.languages', $languages);
        $this->tplVarManager->set('pageDefinition.contentTemplate', 'partials/lead/addEditContent');
        $this->tplVarManager->set('pageDefinition.action', 'add');
        $this->tplVarManager->set('pageDefinition.formAction', 'insert');
        $this->tplVarManager->set('pageDefinition.cancelButtonAction',  zen_href_link($this->request->readGet('cmd'),
            zen_get_all_get_params(array('action'))));
        if (isset($outputLayout['editMap'])) {
            foreach ($outputLayout['editMap'] as $key) {
                $this->resetLanguageKeys($key, $languages);
            }
        }
        $this->setValidationErrors($formValidation, $languages);
    }

    /**
     *
     */
    public function insertExecute()
    {
        if (!$this->hasPostsCheck()) {
            return;
        }
        $validationEntries = $this->buildValidationEntries();
        $formValidation = new FormValidation();
        $result = $formValidation->validate($validationEntries);
        if (!$result) {
            $this->addExecute($formValidation);
            return;
        }
        $this->service->insertExecute();
        $this->response['redirect'] = zen_href_link($this->request->readGet('cmd'));
    }

    /**
     *
     */
    public function deleteExecute()
    {
        $result = $this->service->deleteExecute();
        if ($result === true) {
            $this->filterExecute();
            return;
        }
        $result['header_response_code'] = 403;
        $this->response = $result;
    }

    /**
     *
     */
    public function multiDeleteExecute()
    {
        if (count($this->request->readPost('selected')) === 0) {
            return;
        }
        $result = $this->service->multiDeleteExecute();
        if ($result === true) {
            $this->filterExecute();
            return;
        }
        $result['header_response_code'] = 403;
        $this->response = $result;
    }

    /**
     * @return array
     */
    public function buildValidationEntries()
    {
        $pageDefinition = $this->pageDefinitionBuilder->getPageDefinition();
        $validationEntries = array();
        foreach ($this->request->all('post') as $key => $value ) {
            $realKey = str_replace('entry_field_', '', $key);
            if ($this->service->checkValidUpdateKey($key, $realKey)) {
                $validationEntries[] = array('name' => $realKey, 'value' => $value, 'validations' => $pageDefinition['validations'][$realKey]);
            }
        }
        return $validationEntries;
    }

    /**
     * @return array
     */
    public function getLanguageListIfMulti()
    {
        $languages = array();
        if (!$this->queryBuilderDefinition->getMainModel()->translatable) {
            return $languages;
        }
        $lang = $this->modelFactory->factory('languages');
        $results = $lang->all();
        foreach ($results as $result) {
            $languages[$result['languages_id']] = $result;
        }
        return $languages;
    }

    /**
     * @param $mainKey
     * @param $languages
     */
    public function populateLanguageKeysFromDb($mainKey, $languages)
    {
        if (!isset($this->queryBuilderDefinition->getOutputLayout()['fields'][$mainKey]['language'])) {
            return;
        }
        $this->tplVarManager->forget('pageDefinition.fields.' . $mainKey . '.value');
        $lang = $this->modelFactory->factory($this->queryBuilderDefinition->getListingQuery()['languageInfoTable']);
        foreach ($languages as $language) {
            $zr = $lang
                ->where($this->queryBuilderDefinition->getListingQuery()['mainTable']['fkeyFieldLeft'], '=', $this->request->readGet($this->queryBuilderDefinition->getListingQuery()['mainTable']['fkeyFieldLeft']))
                ->where($this->queryBuilderDefinition->getListingQuery()['languageKeyField'], '=', $language['languages_id'])
            ->first();

            $this->tplVarManager->set('pageDefinition.fields.' . $mainKey . '.value', array($language['languages_id'] => $zr[$mainKey]));
        }
    }
    /**
     * @param $mainKey
     * @param $languages
     */
    public function populateLanguageKeysFromPost($mainKey, $languages)
    {
        if (!isset($this->queryBuilderDefinition->getOutputLayout()['fields'][$mainKey]['language'])) {
            return;
        }
        $this->tplVarManager->forget('pageDefinition.fields.' . $mainKey . '.value');
        foreach ($languages as $language) {
            $languagePost = $this->request->readPost('entry_field_' . $mainKey);
            $languageValue = $languagePost[$language['languages_id']];
            $tplVars['pageDefinition']['fields'][$mainKey]['value'][$language['languages_id']] = $languageValue;
            $this->tplVarManager->set('pageDefinition.fields.' . $mainKey . '.value', array($language['languages_id'] => $languageValue));
        }
    }
    /**
     *
     */
    public function setEditQueryParts()
    {
        $queryBuilderParts = $this->queryBuilder->getParts();
        $queryBuilderParts['whereClauses'][] = array(
            'table' => $this->queryBuilderDefinition->getListingQuery()['mainTable']['table'],
            'field' => $this->queryBuilderDefinition->getListingQuery()['mainTable']['fkeyFieldLeft'],
            'value' => ':indexId:',
            'type' => 'AND'
        );
        $queryBuilderParts['bindVars'][] = array(
            ':indexId:',
            $this->request->readGet($this->queryBuilderDefinition->getListingQuery()['mainTable']['fkeyFieldLeft']),
            $this->queryBuilderDefinition->getOutputLayout()['fields'][$this->queryBuilderDefinition->getListingQuery()['mainTable']['fkeyFieldLeft']]['bindVarsType']
        );
        $this->queryBuilder->setParts($queryBuilderParts);
    }
}
