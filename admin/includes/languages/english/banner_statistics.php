<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce                                       |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003 The zen-cart developers                           |
// |                                                                      |
// | http://www.zen-cart.com/index.php                                    |
// |                                                                      |
// | Portions Copyright (c) 2003 osCommerce                               |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.zen-cart.com/license/2_0.txt.                             |
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to       |
// | license@zen-cart.com so we can mail you a copy immediately.          |
// +----------------------------------------------------------------------+
//  $Id: banner_statistics.php 1105 2005-04-04 22:05:35Z birdbrain $
//

define('HEADING_TITLE', 'Banner Statistics');

define('TABLE_HEADING_SOURCE', 'Source');
define('TABLE_HEADING_VIEWS', 'Views');
define('TABLE_HEADING_CLICKS', 'Clicks');

define('TEXT_BANNERS_DATA', 'D<br>a<br>t<br>a');
define('TEXT_BANNERS_DAILY_STATISTICS', '%s Daily Statistics For %s %s');
define('TEXT_BANNERS_MONTHLY_STATISTICS', '%s Monthly Statistics For %s');
define('TEXT_BANNERS_YEARLY_STATISTICS', '%s Yearly Statistics');

define('STATISTICS_TYPE_DAILY', 'Daily');
define('STATISTICS_TYPE_MONTHLY', 'Monthly');
define('STATISTICS_TYPE_YEARLY', 'Yearly');

define('TITLE_TYPE', 'Type:');
define('TITLE_YEAR', 'Year:');
define('TITLE_MONTH', 'Month:');

define('ERROR_GRAPHS_DIRECTORY_DOES_NOT_EXIST', 'Error: Graphs directory does not exist. Please create a graphs directory example: <strong>' . DIR_WS_ADMIN . 'images/graphs</strong>');
define('ERROR_GRAPHS_DIRECTORY_NOT_WRITEABLE', 'Error: Graphs directory is not writeable. This is located at: <strong>' . DIR_WS_ADMIN . 'images/graphs</strong>');
?>