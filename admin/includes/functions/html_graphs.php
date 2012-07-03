<?php
/**
 * @package admin
 * @copyright Copyright 2003-2011 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: html_graphs.php 18695 2011-05-04 05:24:19Z drbyte $
 */

////
// calls routines to initialize defaults, set up table
// print data, and close table.
  function html_graph($names, $values, $bars, $vals, $dvalues = 0, $dbars = 0) {
// set the error level on entry and exit so as not to interfear with anyone elses error checking.
    $er = error_reporting(1);

// set the values that the user didn't
    $vals = hv_graph_defaults($vals);
    $html_graph_string = start_graph($vals, $names);

    if ($vals['type'] == 0) {
      $html_graph_string .= horizontal_graph($names, $values, $bars, $vals);
    } elseif ($vals['type'] == 1) {
      $html_graph_string .= vertical_graph($names, $values, $bars, $vals);
    } elseif ($vals['type'] == 2) {
      $html_graph_string .= double_horizontal_graph($names, $values, $bars, $vals, $dvalues, $dbars);
    } elseif ($vals['type'] == 3) {
      $html_graph_string .= double_vertical_graph($names, $values, $bars, $vals, $dvalues, $dbars);
    }

    $html_graph_string .= end_graph();

// Set the error level back to where it was.
    error_reporting($er);

    return $html_graph_string;
  }

////
// sets up the $vals array by initializing all values to null. Used to avoid
// warnings from error_reporting being set high. This routine only needs to be
// called if you are worried about using uninitialized variables.
  function html_graph_init() {
    $vals = array('vlabel'=>'',
                  'hlabel'=>'',
                  'type'=>'',
                  'cellpadding'=>'',
                  'cellspacing'=>'',
                  'border'=>'',
                  'width'=>'',
                  'background'=>'',
                  'vfcolor'=>'',
                  'hfcolor'=>'',
                  'vbgcolor'=>'',
                  'hbgcolor'=>'',
                  'vfstyle'=>'',
                  'hfstyle'=>'',
                  'noshowvals'=>'',
                  'scale'=>'',
                  'namebgcolor'=>'',
                  'valuebgcolor'=>'',
                  'namefcolor'=>'',
                  'valuefcolor'=>'',
                  'namefstyle'=>'',
                  'valuefstyle'=>'',
                  'doublefcolor'=>'');

    return($vals);
  }

////
// prints out the table header and graph labels
  function start_graph($vals, $names) {
    $start_graph_string = '<table cellpadding="' . $vals['cellpadding'] . '" cellspacing="' . $vals['cellspacing'] . '" border="' . $vals['border'] . '"';

    if ($vals['width'] != 0) $start_graph_string .= ' width="' . $vals['width'] . '"';
    if ($vals['background']) $start_graph_string .= ' background="' . $vals['background'] . '"';

    $start_graph_string .= '>' . "\n";

    if ( ($vals['vlabel']) || ($vals['hlabel']) ) {
      if ( ($vals['type'] == 0) || ($vals['type'] == 2) ) {
// horizontal chart
        $rowspan = sizeof($names) + 1;
        $colspan = 3;
      } elseif ( ($vals['type'] == 1) || ($vals['type'] == 3) ) {
// vertical chart
        $rowspan = 3;
        $colspan = sizeof($names) + 1;
      }

      $start_graph_string .= '  <tr>' . "\n" .
                             '    <td align="center" valign="center"';

// if a background was choosen don't print cell BGCOLOR
      if (!$vals['background']) $start_graph_string .= ' bgcolor="' . $vals['hbgcolor'] . '"';

      $start_graph_string .= ' colspan="' . $colspan . '"><font color="' . $vals['hfcolor'] . '" style="' . $vals['hfstyle'] . '"><b>' . $vals['hlabel'] . '</b></font></td>' . "\n" .
                             '  </tr>' . "\n" .
                             '  <tr>' . "\n" .
                             '    <td align="center" valign="center"';

// if a background was choosen don't print cell BGCOLOR
      if (!$vals['background']) $start_graph_string .= ' bgcolor="' . $vals['vbgcolor'] . '"';

      $start_graph_string .=  ' rowspan="' . $rowspan . '"><font color="' . $vals['vfcolor'] . '" style="' . $vals['vfstyle'] . '"><b>' . $vals['vlabel'] . '</b></font></td>' . "\n" .
                              '  </tr>' . "\n";
    }

    return $start_graph_string;
  }

////
// prints out the table footer
  function end_graph() {
    return '</table>' . "\n";
  }

////
// sets the default values for the $vals array
  function hv_graph_defaults($vals) {
    if (!$vals['vfcolor']) $vals['vfcolor'] = '#000000';
    if (!$vals['hfcolor']) $vals['hfcolor'] = '#000000';
    if (!$vals['vbgcolor']) $vals['vbgcolor'] = '#FFFFFF';
    if (!$vals['hbgcolor']) $vals['hbgcolor'] = '#FFFFFF';
    if (!$vals['cellpadding']) $vals['cellpadding'] = '0';
    if (!$vals['cellspacing']) $vals['cellspacing'] = '0';
    if (!$vals['border']) $vals['border'] = '0';
    if (!$vals['scale']) $vals['scale'] = '1';
    if (!$vals['namebgcolor']) $vals['namebgcolor'] = '#FFFFFF';
    if (!$vals['valuebgcolor']) $vals['valuebgcolor'] = '#FFFFFF';
    if (!$vals['namefcolor']) $vals['namefcolor'] = '#000000';
    if (!$vals['valuefcolor']) $vals['valuefcolor'] = '#000000';
    if (!$vals['doublefcolor']) $vals['doublefcolor'] = '#886666';

    return $vals;
  }

////
// prints out the actual data for the horizontal chart
  function horizontal_graph($names, $values, $bars, $vals) {
    $horizontal_graph_string = '';
    for($i = 0, $n = sizeof($values); $i < $n; $i++) {
      $horizontal_graph_string .= '  <tr>' . "\n" .
                                  '    <td align="right"';
// if a background was choosen don't print cell BGCOLOR
      if (!$vals['background']) $horizontal_graph_string .= ' bgcolor="' . $vals['namebgcolor'] . '"';

      $horizontal_graph_string .= '><font size="-1" color="' . $vals['namefcolor'] . '" style="' . $vals['namefstyle'] . '">' . $names[$i] . '</font></td>' . "\n" .
                                  '    <td';

// if a background was choosen don't print cell BGCOLOR
      if (!$vals['background']) $horizontal_graph_string .= ' bgcolor="' . $vals['valuebgcolor'] . '"';

      $horizontal_graph_string .= '>';

// decide if the value in bar is a color code or image.
      if (preg_match('/^#/', $bars[$i])) {
        $horizontal_graph_string .= '<table cellpadding="0" cellspacing="0" bgcolor="' . $bars[$i] . '" width="' . ($values[$i] * $vals['scale']) . '">' . "\n" .
                                    '  <tr>' . "\n" .
                                    '    <td>&nbsp;</td>' . "\n" .
                                    '  </tr>' . "\n" .
                                    '</table>';
      } else {
        $horizontal_graph_string .= '<img src="' . $bars[$i] . '" height="10" width="' . ($values[$i] * $vals['scale']) . '">';
      }

      if (!$vals['noshowvals']) {
        $horizontal_graph_string .= '<i><font size="-2" color="' . $vals['valuefcolor'] . '" style="' . $vals['valuefstyle'] . '">(' . $values[$i] . ')</font></i>';
      }

      $horizontal_graph_string .= '</td>' . "\n" .
                                  '  </tr>' . "\n";
    } // endfor

    return $horizontal_graph_string;
  }

////
// prints out the actual data for the vertical chart
  function vertical_graph($names, $values, $bars, $vals) {
    $vertical_graph_string = '  <tr>' . "\n";

    for ($i = 0, $n = sizeof($values); $i < $n; $i++) {
      $vertical_graph_string .= '    <td align="center" valign="bottom"';

// if a background was choosen don't print cell BGCOLOR
      if (!$vals['background']) $vertical_graph_string .= ' bgcolor="' . $vals['valuebgcolor'] . '"';

      $vertical_graph_string .= '>';

      if (!$vals['noshowvals']) {
        $vertical_graph_string .= '<i><font size="-2" color="' . $vals['valuefcolor'] . '" style="' . $vals['valuefstyle'] . '">(' . $values[$i] . ')</font></i><br>';
      }

      $vertical_graph_string .= '<img src="' . $bars[$i] . '" width="5" height="';

// values of zero are displayed wrong because a image height of zero
// gives a strange behavior in Netscape. For this reason the height
// is set at 1 pixel if the value is zero. - Jan Diepens
      if ($values[$i] != 0) {
        $vertical_graph_string .= $values[$i] * $vals['scale'];
      } else {
        $vertical_graph_string .= '1';
      }

      $vertical_graph_string .= '"></td>' . "\n";
    } // endfor

    $vertical_graph_string .= '  </tr>' . "\n" .
                              '  <tr>' . "\n";

    for ($i = 0, $n = sizeof($values); $i < $n; $i++) {
      $vertical_graph_string .= '    <td align="center" valign="top"';

// if a background was choosen don't print cell BGCOLOR
      if (!$vals['background']) $vertical_graph_string .= ' bgcolor="' . $vals['namebgcolor'] . '"';

      $vertical_graph_string .= '><font size="-1" color="' . $vals['namefcolor'] . '" style="' . $vals['namefstyle'] . '">' . $names[$i] . '</font></td>' . "\n";
    } // endfor

    $vertical_graph_string .= '  </tr>' . "\n";

    return $vertical_graph_string;
  }

////
// prints out the actual data for the double horizontal chart
  function double_horizontal_graph($names, $values, $bars, $vals, $dvalues, $dbars) {
    $double_horizontal_graph_string = '';
    for($i = 0, $n = sizeof($values); $i < $n; $i++) {
      $double_horizontal_graph_string .= '  <tr>' . "\n" .
                                        '    <td align="right"';

// if a background was choosen don't print cell BGCOLOR
      if (!$vals['background']) $double_horizontal_graph_string .= ' bgcolor="' . $vals['namebgcolor'] . '"';

      $double_horizontal_graph_string .= '><font size="-1" color="' . $vals['namefcolor'] . '" style="' . $vals['namefstyle'] . '">' . $names[$i] . '</font></td>' . "\n" .
                                         '    <td';

// if a background was choosen don't print cell BGCOLOR
      if (!$vals['background']) $double_horizontal_graph_string .= ' bgcolor="' . $vals['valuebgcolor'] . '"';

      $double_horizontal_graph_string .= '><table align="left" cellpadding="0" cellspacing="0" width="' . ($dvalues[$i] * $vals['scale']) . '">' . "\n" .
                                         '      <tr>' . "\n" .
                                         '        <td';

// set background to a color if it starts with # or an image otherwise.
      if (preg_match('/^#/', $dbars[$i])) {
        $double_horizontal_graph_string .= ' bgcolor="' . $dbars[$i] . '">';
      } else {
        $double_horizontal_graph_string .= ' background="' . $dbars[$i] . '">';
      }

      $double_horizontal_graph_string .= '<nowrap>';

// decide if the value in bar is a color code or image.
      if (preg_match('/^#/', $bars[$i])) {
        $double_horizontal_graph_string .= '<table align="left" cellpadding="0" cellspacing="0" bgcolor="' . $bars[$i] . '" width="' . ($values[$i] * $vals['scale']) . '">' . "\n" .
                                           '  <tr>' . "\n" .
                                           '    <td>&nbsp;</td>' . "\n" .
                                           '  </tr>' . "\n" .
                                           '</table>';
      } else {
        $double_horizontal_graph_string .= '<img src="' . $bars[$i] . '" height="10" width="' . ($values[$i] * $vals['scale']) . '">';
      }

      if (!$vals['noshowvals']) {
        $double_horizontal_graph_string .= '<i><font size="-3" color="' . $vals['valuefcolor'] . '" style="' . $vals['valuefstyle'] . '">(' . $values[$i] . ')</font></i>';
      }

      $double_horizontal_graph_string .= '</nowrap></td>' . "\n" .
                                         '        </tr>' . "\n" .
                                         '      </table>';

      if (!$vals['noshowvals']) {
        $double_horizontal_graph_string .= '<i><font size="-3" color="' . $vals['doublefcolor'] . '" style="' . $vals['valuefstyle'] . '">(' . $dvalues[$i] . ')</font></i>';
      }

      $double_horizontal_graph_string .= '</td>' . "\n" .
                                         '  </tr>' . "\n";
    } // endfor

    return $double_horizontal_graph_string;
  }

////
// prints out the actual data for the double vertical chart
  function double_vertical_graph($names, $values, $bars, $vals, $dvalues, $dbars) {
    $double_vertical_graph_string = '  <tr>' . "\n";
    for ($i = 0, $n = sizeof($values); $i < $n; $i++) {
      $double_vertical_graph_string .= '    <td align="center" valign="bottom"';

// if a background was choosen don't print cell BGCOLOR
      if (!$vals['background']) $double_vertical_graph_string .= ' bgcolor="' . $vals['valuebgcolor'] . '"';

      $double_vertical_graph_string .= '><table>' . "\n" .
                                       '      <tr>' . "\n" .
                                       '        <td align="center" valign="bottom"';

// if a background was choosen don't print cell BGCOLOR
      if (!$vals['background']) $double_vertical_graph_string .= ' bgcolor="' . $vals['valuebgcolor'] . '"';

      $double_vertical_graph_string .= '>';

      if (!$vals['noshowvals'] && $values[$i]) {
        $double_vertical_graph_string .= '<i><font size="-2" color="' . $vals['valuefcolor'] . '" style="' . $vals['valuefstyle'] . '">(' . $values[$i] . ')</font></i><br>';
      }

      $double_vertical_graph_string .= '<img src="' . $bars[$i] . '" width="10" height="';

      if ($values[$i] != 0) {
        $double_vertical_graph_string .= $values[$i] * $vals['scale'];
      } else {
        $double_vertical_graph_string .= '1';
      }

      $double_vertical_graph_string .= '"></td>' . "\n" .
                                       '        <td align="center" valign="bottom"';

// if a background was choosen don't print cell BGCOLOR
      if (!$vals['background']) $double_vertical_graph_string .= ' bgcolor="' . $vals['valuebgcolor'] . '"';

      $double_vertical_graph_string .= '>';

      if (!$vals['noshowvals'] && $dvalues[$i]) {
        $double_vertical_graph_string .= '<i><font size="-2" color="' . $vals['doublefcolor'] . '" style="' . $vals['valuefstyle'] . '">(' . $dvalues[$i] . ')</font></i><br>';
      }

      $double_vertical_graph_string .= '<img src="' . $dbars[$i] . '" width="10" height="';

      if ($dvalues[$i] != 0) {
        $double_vertical_graph_string .= $dvalues[$i] * $vals['scale'];
      } else {
        $double_vertical_graph_string .= '1';
      }

      $double_vertical_graph_string .= '"></td>' . "\n" .
                                       '      </tr>' . "\n" .
                                       '    </table></td>' . "\n";
    } // endfor

    $double_vertical_graph_string .= '  </tr>' . "\n" .
                                     '  <tr>' . "\n";

    for ($i = 0, $n = sizeof($values); $i < $n; $i++) {
      $double_vertical_graph_string .= '    <td align="center" valign="top"';

// if a background was choosen don't print cell BGCOLOR
      if (!$vals['background']) $double_vertical_graph_string .= ' bgcolor="' . $vals['namebgcolor'] . '"';

      $double_vertical_graph_string .= '><font size="-1" color="' . $vals['namefcolor'] . '" style="' . $vals['namefstyle'] . '">' . $names[$i] . '</font></td>' . "\n";
    } // endfor

    $double_vertical_graph_string .= '  </tr>' . "\n";

    return $double_vertical_graph_string;
  }

////
// draws a double vertical bar graph for the banner views vs clicks statistics
  function zen_banner_graph_infoBox($banner_id, $days) {
    global $db;
    $names = array();
    $values = array();
    $dvalues = array();

    $banner_stats = $db->Execute("select dayofmonth(banners_history_date) as name,
	                                     banners_shown as value, banners_clicked as dvalue
										 from " . TABLE_BANNERS_HISTORY . "
										 where banners_id = '" . (int)$banner_id . "'
										 and to_days(now()) - to_days(banners_history_date) < " . zen_db_input($days) . "
										 order by banners_history_date");

    while (!$banner_stats->EOF) {
      $names[] = $banner_stats->fields['name'];
      $values[] = $banner_stats->fields['value'];
      $dvalues[] = $banner_stats->fields['dvalue'];
	  $banner_stats->MoveNext();
    }
    $largest = @max($values);

    $bars = array();
    $dbars = array();
    for ($i = 0, $n = sizeof($values); $i < $n; $i++) {
      $bars[$i] = DIR_WS_IMAGES . 'graph_hbar_blue.gif';
      $dbars[$i] = DIR_WS_IMAGES . 'graph_hbar_red.gif';
    }

    $graph_vals = @array('vlabel'=>TEXT_BANNERS_DATA,
                        'hlabel'=>TEXT_BANNERS_LAST_3_DAYS,
                        'type'=>'3',
                        'cellpadding'=>'',
                        'cellspacing'=>'1',
                        'border'=>'',
                        'width'=>'',
                        'vfcolor'=>'#ffffff',
                        'hfcolor'=>'#ffffff',
                        'vbgcolor'=>'#81a2b6',
                        'hbgcolor'=>'#81a2b6',
                        'vfstyle'=>'Verdana, Arial, Helvetica',
                        'hfstyle'=>'Verdana, Arial, Helvetica',
                        'scale'=>100/$largest,
                        'namebgcolor'=>'#f3f5fe',
                        'valuebgcolor'=>'#f3f5fe',
                        'namefcolor'=>'',
                        'valuefcolor'=>'#0000d0',
                        'namefstyle'=>'Verdana, Arial, Helvetica',
                        'valuefstyle'=>'',
                        'doublefcolor'=>'#ff7339');

    return html_graph($names, $values, $bars, $graph_vals, $dvalues, $dbars);
  }

////
// draws a double vertical bar graph for the banner views vs clicks statistics
  function zen_banner_graph_yearly($banner_id) {
    global $db, $banner, $_GET;

    $banner_stats = $db->Execute("select year(banners_history_date) as year,
	                                     sum(banners_shown) as value, sum(banners_clicked) as dvalue
										 from " . TABLE_BANNERS_HISTORY . "
										 where banners_id = '" . (int)$banner_id . "'
										 group by year(banners_history_date)");

    while (!$banner_stats->EOF) {
      $names[] = $banner_stats->fields['year'];
      $values[] = (($banner_stats->fields['value']) ? $banner_stats->fields['value'] : '0');
      $dvalues[] = (($banner_stats->fields['dvalue']) ? $banner_stats->fields['dvalue'] : '0');
	  $banner_stats->MoveNext();
    }

    $largest = @max($values);

    $bars = array();
    $dbars = array();
    for ($i = 0, $n = sizeof($values); $i < $n; $i++) {
      $bars[$i] = DIR_WS_IMAGES . 'graph_hbar_blue.gif';
      $dbars[$i] = DIR_WS_IMAGES . 'graph_hbar_red.gif';
    }

    $graph_vals = @array('vlabel'=>TEXT_BANNERS_DATA,
                        'hlabel'=>sprintf(TEXT_BANNERS_YEARLY_STATISTICS, $banner->fields['banners_title']),
                        'type'=>'3',
                        'cellpadding'=>'',
                        'cellspacing'=>'1',
                        'border'=>'',
                        'width'=>'',
                        'vfcolor'=>'#ffffff',
                        'hfcolor'=>'#ffffff',
                        'vbgcolor'=>'#81a2b6',
                        'hbgcolor'=>'#81a2b6',
                        'vfstyle'=>'Verdana, Arial, Helvetica',
                        'hfstyle'=>'Verdana, Arial, Helvetica',
                        'scale'=>100/$largest,
                        'namebgcolor'=>'#f3f5fe',
                        'valuebgcolor'=>'#f3f5fe',
                        'namefcolor'=>'',
                        'valuefcolor'=>'#0000d0',
                        'namefstyle'=>'Verdana, Arial, Helvetica',
                        'valuefstyle'=>'',
                        'doublefcolor'=>'#ff7339');

    return html_graph($names, $values, $bars, $graph_vals, $dvalues, $dbars);
  }

////
// draws a double vertical bar graph for the banner views vs clicks statistics
  function zen_banner_graph_monthly($banner_id) {
    global $db, $banner, $_GET;

    $year = (($_GET['year']) ? $_GET['year'] : date('Y'));

    for ($i=1; $i<13; $i++) {
      $names[] = strftime('%b', mktime(0,0,0,$i));
      $values[] = '0';
      $dvalues[] = '0';
    }

    $banner_stats = $db->Execute("select month(banners_history_date) as banner_month, sum(banners_shown) as value,
	                              sum(banners_clicked) as dvalue
								  from " . TABLE_BANNERS_HISTORY . "
								  where banners_id = '" . (int)$banner_id . "'
								  and year(banners_history_date) = '" . zen_db_input($year) . "'
								  group by month(banners_history_date)");

    while (!$banner_stats->EOF) {
      $names[($banner_stats->fields['banner_month']-1)] = strftime('%b', mktime(0,0,0,$banner_stats->fields['banner_month']));
      $values[($banner_stats->fields['banner_month']-1)] = (($banner_stats->fields['value']) ? $banner_stats->fields['value'] : '0');
      $dvalues[($banner_stats->fields['banner_month']-1)] = (($banner_stats->fields['dvalue']) ? $banner_stats->fields['dvalue'] : '0');
	  $banner_stats->MoveNext();
    }

    $largest = @max($values);

    $bars = array();
    $dbars = array();
    for ($i = 0, $n = sizeof($values); $i < $n; $i++) {
      $bars[$i] = DIR_WS_IMAGES . 'graph_hbar_blue.gif';
      $dbars[$i] = DIR_WS_IMAGES . 'graph_hbar_red.gif';
    }

    $graph_vals = @array('vlabel'=>TEXT_BANNERS_DATA,
                        'hlabel'=>sprintf(TEXT_BANNERS_MONTHLY_STATISTICS, $banner->fields['banners_title'], date('Y')),
                        'type'=>'3',
                        'cellpadding'=>'',
                        'cellspacing'=>'1',
                        'border'=>'',
                        'width'=>'',
                        'vfcolor'=>'#ffffff',
                        'hfcolor'=>'#ffffff',
                        'vbgcolor'=>'#81a2b6',
                        'hbgcolor'=>'#81a2b6',
                        'vfstyle'=>'Verdana, Arial, Helvetica',
                        'hfstyle'=>'Verdana, Arial, Helvetica',
                        'scale'=>100/$largest,
                        'namebgcolor'=>'#f3f5fe',
                        'valuebgcolor'=>'#f3f5fe',
                        'namefcolor'=>'',
                        'valuefcolor'=>'#0000d0',
                        'namefstyle'=>'Verdana, Arial, Helvetica',
                        'valuefstyle'=>'',
                        'doublefcolor'=>'#ff7339');

    return html_graph($names, $values, $bars, $graph_vals, $dvalues, $dbars);
  }

////
// draws a double vertical bar graph for the banner views vs clicks statistics
  function zen_banner_graph_daily($banner_id) {
    global $db, $banner, $_GET;

    $year = (isset($_GET['year']) ? $_GET['year'] : date('Y'));
    $month = (isset($_GET['month']) ? $_GET['month'] : date('n'));

    $days = (date('t', mktime(0,0,0,$month))+1);
    $stats = array();
    for ($i=1; $i<$days; $i++) {
      $names[] = $i;
      $values[] = '0';
      $dvalues[] = '0';
    }

    $banner_stats = $db->Execute("select dayofmonth(banners_history_date) as banner_day,
	                                     banners_shown as value, banners_clicked as dvalue
										 from " . TABLE_BANNERS_HISTORY . "
										 where banners_id = '" . (int)$banner_id . "'
										 and month(banners_history_date) = '" . zen_db_input($month) . "'
										 and year(banners_history_date) = '" . zen_db_input($year) . "'");

    while (!$banner_stats->EOF) {
      $names[($banner_stats->fields['banner_day']-1)] = $banner_stats->fields['banner_day'];
      $values[($banner_stats->fields['banner_day']-1)] = (($banner_stats->fields['value']) ? $banner_stats->fields['value'] : '0');
      $dvalues[($banner_stats->fields['banner_day']-1)] = (($banner_stats->fields['dvalue']) ? $banner_stats->fields['dvalue'] : '0');
	  $banner_stats->MoveNext();
    }

    $largest = @max($values);

    $bars = array();
    $dbars = array();
    for ($i = 0, $n = sizeof($values); $i < $n; $i++) {
      $bars[$i] = DIR_WS_IMAGES . 'graph_hbar_blue.gif';
      $dbars[$i] = DIR_WS_IMAGES . 'graph_hbar_red.gif';
    }

    $graph_vals = @array('vlabel'=>TEXT_BANNERS_DATA,
                        'hlabel'=>sprintf(TEXT_BANNERS_DAILY_STATISTICS, $banner->fields['banners_title'], strftime('%B', mktime(0,0,0,$month)), $year),
                        'type'=>'3',
                        'cellpadding'=>'',
                        'cellspacing'=>'1',
                        'border'=>'',
                        'width'=>'',
                        'vfcolor'=>'#ffffff',
                        'hfcolor'=>'#ffffff',
                        'vbgcolor'=>'#81a2b6',
                        'hbgcolor'=>'#81a2b6',
                        'vfstyle'=>'Verdana, Arial, Helvetica',
                        'hfstyle'=>'Verdana, Arial, Helvetica',
                        'scale'=>100/$largest,
                        'namebgcolor'=>'#f3f5fe',
                        'valuebgcolor'=>'#f3f5fe',
                        'namefcolor'=>'',
                        'valuefcolor'=>'#0000d0',
                        'namefstyle'=>'Verdana, Arial, Helvetica',
                        'valuefstyle'=>'',
                        'doublefcolor'=>'#ff7339');

    return html_graph($names, $values, $bars, $graph_vals, $dvalues, $dbars);
  }
?>
