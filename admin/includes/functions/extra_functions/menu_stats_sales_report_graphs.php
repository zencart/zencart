<?php
/**
 * @package admin
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @author inspired from sales_report_graphs.php,v 0.01 2002/11/27 19:02:22 cwi Exp  Released under the GNU General Public License $
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  New in v1.5.5 $
 */

if (function_exists('zen_register_admin_page')) {
    if (!zen_page_key_exists ('reportSalesWithGraphs')) {
        zen_register_admin_page('reportSalesWithGraphs', 'BOX_REPORTS_SALES_REPORT_GRAPHS', 'FILENAME_STATS_SALES_REPORT_GRAPHS', '', 'reports', 'Y', 15);
    }
}
