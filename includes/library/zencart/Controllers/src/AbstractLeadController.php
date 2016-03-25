<?php
/**
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  New in v1.6.0 $
 */
namespace ZenCart\Controllers;

use ZenCart\Lead\Builder;
use ZenCart\QueryBuilder\QueryBuilder;
use ZenCart\Request\Request as Request;
use ZenCart\Paginator\Paginator as Paginator;
use ZenCart\ListingBox\PaginatorBuilder as PaginatorBuilder;
use ZenCart\Services\LeadRoutes as LeadService;

/**
 * Class AbstractLeadController
 * @package ZenCart\Controllers
 */
abstract class AbstractLeadController extends AbstractController
{
    /**
     * @var bool
     */
    public $useFoundation = true;
    /**
     * @var string
     */
    public $mainTemplate = 'tplAdminLead.php';

    /**
     * @param $controllerCommand
     * @param Request $request
     * @param $db
     */
    public function __construct($controllerCommand, Request $request, $db)
    {
        parent::__construct($controllerCommand, $request, $db);
        $listingBox = 'Lead' . ucfirst(\base::camelize($this->controllerCommand));
        $boxClass = NAMESPACE_LISTINGBOX . '\\boxes\\' . $listingBox;
        $this->listingBox = new $boxClass($this->request);
        $this->leadDefinitionBuilder = new Builder($this->listingBox, $request);
        $this->queryBuilder = new QueryBuilder($this->dbConn, $this->listingBox->getListingQuery());
        $leadDef = $this->leadDefinitionBuilder->getleadDefinition();
        $this->paginator = new Paginator($request);
        $this->paginator->setScrollerParams(array('mvcCmdName' => 'cmd'));
        $this->paginatorBuilder = new PaginatorBuilder($request, $this->listingBox->getListingQuery(),
            $this->paginator);
        $this->paginator->setAdapterParams(array('itemsPerPage' => $leadDef['paginationLimitDefault']));
        $this->listingBox->setLeadDefinition($this->leadDefinitionBuilder->getleadDefinition());
        $this->service = LeadService::factory('Lead', 'Routes', $this, $request, $db);
        $this->service->setListingBox($this->listingBox);
        $this->service->setQueryBuilder($this->queryBuilder);
    }

    /**
     *
     */
    public function mainExecute()
    {
        $this->service->manageLanguageJoin();
        $this->listingBox->buildResults($this->queryBuilder, $this->dbConn,
            new \ZenCart\ListingBox\DerivedItemManager, $this->paginatorBuilder->getPaginator());
        $this->setDefaultTplVars($this->leadDefinitionBuilder, $this->listingBox);
    }

    /**
     *
     */
    public function paginatorExecute()
    {
        $this->useView = false;
        $this->filterExecute();
    }

    /**
     *
     */
    public function updateFieldExecute()
    {
        $this->useView = false;
        $this->service->updateField();
        $this->filterExecute();
    }

    /**
     *
     */
    public function filterExecute()
    {
        $this->useView = false;
        $this->service->doFilter();
        $this->listingBox->buildResults($this->queryBuilder, $this->dbConn,
            new \ZenCart\ListingBox\DerivedItemManager, $this->paginatorBuilder->getPaginator());
        $this->setDefaultTplVars($this->leadDefinitionBuilder, $this->listingBox);
        $tplRows = $this->loadTemplateAsString('includes/template/partials/tplAdminLeadItemRows.php', $this->tplVars);
        $paginator = $this->loadTemplateAsString('includes/template/partials/tplPaginatorStandard.php', $this->tplVars);
        $ma = $this->loadTemplateAsString('includes/template/partials/tplAdminLeadMultipleActions.php', $this->tplVars);
        $this->response = array(
            'html' => array(
                'itemRows' => $tplRows,
                'paginator' => $paginator,
                'ma' => $ma
            )
        );
    }

    /**
     *
     */
    public function editExecute()
    {
        $languages = $this->service->prepareLanguageTplVars();
        $this->tplVars['languages'] = $languages;
        $this->service->setEditQueryparts();
        $resultItems = $this->listingBox->buildResults($this->queryBuilder, $this->dbConn,
            new \ZenCart\ListingBox\DerivedItemManager, $this->paginatorBuilder->getPaginator());
        $this->tplVars['legendTitle'] = TEXT_LEAD_EDIT_ENTRY;
        $this->tplVars['leadDefinition'] = $this->leadDefinitionBuilder->getleadDefinition();
        $this->tplVars['leadDefinition']['contentTemplate'] = 'tplAdminLeadAddEditContent.php';
        $this->tplVars['leadDefinition']['action'] = 'edit';
        $this->tplVars['leadDefinition']['formAction'] = 'update';
        foreach ($this->tplVars['leadDefinition']['fields'] as $key => $value) {
            $this->tplVars['leadDefinition']['fields'][$key]['value'] = $resultItems[0][$key];
            $this->service->populateLanguageKeys($key, $languages, $resultItems);
        }
        $this->tplVars['hiddenFields'][] = $this->service->getEditHiddenField();
        $this->tplVars['leadDefinition']['cancelButtonAction'] = zen_href_link($this->request->readGet('cmd'), zen_get_all_get_params(array('action')));
    }

    /**
     *
     */
    public function updateExecute()
    {
        if (!$this->hasPostsCheck()) {
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
    public function addExecute()
    {
        $outputLayout = $this->listingBox->getOutputLayout();
        $languages = $this->service->prepareLanguageTplVars();
        $this->tplVars['languages'] = $languages;
        $resultItems = $this->listingBox->buildResults($this->queryBuilder, $this->dbConn,
            new \ZenCart\ListingBox\DerivedItemManager, $this->paginatorBuilder->getPaginator());
        $this->tplVars['leadDefinition'] = $this->leadDefinitionBuilder->getleadDefinition();
        if (isset($outputLayout['editMap'])) {
            foreach ($outputLayout['editMap'] as $key) {
                $this->resetLanguageKeys($key, $languages);
            }
        }
        $this->tplVars['leadDefinition']['contentTemplate'] = 'tplAdminLeadAddEditContent.php';
        $this->tplVars['legendTitle'] = TEXT_LEAD_ADD_ENTRY;
        $this->tplVars['leadDefinition']['action'] = 'add';
        $this->tplVars['leadDefinition']['formAction'] = 'insert';
        $this->tplVars['leadDefinition']['cancelButtonAction'] = zen_href_link($this->request->readGet('cmd'), zen_get_all_get_params(array('action')));
    }

    /**
     *
     */
    public function insertExecute()
    {
        if (!$this->hasPostsCheck()) {
            return;
        }
        $this->service->insertExecute();
        $this->response['redirect'] = zen_href_link($this->request->readGet('cmd'));
    }

    /**
     *
     */
    public function autocompleteExecute()
    {
        $this->useView = false;
        $retVal = $this->service->autocompleteExecute();
        $this->response = $retVal;
    }

    /**
     *
     */
    public function deleteExecute()
    {
        $this->useView = false;
        $result = $this->service->deleteExecute();
        if ($result === true) {
            $this->filterExecute();

            return;
        }
        header("Status: 403 Forbidden", true, 403);  //@todo REFACTOR  handle header output in main controller
        $this->response = $result;
    }

    /**
     *
     */
    public function multiDeleteExecute()
    {
        $this->useView = false;
        if (count($this->request->readPost('selected')) === 0) {
            return;
        }
        $result = $this->service->multiDeleteExecute();
        if ($result === true) {
            $this->filterExecute();
            return;
        }
        header("Status: 403 Forbidden", true, 403);  //@todo REFACTOR  handle header output in main controller
        $this->response = $result;
    }

    /**
     *
     */
    public function paginationLimitExecute()
    {
        $this->useView = false;
        $outputLayout = $this->listingBox->getOutputLayout();
        $listingQuery = $this->listingBox->getListingQuery();
        if (is_numeric($this->request->readGet('limit'))) {
            $paginationSessKey = issetorArray($outputLayout, 'paginationSessionKey',
                $listingQuery['mainTable']['table'] . '_pql');
            $_SESSION[$paginationSessKey] = $this->request->readGet('limit');
            $this->paginator->setAdapterParams(array('itemsPerPage' => $this->request->readGet('limit')));
            $this->filterExecute();
        }
    }

    /**
     * @param $mainKey
     * @param $languages
     */
    public function resetLanguageKeys($mainKey, $languages)
    {
        $outputLayout = $this->listingBox->getOutputLayout();
        if (!isset($outputLayout['fields'][$mainKey]['language'])) {
            return;
        }
        foreach ($languages as $languageKey => $languageValue) {
            $this->tplVars['leadDefinition']['fields'][$mainKey]['value'][$languageKey] = "";
        }
    }

    /**
     * @param $builder
     * @param $listingBox
     */
    protected function setDefaultTplVars($builder, $listingBox)
    {
        $this->tplVars['leadDefinition'] = $builder->getleadDefinition();
        $this->tplVars['listingBox'] = $listingBox->getTplVars();
    }

    /**
     * @return bool
     */
    public function hasPostsCheck()
    {
        if (count($this->request->all('post')) == 0) {
            $this->response['redirect'] = zen_href_link($this->request->readGet('cmd'));
            return false;
        }
        return true;
    }
}
