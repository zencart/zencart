<?php
/**
 * @package admin
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: DrByte  Tue Jul 17 11:18:56 2012 -0400 Modified in v1.5.1 $
 */

  require('includes/application_top.php');

  $type = (isset($_GET['type']) ? $_GET['type'] : '');

  $banner_extension = zen_banner_image_extension();

// check if the graphs directory exists
  $dir_ok = false;
  if (function_exists('imagecreate') && zen_not_null($banner_extension)) {
    if (is_dir(DIR_WS_IMAGES . 'graphs')) {
      if (is_writeable(DIR_WS_IMAGES . 'graphs')) {
        $dir_ok = true;
      } else {
        $messageStack->add(ERROR_GRAPHS_DIRECTORY_NOT_WRITEABLE, 'error');
      }
    } else {
      $messageStack->add(ERROR_GRAPHS_DIRECTORY_DOES_NOT_EXIST, 'error');
    }
  }

  $banner = $db->Execute("select banners_title
                                from " . TABLE_BANNERS . "
                                where banners_id = '" . (int)$_GET['bID'] . "'");

  $years_array = array();
  $years = $db->Execute("select distinct year(banners_history_date) as banner_year
                              from " . TABLE_BANNERS_HISTORY . "
                              where banners_id = '" . (int)$_GET['bID'] . "'");
  while (!$years->EOF) {
    $years_array[] = array('id' => $years->fields['banner_year'],
                           'text' => $years->fields['banner_year']);
    $years->MoveNext();
  }

  $months_array = array();
  for ($i=1; $i<13; $i++) {
    $months_array[] = array('id' => $i,
                            'text' => strftime('%B', mktime(0,0,0,$i)));
  }

  $type_array = array(array('id' => 'daily',
                            'text' => STATISTICS_TYPE_DAILY),
                      array('id' => 'monthly',
                            'text' => STATISTICS_TYPE_MONTHLY),
                      array('id' => 'yearly',
                            'text' => STATISTICS_TYPE_YEARLY));
require('includes/admin_html_head.php');
?>
<body>
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
<!-- body_text //-->
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr><?php echo zen_draw_form('year', FILENAME_BANNER_STATISTICS, '', 'get'); ?>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo zen_draw_separator('pixel_trans.gif', '1', HEADING_IMAGE_HEIGHT); ?></td>
            <td class="main" align="right"><?php echo TITLE_TYPE . ' ' . zen_draw_pull_down_menu('type', $type_array, (zen_not_null($type) ? $type : 'daily'), 'onChange="this.form.submit();"'); ?><noscript><input type="submit" value="GO"></noscript><br>
<?php
  switch ($type) {
    case 'yearly': break;
    case 'monthly':
      echo TITLE_YEAR . ' ' . zen_draw_pull_down_menu('year', $years_array, (isset($_GET['year']) ? $_GET['year'] : date('Y')), 'onChange="this.form.submit();"') . '<noscript><input type="submit" value="GO"></noscript>';
      break;
    default:
    case 'daily':
      echo TITLE_MONTH . ' ' . zen_draw_pull_down_menu('month', $months_array, (isset($_GET['month']) ? $_GET['month'] : date('n')), 'onChange="this.form.submit();"') . '<noscript><input type="submit" value="GO"></noscript><br>' . TITLE_YEAR . ' ' . zen_draw_pull_down_menu('year', $years_array, (isset($_GET['year']) ? $_GET['year'] : date('Y')), 'onChange="this.form.submit();"') . '<noscript><input type="submit" value="GO"></noscript>';
      break;
  }
?>
            </td>
          <?php echo zen_hide_session_id() . zen_draw_hidden_field('page', $_GET['page']) . zen_draw_hidden_field('bID', $_GET['bID']); ?></form></tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td class="main" align="right"><?php echo '<a href="' . zen_href_link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $_GET['bID']) . '">' . zen_image_button('button_back.gif', IMAGE_BACK) . '</a>'; ?></td>
      </tr>
      <tr>
        <td align="center">
<?php
  if (function_exists('imagecreate') && ($dir_ok == true) && zen_not_null($banner_extension)) {
    $banner_id = (int)$_GET['bID'];

    switch ($type) {
      case 'yearly':
        include(DIR_WS_INCLUDES . 'graphs/banner_yearly.php');
        echo zen_image(DIR_WS_IMAGES . 'graphs/banner_yearly-' . $banner_id . '.' . $banner_extension);
        break;
      case 'monthly':
        include(DIR_WS_INCLUDES . 'graphs/banner_monthly.php');
        echo zen_image(DIR_WS_IMAGES . 'graphs/banner_monthly-' . $banner_id . '.' . $banner_extension);
        break;
      default:
      case 'daily':
        include(DIR_WS_INCLUDES . 'graphs/banner_daily.php');
        echo zen_image(DIR_WS_IMAGES . 'graphs/banner_daily-' . $banner_id . '.' . $banner_extension);
        break;
    }
?>
          <table border="0" width="600" cellspacing="0" cellpadding="2">
            <tr class="dataTableHeadingRow">
             <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_SOURCE; ?></td>
             <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_VIEWS; ?></td>
             <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_CLICKS; ?></td>
           </tr>
<?php
    for ($i=0, $n=sizeof($stats); $i<$n; $i++) {
      echo '            <tr class="dataTableRow">' . "\n" .
           '              <td class="dataTableContent">' . $stats[$i][0] . '</td>' . "\n" .
           '              <td class="dataTableContent" align="right">' . number_format($stats[$i][1]) . '</td>' . "\n" .
           '              <td class="dataTableContent" align="right">' . number_format($stats[$i][2]) . '</td>' . "\n" .
           '            </tr>' . "\n";
    }
?>
          </table>
<?php
  } else {
    include(DIR_WS_FUNCTIONS . 'html_graphs.php');

    switch ($type) {
      case 'yearly':
        echo zen_banner_graph_yearly($_GET['bID']);
        break;
      case 'monthly':
        echo zen_banner_graph_monthly($_GET['bID']);
        break;
      default:
      case 'daily':
        echo zen_banner_graph_daily($_GET['bID']);
        break;
    }
  }
?>
        </td>
      </tr>
      <tr>
        <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td class="main" align="right"><?php echo '<a href="' . zen_href_link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $_GET['bID']) . '">' . zen_image_button('button_back.gif', IMAGE_BACK) . '</a>'; ?></td>
      </tr>
    </table></td>
<!-- body_text_eof //-->
  </tr>
</table>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
<br>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>