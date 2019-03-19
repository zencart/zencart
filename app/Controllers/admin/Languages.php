<?php
/**
 * @copyright Copyright 2003-2017 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  New in v1.6.0 $
 */
namespace App\Controllers\admin;

use App\Controllers\AbstractLeadController;

/**
 * Class Languages
 * @package App\Controllers
 */
class Languages extends AbstractLeadController
{
    /**]
     * @var
     */
    protected $tableList;

    /**
     *
     */
    public function editExecute($formValidation = null)
    {
        parent::editExecute($formValidation);
        if ($this->pageDefinitionBuilder->getPageDefinition()['fields']['code']['value'] == DEFAULT_LANGUAGE) {
            $this->tplVarManager->forget('pageDefinition.fields.setAsDefault');
        }
    }

    /**
     *
     */
    protected function initController($pageDefinitionBuilder, $serviceFactory)
    {
        parent::initController($pageDefinitionBuilder, $serviceFactory);
        $this->tableList = $this->setTableList();
    }

    /**
     * @return array
     */
    protected function setTableList()
    {
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
                'table' => TABLE_METATAGS_PRODUCTS_DESCRIPTION,
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
                'table' => TABLE_COUNTRIES_NAME,
                'languageKeyField' => 'language_id',
                'orderBy' => 'countries_id',
                'fields' => array(
                    'countries_id' => 'integer',
                    'countries_name' => 'string',
                )
            ),
        );
        return $tableList;
    }

    /**
     * @return mixed
     */
    public function getTableList()
    {
        return $this->tableList;
    }

}
