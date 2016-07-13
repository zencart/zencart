<?php
/**
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  New in v1.6.0 $
 */
namespace ZenCart\Controllers;

use ZenCart\FormValidation\FormValidation;
use ZenCart\Lead\BuilderFactory;
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
abstract class AbstractListingController extends AbstractAdminController
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
     * @param $controllerCommand
     * @param Request $request
     * @param $db
     */
    public function __construct(Request $request, $db, User $user, Paginator $paginator)
    {
        parent::__construct($request, $db, $user);
        $this->paginator = $paginator;
        $this->initController();
    }

    /**
     * @todo REFACTORING DI listingbox factory
     * @todo REFACTORING DI querybuilder
     */
    protected function initController()
    {
        $listingBox = $this->classPrefix . ucfirst(\base::camelize($this->controllerCommand));
        $boxClass = NAMESPACE_QUERYBUILDERDEFINITIONS . '\\definitions\\' . $listingBox;
        $this->listingBox = new $boxClass($this->request, $this->dbConn);
        $builderFactory = new BuilderFactory;
        $this->leadDefinitionBuilder = $builderFactory->factory($this->classPrefix, $this->listingBox, $this->request);
        $this->queryBuilder = new QueryBuilder($this->dbConn, $this->listingBox->getListingQuery());
        $leadDef = $this->leadDefinitionBuilder->getleadDefinition();
        $this->paginator->setScrollerParams(array('mvcCmdName' => 'cmd'));
        $this->paginatorBuilder = new PaginatorBuilder($this->request, $this->listingBox->getListingQuery(),
            $this->paginator);
        $this->paginator->setAdapterParams(array('itemsPerPage' => $leadDef['paginationLimitDefault']));
        $this->listingBox->setLeadDefinition($this->leadDefinitionBuilder->getleadDefinition());
        $this->service = LeadService::factory('Lead', 'Routes', $this, $this->request, $this->dbConn);
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
            new \ZenCart\QueryBuilder\DerivedItemManager, $this->paginatorBuilder->getPaginator());
        $this->setDefaultTplVars($this->leadDefinitionBuilder, $this->listingBox);
    }

    /**
     *
     */
    public function paginatorExecute()
    {
        $this->filterExecute();
    }

    /**
     *
     */
    public function updateFieldExecute()
    {
        $this->service->updateField();
        $this->filterExecute();
    }

    /**
     *
     */
    public function filterExecute()
    {
        $this->service->doFilter();
        $this->listingBox->buildResults($this->queryBuilder, $this->dbConn,
            new \ZenCart\QueryBuilder\DerivedItemManager, $this->paginatorBuilder->getPaginator());
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
    public function paginationLimitExecute()
    {
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
     *
     */
    public function fillByLookupExecute()
    {
        $retVal = $this->service->fillByLookupExecute();
        $this->response = $retVal;
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
