<?php
/**
 * @package admin
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: DrByte   Modified in v1.6.0 $
 *
 *
 * @TODO - align .flot_chart.flot-x-axis smarter in relation to .flot_chart, and add styling, such as slightly larger font and bold, etc
 * @TODO - expand the functionality to enable hover-points and hover-text describing each point on the graphs
 * @TODO - test on tablet
 * @TODO - test printing and/or emailing from screen, with date-stamp visible near/on the image
 */

  require('includes/application_top.php');
  require('includes/functions/functions_graphs.php');

  $banner_id = (isset($_GET['bID'])) ? (int)$_GET['bID'] : 0;
  $type = (isset($_GET['type']) ? preg_replace('/[^a-z]/', '', $_GET['type']) : '');
  $years_array = array();
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

  if ($banner_id) {
    $banner = $db->Execute("select * from " . TABLE_BANNERS . "
                            where banners_id = '" . (int)$banner_id . "'");

    $years = $db->Execute("select distinct year(banners_history_date) as banner_year
                           from " . TABLE_BANNERS_HISTORY . "
                           where banners_id = '" . (int)$banner_id . "' order by banner_year");
    while (!$years->EOF) {
      $years_array[] = array('id' => $years->fields['banner_year'],
                             'text' => $years->fields['banner_year']);
      $years->MoveNext();
    }
  }
  if (!isset($banner)) {
    $banner = new stdClass();
  }

  // default options for the graphs
  $opts = array('series' => array('lines'=> array('show'=>'true'),
                                  'points'=> array('show'=>'true'),
                                  ),
                'yaxis' => array('tickDecimals' => 0),
                'colors' => array('blue', 'red'),
                );

require('includes/admin_html_head.php');
?>
<link rel="stylesheet" type="text/css" href="includes/template/css/banner_tools.css" />
<!--[if lte IE 8]><script src="includes/template/javascript/flot/excanvas.min.js"></script><![endif]-->
<script src="includes/template/javascript/flot/jquery.flot.min.js"></script>
<script src="includes/template/javascript/flot/jquery.flot.resize.min.js"></script>
</head>
<body>
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<div id="pageWrapper">

  <h1><?php echo HEADING_TITLE ?></h1>

<!-- Graph HTML -->
<div id="graph-wrapper">
  <div class="graph-info">
    <a href="javascript:void(0)" class="visitors">Visitors</a>
    <a href="javascript:void(0)" class="returning">Returning Visitors</a>

    <a href="#" id="bars"><span></span></a>
    <a href="#" id="lines" class="active"><span></span></a>
  </div>

  <div class="graph-container">
    <div id="graph-lines"></div>
    <div id="graph-bars"></div>
  </div>
</div>
<!-- end Graph HTML -->
<script>
$(document).ready(function () {
  var graphData = [{
    // Visits
    data: [ [6, 1300], [7, 1600], [8, 1900], [9, 2100], [10, 2500], [11, 2200], [12, 2000], [13, 1950], [14, 1900], [15, 2000] ],
    color: '#71c73e'
  }, {
    // Returning Visits
    data: [ [6, 500], [7, 600], [8, 550], [9, 600], [10, 800], [11, 900], [12, 800], [13, 850], [14, 830], [15, 1000] ],
    color: '#77b7c5',
    points: { radius: 4, fillColor: '#77b7c5' }
  }
];

  // Lines
  $.plot($('#graph-lines'), graphData, {
    series: {
      points: {
        show: true,
        radius: 5
      },
      lines: {
        show: true
      },
      shadowSize: 0
    },
    grid: {
      color: '#646464',
      borderColor: 'transparent',
      borderWidth: 20,
      hoverable: true
    },
    xaxis: {
      tickColor: 'transparent',
      tickDecimals: 2
    },
    yaxis: {
      tickSize: 1000
    }
  });

  // Bars
  $.plot($('#graph-bars'), graphData, {
    series: {
      bars: {
        show: true,
        barWidth: .9,
        align: 'center'
      },
      shadowSize: 0
    },
    grid: {
      color: '#646464',
      borderColor: 'transparent',
      borderWidth: 20,
      hoverable: true
    },
    xaxis: {
      tickColor: 'transparent',
      tickDecimals: 2
    },
    yaxis: {
      tickSize: 1000
    }
  });

  $('#graph-bars').hide();

  $('#lines').on('click', function (e) {
    $('#bars').removeClass('active');
    $('#graph-bars').fadeOut();
    $(this).addClass('active');
    $('#graph-lines').fadeIn();
    e.preventDefault();
  });

  $('#bars').on('click', function (e) {
    $('#lines').removeClass('active');
    $('#graph-lines').fadeOut();
    $(this).addClass('active');
    $('#graph-bars').fadeIn().removeClass('hidden');
    e.preventDefault();
  });

  function showTooltip(x, y, contents) {
    $('<div id="tooltip">' + contents + '</div>').css({
      top: y - 16,
      left: x + 20
    }).appendTo('body').fadeIn();
  }

  var previousPoint = null;

  $('#graph-lines, #graph-bars').bind('plothover', function (event, pos, item) {
    if (item) {
      if (previousPoint != item.dataIndex) {
        previousPoint = item.dataIndex;
        $('#tooltip').remove();
        var x = item.datapoint[0],
          y = item.datapoint[1];
          showTooltip(item.pageX, item.pageY, y + ' visitors at ' + x + '.00h');
      }
    } else {
      $('#tooltip').remove();
      previousPoint = null;
    }
  });

});
</script>













<!-- body_text //-->
  <?php echo zen_draw_form('year', FILENAME_BANNER_STATISTICS, '', 'get'); ?>
<?php echo zen_hide_session_id() . zen_draw_hidden_field('page', (int)$_GET['page']) . zen_draw_hidden_field('bID', $banner_id); ?>
<?php echo TITLE_TYPE . ' ' . zen_draw_pull_down_menu('type', $type_array, (zen_not_null($type) ? $type : 'daily'), 'onChange="this.form.submit();"'); ?><noscript><input type="submit" value="GO"></noscript><br>
<?php
  switch ($type) {
    case 'yearly': break;
    case 'monthly':
      echo TITLE_YEAR . ' ' . zen_draw_pull_down_menu('year', $years_array, (isset($_GET['year']) ? (int)$_GET['year'] : date('Y')), 'onChange="this.form.submit();"') . '<noscript><input type="submit" value="GO"></noscript>';
      break;
    default:
    case 'daily':
      echo TITLE_MONTH . ' ' . zen_draw_pull_down_menu('month', $months_array, (isset($_GET['month']) ? (int)$_GET['month'] : date('n')), 'onChange="this.form.submit();"') . '<noscript><input type="submit" value="GO"></noscript><br>' . TITLE_YEAR . ' ' . zen_draw_pull_down_menu('year', $years_array, (isset($_GET['year']) ? (int)$_GET['year'] : date('Y')), 'onChange="this.form.submit();"') . '<noscript><input type="submit" value="GO"></noscript>';
      break;
  }
?>
  </form>

  <p>
    <a href="<?php echo zen_admin_href_link(FILENAME_BANNER_MANAGER, 'page=' . (int)$_GET['page'] . '&bID=' . $banner_id);?>"><?php echo zen_image_button('button_back.gif', IMAGE_BACK); ?></a>
  </p>


<?php
  $stats = zen_get_banner_data_yearly($banner_id);
  $data = array(array('label'=>TEXT_BANNERS_BANNER_VIEWS, 'data'=>$stats[0]), array('label'=>TEXT_BANNERS_BANNER_CLICKS, 'data'=>$stats[1]));
  $title = sprintf(TEXT_BANNERS_YEARLY_STATISTICS, $banner->fields['banners_title']);
?>

  <div class="flot-x-axis">
    <div class="flot-tick-label"><?php echo $title; ?></div>
  </div>
  <div id="banner-yearly" class="flot_chart" style="width:600px;height:350px;"></div>
  <script>
    var yData = <?php echo json_encode($data); ?> ;
    var yOptions = <?php echo json_encode(array_merge($opts, array('xaxis'=>array('ticks'=>$stats[3]) ))); ?> ;
    var plot = $("#banner-yearly").plot(yData, yOptions).data("plot");
  </script>

<?php
  $stats = zen_get_banner_data_monthly($banner_id, (isset($_GET['year']) ? (int)$_GET['year'] : ''));
  $data = array(array('label'=>TEXT_BANNERS_BANNER_VIEWS, 'data'=>$stats[0]), array('label'=>TEXT_BANNERS_BANNER_CLICKS, 'data'=>$stats[1]));
  $title = sprintf(TEXT_BANNERS_MONTHLY_STATISTICS, $banner->fields['banners_title'], (isset($_GET['year']) ? (int)$_GET['year'] : date('Y')));
?>

  <div class="flot-x-axis">
    <div class="flot-tick-label"><?php echo $title; ?></div>
  </div>
  <div id="banner-monthly" class="flot_chart" style="width:600px;height:350px;"></div>

  <script>
    var mData = <?php echo json_encode($data); ?> ;
    var mOptions = <?php echo json_encode(array_merge($opts, array('xaxis'=>array('ticks'=>$stats[3])))); ?> ;
    var plot = $("#banner-monthly").plot(mData, mOptions).data("plot");
  </script>

<?php
  $stats = zen_get_banner_data_daily($banner_id, (isset($_GET['year']) ? (int)$_GET['year'] : ''), (isset($_GET['month']) ? (int)$_GET['month'] : ''));
  $data = array(array('label'=>TEXT_BANNERS_BANNER_VIEWS, 'data'=>$stats[0]), array('label'=>TEXT_BANNERS_BANNER_CLICKS, 'data'=>$stats[1]));
  $title = sprintf(TEXT_BANNERS_DAILY_STATISTICS, $banner->fields['banners_title'], strftime('%B', mktime(0,0,0,(isset($_GET['month']) ? (int)$_GET['month'] : date('n')))), (isset($_GET['year']) ? (int)$_GET['year'] : date('Y')));
?>

  <div class="flot-x-axis">
    <div class="flot-tick-label"><?php echo $title; ?></div>
  </div>
  <div id="banner-daily" class="flot_chart" style="width:600px;height:350px;"></div>

  <script>
    var dData = <?php echo json_encode($data); ?> ;
    var dOptions = <?php echo json_encode(array_merge($opts, array('xaxis'=>array('ticks'=>sizeof($stats[0]),'tickDecimals' => 0)))); ?> ;
    var plot = $("#banner-daily").plot(dData, dOptions).data("plot");
  </script>









<?php if (FALSE) { ?>
  <div class="banner-statistics-source-data">
    <table caption="<?php echo TABLE_HEADING_SOURCE; ?>">
      <tr class="headingRow dataTableHeadingRow">
        <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_SOURCE; ?></th>
        <th class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_VIEWS; ?></th>
        <th class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_CLICKS; ?></th>
      </tr>
  <?php
      for ($i=0, $n=sizeof($stats[2]); $i<$n; $i++) {
  ?>
        <tr class="dataTableRow">
          <td class="dataTableContent"><?php echo $stats[2][$i][0]; ?></td>
          <td class="dataTableContent" align="right"><?php echo $stats[2][$i][1]; ?></td>
          <td class="dataTableContent" align="right"><?php echo $stats[2][$i][2]; ?></td>
        </tr>
  <?php
      }
  ?>
    </table>
  </div>
<?php } ?>








  <p>
    <a href="<?php echo zen_admin_href_link(FILENAME_BANNER_MANAGER, 'page=' . (int)$_GET['page'] . '&bID=' . (int)$_GET['bID']);?>"><?php echo zen_image_button('button_back.gif', IMAGE_BACK); ?></a>
  </p>
</div>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
<br>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>




