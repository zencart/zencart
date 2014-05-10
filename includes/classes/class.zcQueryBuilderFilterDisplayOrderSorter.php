<?php
/**
 * File contains just the zcQueryBuilderFilterDisplayOrderSorter class
 *
 * @package classes
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  $
 */
/**
 * class zcQueryBuilderFilterDisplayOrderSorter
 *
 * @package classes
 */
class zcQueryBuilderFilterDisplayOrderSorter extends zcAbstractQueryBuilderFilterBase
{
  /**
   * (non-PHPdoc)
   * @see zcAbstractQueryBuilderFilterBase::filterItem()
   */
  public function filterItem()
  {
    $this->outputVariables  ['displayOrderDefault'] = $this->parameters ['defaultSortOrder'];
    if (! zcRequest::hasGet('disp_order')) {
      zcRequest::set('disp_order', $this->outputVariables  ['displayOrderDefault'], 'get');
      $this->outputVariables  ['displayOrder'] = $this->outputVariables ['displayOrderDefault'];
    } else {
      $this->outputVariables  ['displayOrder'] = zcRequest::readGet('disp_order', 0);
    }
    switch (true) {
      case (zcRequest::readGet('disp_order', 0) == 0) :
        // reset and let reset continue
        zcRequest::set('disp_order', $this->outputVariables ['displayOrderDefault'], 'get');
        $this->outputVariables  ['displayOrder'] = $this->outputVariables ['displayOrderDefault'];
      case (zcRequest::readGet('disp_order', 0) == 1) :
        $orderBy = " pd.products_name";
        break;
      case (zcRequest::readGet('disp_order', 0) == 2) :
        $orderBy = " pd.products_name DESC";
        break;
      case (zcRequest::readGet('disp_order', 0) == 3) :
        $orderBy = " p.products_price_sorter, pd.products_name";
        break;
      case (zcRequest::readGet('disp_order', 0) == 4) :
        $orderBy = " p.products_price_sorter DESC, pd.products_name";
        break;
      case (zcRequest::readGet('disp_order', 0) == 5) :
        $orderBy = "p.products_model";
        break;
      case (zcRequest::readGet('disp_order', 0) == 6) :
        $orderBy = " p.products_date_added DESC, pd.products_name";
        break;
      case (zcRequest::readGet('disp_order', 0) == 7) :
        $orderBy = " p.products_date_added, pd.products_name";
        break;
      default :
        $orderBy = " p.products_sort_order";
        break;
    }
    $this->parts ['orderBys'] [] = array(
        'type' => 'custom',
        'field' => $orderBy
    );
  }
}