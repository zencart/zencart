<?php
/**
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  New in v1.6.0 $
 */
namespace ZenCart\Controllers;

/**
 * Class MusicGenre
 * @package ZenCart\Controllers
 */
class MediaManagerProducts extends AbstractLeadController
{
    /**
     *
     */
    public function productsFromCategoryExecute()
    {
        if (!$this->request->has('id', 'post')) {
            return;
        }
        $products = new \products();
        $productList = $products->get_products_in_category($this->request->readPost('id'), false);
        $result = TEXT_NO_PRODUCTS;
        if ($productList) {
            $result = "Has products in this Category";
            $result = zen_draw_pull_down_menu('current_product_id', $productList);
        }
        $this->response =  array('html'=>$result);
    }

    /**
     *
     */
    public function insertExecute()
    {
        if (!$this->request->has('current_product_id', 'post')) {
            $this->response['redirect'] = zen_href_link(FILENAME_MEDIA_MANAGER_PRODUCTS, 'action=add&media_id='. $this->request->readGet('media_id'));
            return;
        }
        $sql = "insert into " . TABLE_MEDIA_TO_PRODUCTS . " (media_id, product_id) values
                                          (:mediaId:, :productId:)";
        $sql = $this->dbConn->bindVars($sql, ':mediaId:', $this->request->readGet('media_id'), 'integer');
        $sql = $this->dbConn->bindVars($sql, ':productId:', $this->request->readPost('current_product_id'), 'integer');
        $this->dbConn->execute($sql);
        $this->response['redirect'] = zen_href_link(FILENAME_MEDIA_MANAGER_PRODUCTS, 'media_id='. $this->request->readGet('media_id'));
    }
}
