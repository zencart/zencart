<?php
/**
 * Class AbstractListingBox
 *
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: currencies.php 15880 2010-04-11 16:24:30Z wilt $
 */
namespace ZenCart\ListingBox\Box;
/**
 * Class AbstractListingBox
 * @package ZenCart\ListingBox\Box
 */
abstract class AbstractListingBox extends \base
{
    /**
     * @var
     */
    protected $productQuery;
    /**
     * @var
     */
    protected $outputLayout;
    /**
     * @var
     */
    protected $formatter;
    /**
     * @var
     */
    protected $paginator;
    /**
     * @var
     */
    protected $templateVariables;

    /**
     *
     */
    public abstract function __construct();

    /**
     * @param $diContainer
     */
    public function init($diContainer)
    {
        $this->diContainer = $diContainer;
        $this->initTemplateVariables();
    }

    /**
     *
     */
    protected function initTemplateVariables()
    {
        $formattedItems = $this->diContainer->get('formatter')->format();
        $this->diContainer->get('formatter')->setTemplateVars($formattedItems);

        $hasContent = (count($formattedItems) > 0) ? true : false;
        $this->notify('NOTIFY_LISTING_BOX_INITTEMPLATEVARIABLES_START');
        $this->templateVariables['filter'] = $this->getFilterVars();
        $this->templateVariables ['title'] = $this->initTitle();
        $this->templateVariables ['items'] = $this->diContainer->get('queryBuilder')->getResultItems();
        $this->templateVariables ['hasFormattedItems'] = $hasContent;
        $this->templateVariables ['formattedItems'] = $formattedItems;
        $this->templateVariables ['template'] = $this->diContainer->get('formatter')->getDefaultTemplate();
        $paginator = $this->diContainer->get('paginator')->getParams();
        $this->templateVariables ['pagination'] = $paginator;
        $this->templateVariables ['pagination']['show'] = ($paginator['totalPages'] > 0);
        $this->templateVariables ['pagination']['showTop'] = ((PREV_NEXT_BAR_LOCATION == '1') || (PREV_NEXT_BAR_LOCATION == '3'));
        $this->templateVariables ['pagination']['showBottom'] = ((PREV_NEXT_BAR_LOCATION == '2') || (PREV_NEXT_BAR_LOCATION == '3'));
        $this->templateVariables ['showFiltersForm'] = $this->showFilterForm();
        $this->notify('NOTIFY_LISTING_BOX_INITTEMPLATEVARIABLES_END');
    }

    /**
     * @return bool
     */
    public function showFilterForm()
    {
        return true;
    }

    /**
     * @return mixed
     */
    public function getTemplateVariables()
    {
        $this->notify('NOTIFY_LISTING_BOX_GETTEMPLATEVARIABLES_START');
        return $this->templateVariables;
    }

    /**
     * @param $varName
     * @param $value
     */
    public function setTemplateVariable($varName, $value)
    {
        $this->templateVariables[$varName] = $value;
    }

    /**
     * @param array $productQuery
     */
    public function setProductQuery(array $productQuery)
    {
        $this->productQuery = $productQuery;
    }

    /**
     * @param array $outputLayout
     */
    public function setOutputLayout(array $outputLayout)
    {
        $this->outputLayout = $outputLayout;
    }

    /**
     * @return mixed
     */
    public function getProductQuery()
    {
        return $this->productQuery;
    }

    /**
     * @return mixed
     */
    public function getOutputLayout()
    {
        return $this->outputLayout;
    }

    /**
     * @return int
     */
    public function getFormattedItemsCount()
    {
        $this->notify('NOTIFY_LISTING_BOX_GETFORMATTEDITEMSCOUNT_START');
        return count($this->templateVariables['formattedItems']);
    }

    /**
     * @return int
     */
    public function getItemCount()
    {
        $this->notify('NOTIFY_LISTING_BOX_GETFORMATTEDITEMSCOUNT_START');

        return count($this->diContainer->get('queryBuilder')->getResultItems());
    }

    /**
     * @param $filterVars
     */
    public function setFilterVars($filterVars)
    {
        $this->filterVars = $filterVars;
    }

    /**
     * @return mixed
     */
    public function getFilterVars()
    {
        return $this->filterVars;
    }
}
