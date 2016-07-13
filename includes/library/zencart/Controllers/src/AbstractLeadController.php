<?php
/**
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  New in v1.6.0 $
 */
namespace ZenCart\Controllers;

use ZenCart\FormValidation\FormValidation;
use ZenCart\Lead\Builder;
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
    public $classPrefix = 'Lead';
    /**
     * @var string
     */
    public $mainTemplate = 'tplAdminLead.php';

    /**
     *
     */
    public function editExecute($formValidation = null)
    {
        $languages = $this->service->prepareLanguageTplVars();
        $this->tplVars['languages'] = $languages;
        $this->tplVars['legendTitle'] = TEXT_LEAD_EDIT_ENTRY;
        $this->tplVars['leadDefinition'] = $this->leadDefinitionBuilder->getleadDefinition();
        $this->tplVars['leadDefinition']['contentTemplate'] = 'tplAdminLeadAddEditContent.php';
        $this->tplVars['leadDefinition']['action'] = 'edit';
        $this->tplVars['leadDefinition']['formAction'] = 'update';

        if (!isset($formValidation)) {
            $this->service->setEditQueryparts();
            $resultItems = $this->listingBox->buildResults($this->queryBuilder, $this->dbConn,
                new \ZenCart\QueryBuilder\DerivedItemManager, $this->paginatorBuilder->getPaginator(), true);
            foreach ($this->tplVars['leadDefinition']['fields'] as $key => $value) {
                $this->tplVars['leadDefinition']['fields'][$key]['value'] = $resultItems[0][$key];
                $this->service->populateLanguageKeysFromDb($key, $languages);
            }
        }
        $this->setValidationErrors($formValidation, $languages);
        $this->tplVars['hiddenFields'][] = $this->service->getEditHiddenField();
        $this->tplVars['leadDefinition']['cancelButtonAction'] = zen_href_link($this->request->readGet('cmd'), zen_get_all_get_params(array('action')));
    }

    protected function setValidationErrors($formValidation, $languages)
    {
        if (isset($formValidation)) {
            $errors = $formValidation->getErrors();
            $this->tplVars['validationErrors'] = $errors;
            foreach ($this->tplVars['leadDefinition']['fields'] as $key => $value) {
                $realKey = 'entry_field_' . $key;
                $this->tplVars['leadDefinition']['fields'][$key]['value'] = $this->request->readPost($realKey);
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
        $outputLayout = $this->listingBox->getOutputLayout();
        $languages = $this->service->prepareLanguageTplVars();
        $this->tplVars['languages'] = $languages;
        $this->tplVars['leadDefinition'] = $this->leadDefinitionBuilder->getleadDefinition();
        $this->tplVars['leadDefinition']['contentTemplate'] = 'tplAdminLeadAddEditContent.php';
        $this->tplVars['legendTitle'] = TEXT_LEAD_ADD_ENTRY;
        $this->tplVars['leadDefinition']['action'] = 'add';
        $this->tplVars['leadDefinition']['formAction'] = 'insert';
        $this->tplVars['leadDefinition']['cancelButtonAction'] = zen_href_link($this->request->readGet('cmd'), zen_get_all_get_params(array('action')));
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



    public function buildValidationEntries()
    {
        $leadDefinition = $this->leadDefinitionBuilder->getleadDefinition();
        $validationEntries = array();
        foreach ($this->request->all('post') as $key => $value ) {
            $realKey = str_replace('entry_field_', '', $key);
            if ($this->service->checkValidUpdateKey($key, $realKey)) {
                $validationEntries[] = array('name' => $realKey, 'value' => $value, 'validations' => $leadDefinition['validations'][$realKey]);
            }
        }
        return $validationEntries;
    }

}
