<?php
/**
 * zcDashboardWidgetGeneralStatistics Class.
 *
 * @package classes
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: Ian Wilson  Fri Aug 17 17:42:37 2012 +0100 New in v1.5.1 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
/**
 * zcDashboardWidgetGeneralStatistics Class
 *
 * @package classes
 */
class zcDashboardWidgetGeneralStatistics extends zcDashboardWidgetBase
{
  public function prepareContent()
  {
    global $db;
    $tplVars = array();
    $customers = $db->Execute("select count(*) as count from " . TABLE_CUSTOMERS);
    $products = $db->Execute("select count(*) as count from " . TABLE_PRODUCTS . " where products_status = '1'");
    $products_off = $db->Execute("select count(*) as count from " . TABLE_PRODUCTS . " where products_status = '0'");
    $reviews = $db->Execute("select count(*) as count from " . TABLE_REVIEWS);
    $reviews_pending = $db->Execute("select count(*) as count from " . TABLE_REVIEWS . " where status='0'");
    $newsletters = $db->Execute("select count(*) as count from " . TABLE_CUSTOMERS . " where customers_newsletter = '1'");
    $counter = $db->Execute("select startdate, counter from " . TABLE_COUNTER);
    if ($counter->EOF) {$counter = new StdClass; $counter->fields = array('startdate'=>date('Ymd'), 'counter'=>0);}
    $counter_startdate = $counter->fields['startdate'];
    $counter_startdate_formatted = strftime(DATE_FORMAT_SHORT, mktime(0, 0, 0, substr($counter_startdate, 4, 2), substr($counter_startdate, -2), substr($counter_startdate, 0, 4)));
    $specials = $db->Execute("select count(*) as count from " . TABLE_SPECIALS . " where status= '0'");
    $specials_act = $db->Execute("select count(*) as count from " . TABLE_SPECIALS . " where status= '1'");
    $featured = $db->Execute("select count(*) as count from " . TABLE_FEATURED . " where status= '0'");
    $featured_act = $db->Execute("select count(*) as count from " . TABLE_FEATURED . " where status= '1'");
    $salemaker = $db->Execute("select count(*) as count from " . TABLE_SALEMAKER_SALES . " where sale_status = '0'");
    $salemaker_act = $db->Execute("select count(*) as count from " . TABLE_SALEMAKER_SALES . " where sale_status = '1'");
    $tplVars['content'] = array();
    $tplVars['content'][] = array('text'=>BOX_ENTRY_COUNTER_DATE, 'value'=>$counter_startdate_formatted);
    $tplVars['content'][] = array('text'=>BOX_ENTRY_COUNTER, 'value'=>$counter->fields['counter']);
    $tplVars['content'][] = array('text'=>BOX_ENTRY_CUSTOMERS, 'value'=>$customers->fields['count']);
    $tplVars['content'][] = array('text'=>BOX_ENTRY_PRODUCTS, 'value'=>$products->fields['count']);

    $tplVars['content'][] = array('text'=>BOX_ENTRY_PRODUCTS_OFF, 'value'=>$products_off->fields['count']);
    $tplVars['content'][] = array('text'=>BOX_ENTRY_REVIEWS, 'value'=>$reviews->fields['count']);
    if (REVIEWS_APPROVAL=='1')
    {
      $tplVars['content'][] = array('text'=>'<a href="' . zen_href_link(FILENAME_REVIEWS, 'status=1', 'NONSSL') . '">' . BOX_ENTRY_REVIEWS_PENDING . '</a>', 'value'=>$reviews_pending->fields['count']);
    }
    $tplVars['content'][] = array('text'=>BOX_ENTRY_NEWSLETTERS, 'value'=>$newsletters->fields['count']);
    $tplVars['content'][] = array('text'=>BOX_ENTRY_SPECIALS_EXPIRED, 'value'=>$specials->fields['count']);

    $tplVars['content'][] = array('text'=>BOX_ENTRY_SPECIALS_ACTIVE, 'value'=>$specials_act->fields['count']);
    $tplVars['content'][] = array('text'=>BOX_ENTRY_FEATURED_EXPIRED, 'value'=>$featured->fields['count']);
    $tplVars['content'][] = array('text'=>BOX_ENTRY_FEATURED_ACTIVE, 'value'=>$featured_act->fields['count']);
    $tplVars['content'][] = array('text'=>BOX_ENTRY_SALEMAKER_EXPIRED, 'value'=>$salemaker->fields['count']);
    $tplVars['content'][] = array('text'=> BOX_ENTRY_SALEMAKER_ACTIVE, 'value'=>$salemaker_act->fields['count']);
    return $tplVars;
  }
}