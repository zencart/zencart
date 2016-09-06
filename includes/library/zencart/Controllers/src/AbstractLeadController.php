<?php
/**
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  New in v1.6.0 $
 */
namespace ZenCart\Controllers;

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
 * @package ZenCart\Controllers
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
    protected $mainTemplate = 'tplAdminLead.php';

    /**
     *
     */
    public function editExecute($formValidation = null)
    {
        $languages = $this->service->prepareLanguageTplVars();
        $this->view->getTplVarManager()->set('legendTitle', TEXT_LEAD_EDIT_ENTRY);
        $this->tplVars['pageDefinition'] = $this->pageDefinitionBuilder->getPageDefinition();
        $this->tplVars['pageDefinition']['languages'] = $languages;
        $this->tplVars['pageDefinition']['contentTemplate'] = 'tplAdminLeadAddEditContent.php';
        $this->tplVars['pageDefinition']['action'] = 'edit';
        $this->tplVars['pageDefinition']['formAction'] = 'update';
        $this->tplVars['pageDefinition']['cancelButtonAction'] = zen_href_link($this->request->readGet('cmd'),
            zen_get_all_get_params(array('action')));
        $this->setValidationErrors($formValidation, $languages);
        $this->view->getTplVarManager()->push('hiddenFields', $this->service->getEditHiddenField());

        if (isset($formValidation)) {
            return;
        }

        $this->service->setEditQueryparts();
        $resultItems = $this->queryBuilderDefinition->getEditableFields($this->queryBuilder, $this->dbConn,
            new \ZenCart\QueryBuilder\DerivedItemManager);
        foreach ($this->tplVars['pageDefinition']['fields'] as $key => $value) {
            $this->tplVars['pageDefinition']['fields'][$key]['value'] = $resultItems[0][$key];
            $this->service->populateLanguageKeysFromDb($key, $languages);
        }
    }


    /**
     * @param $formValidation
     * @param $languages
     */
    protected function setValidationErrors($formValidation, $languages)
    {
        if (isset($formValidation)) {
            $errors = $formValidation->getErrors();
            $this->tplVars['validationErrors'] = $errors;
            foreach ($this->tplVars['pageDefinition']['fields'] as $key => $value) {
                $realKey = 'entry_field_' . $key;
                $this->tplVars['pageDefinition']['fields'][$key]['value'] = $this->request->readPost($realKey);
                $this->service->populateLanguageKeysFromPost($key, $languages);
            }
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
        $languages = $this->service->prepareLanguageTplVars();
        $this->tplVars['pageDefinition'] = $this->pageDefinitionBuilder->getPageDefinition();
        $this->tplVars['pageDefinition']['contentTemplate'] = 'tplAdminLeadAddEditContent.php';
        $this->view->getTplVarManager()->set('legendTitle', TEXT_LEAD_ADD_ENTRY);
        $this->tplVars['pageDefinition']['languages'] = $languages;
        $this->tplVars['pageDefinition']['action'] = 'add';
        $this->tplVars['pageDefinition']['formAction'] = 'insert';
        $this->tplVars['pageDefinition']['cancelButtonAction'] = zen_href_link($this->request->readGet('cmd'), zen_get_all_get_params(array('action')));
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
}
