<?php
/**
 * @copyright Copyright 2003-2017 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  New in v1.6.0 $
 */
namespace App\Controllers;

use ZenCart\Page\BuilderFactory as PageBuilderFactory;
use ZenCart\QueryBuilder\QueryBuilder;
use ZenCart\Request\Request as Request;
use ZenCart\Paginator\Paginator as Paginator;
use ZenCart\QueryBuilder\PaginatorBuilder as PaginatorBuilder;
use ZenCart\AdminUser\AdminUser as User;
use ZenCart\View\ViewFactory as View;
use ZenCart\Services\ServiceFactory;
use ZenCart\ListingQueryAndOutput\ViewDefinitionFactory;

/**
 * Class AbstractListingController
 * @package App\Controllers
 */
abstract class AbstractListingController extends AbstractAdminController
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
     * @var
     */
    protected $service;

    /**
     * @var
     */
    protected $queryBuilderDefinition;

    /**
     * @var
     */
    protected $pageDefinitionBuilder;

    /**
     * @var
     */
    protected $queryBuilder;

    /**
     * @var Paginator
     */
    protected $paginator;


    /**
     * AbstractListingController constructor.
     * @param Request $request
     * @param \App\Model\ModelFactory $modelFactory
     * @param User $user
     * @param View $view
     * @param Paginator $paginator
     */
    public function __construct(Request $request, $modelFactory, User $user, View $view, Paginator $paginator)
    {
        parent::__construct($request, $modelFactory, $user, $view);
        $this->paginator = $paginator;
        $this->initQueryBuilderDefinition(new ViewDefinitionFactory());
        $this->initPageBuilderFactory(new PageBuilderFactory());
        $this->initQueryBuilder(new QueryBuilder($this->dbConn, $this->queryBuilderDefinition->getListingQuery()));
        $this->initPagination(new PaginatorBuilder($this->request, $this->queryBuilderDefinition->getListingQuery(),
            $this->paginator));
        $this->queryBuilderDefinition->setPageDefinition($this->pageDefinitionBuilder->getPageDefinition());
        $this->setService(new ServiceFactory());
    }

    /**
     * @param ViewDefinitionFactory $viewDefinitionFactory
     */
    protected function initQueryBuilderDefinition(ViewDefinitionFactory $viewDefinitionFactory)
    {
        $type = $this->classPrefix . ucfirst(\base::camelize($this->request->readGet('cmd')));
        $this->queryBuilderDefinition = $viewDefinitionFactory->factory($type, $this->request, $this->modelFactory);
    }

    /**
     * @param PageBuilderFactory $pageDefinitionBuilder
     */
    protected function initPageBuilderFactory(PageBuilderFactory $pageDefinitionBuilder)
    {
        $this->pageDefinitionBuilder = $pageDefinitionBuilder->factory($this->classPrefix, $this->queryBuilderDefinition, $this->request);
    }

    /**
     * @param QueryBuilder $queryBuilder
     */
    protected function initQueryBuilder(QueryBuilder $queryBuilder)
    {
        $leadDef = $this->pageDefinitionBuilder->getPageDefinition();
        $this->paginator->setScrollerParams(array('mvcCmdName' => 'cmd'));
        $this->queryBuilder = $queryBuilder;
        $this->paginator->setAdapterParams(array('itemsPerPage' => $leadDef['paginationLimitDefault']));
    }

    /**
     * @param PaginatorBuilder $paginatorBuilder
     */
    protected function initPagination(PaginatorBuilder $paginatorBuilder)
    {
        $this->paginatorBuilder = $paginatorBuilder;
    }

    /**
     * @param ServiceFactory $serviceFactory
     */
    protected function setService(ServiceFactory $serviceFactory)
    {
        $this->service = $serviceFactory->factory('Lead', 'Routes', $this, $this->request, $this->modelFactory);
        $this->service->setQueryBuilderDefinition($this->queryBuilderDefinition);
        $this->service->setQueryBuilder($this->queryBuilder);

    }
    /**
     *
     */
    public function mainExecute()
    {
        $this->service->manageLanguageJoin();
        $this->queryBuilderDefinition->buildResults($this->queryBuilder, $this->dbConn,
            new \ZenCart\QueryBuilder\DerivedItemManager, $this->paginatorBuilder->getPaginator());
        $this->setDefaultTplVars($this->pageDefinitionBuilder, $this->queryBuilderDefinition);
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
        $this->queryBuilderDefinition->buildResults($this->queryBuilder, $this->dbConn,
            new \ZenCart\QueryBuilder\DerivedItemManager, $this->paginatorBuilder->getPaginator());
        $this->setDefaultTplVars($this->pageDefinitionBuilder, $this->queryBuilderDefinition);
        $tplRows = $this->view->loadTemplateAsString('includes/template/partials/tplAdminLeadItemRows.php', $this->tplVars);
        $paginator = $this->view->loadTemplateAsString('includes/template/partials/tplPaginatorStandard.php', $this->tplVars);
        $ma = $this->view->loadTemplateAsString('includes/template/partials/tplAdminLeadMultipleActions.php', $this->tplVars);
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
        $outputLayout = $this->queryBuilderDefinition->getOutputLayout();
        $listingQuery = $this->queryBuilderDefinition->getListingQuery();
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
        $outputLayout = $this->queryBuilderDefinition->getOutputLayout();
        if (!isset($outputLayout['fields'][$mainKey]['language'])) {
            return;
        }
        foreach ($languages as $languageKey => $languageValue) {
            $this->tplVarManager->set('pageDefinition.fields.' . $mainKey . '.value' , array($languageKey => ''));
        }
    }

    /**
     * @param $builder
     * @param $listingBox
     */
    protected function setDefaultTplVars($builder, $listingBox)
    {
        $this->tplVarManager->set('pageDefinition' , $builder->getPageDefinition());
        $this->tplVarManager->set('listingBox' , $listingBox->getTplVars());
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
