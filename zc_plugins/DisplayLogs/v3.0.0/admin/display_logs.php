<?php
// -----
// Part of the "Display Logs" plugin for Zen Cart v1.5.7 or later
//
// Copyright (c) 2012-2020, Vinos de Frutas Tropicales (lat9)
//

// -----
// Functions that gather the log-related files and provide the ascending/descending sort thereof.
//
function sortLogDateAsc($a, $b)
{
    if ($a['mtime'] == $b['mtime']) return 0;
    return ($a['mtime'] < $b['mtime']) ? -1 : 1;
}
function sortLogDateDesc($a, $b)
{
    if ($a['mtime'] == $b['mtime']) return 0;
    return ($a['mtime'] > $b['mtime']) ? -1 : 1;
}
function sortLogSizeAsc($a, $b)
{
    if ($a['filesize'] == $b['filesize']) return 0;
    return ($a['filesize'] < $b['filesize']) ? -1 : 1;
}
function sortLogSizeDesc($a, $b)
{
    if ($a['filesize'] == $b['filesize']) return 0;
    return ($a['filesize'] > $b['filesize']) ? -1 : 1;
}

// -----
// Start main processing ...
//
require 'includes/application_top.php';

// -----
// If debug-logs-only has been selected, display only those files.  If multiple file prefixes are
// to be either included or excluded, wrap that value with parenthese to make preg_match "happy".
//
if (isset($_GET['debug_only'])) {
    $files_to_match = 'myDEBUG-(adm-)?[0-9]+-[0-9]+-[0-9]+((-deprecated)|(-warning)|(-error))?';
    $files_to_exclude = '';
} else {
    $files_to_match = str_replace(' ', '', DISPLAY_LOGS_INCLUDED_FILES);
    if (strpos($files_to_match, '|') !== false) {
        $files_to_match = "($files_to_match)";
    }
    $files_to_match .= '.*';

    $files_to_exclude = str_replace(' ', '', DISPLAY_LOGS_EXCLUDED_FILES);
    if (strpos($files_to_exclude, '|') !== false) {
        $files_to_exclude = "($files_to_exclude)";
    }
    if ($files_to_exclude != '') {
        $files_to_exclude .= '.*';
    }
}

// -----
// Determine (and properly default) the number of log files to be displayed.
//
$max_logs_to_display = (int)DISPLAY_LOGS_MAX_DISPLAY;
if ($max_logs_to_display < 1) {
    $max_logs_to_display = 20;
}

// -----
// Gather the current log files.
//
$logFiles = array();
foreach (array (DIR_FS_LOGS, DIR_FS_SQL_CACHE, DIR_FS_CATALOG . '/includes/modules/payment/paypal/logs') as $logFolder) {
    $logFolder = rtrim($logFolder, '/');
    $dir = @dir($logFolder);
    if ($dir != NULL) {
        while ($file = $dir->read()) {
            if ( ($file != '.') && ($file != '..') && substr($file, 0, 1) != '.') {
                if (preg_match('/^' . $files_to_match . '\.log$/', $file)) {
                    if ($files_to_exclude == '' || !preg_match('/^' . $files_to_exclude . '\.log$/', $file)) {
                        $hash = sha1($logFolder . '/' . $file);
                        $logFiles[$hash] = array (
                            'name'  => $logFolder . '/' . $file,
                            'mtime' => filemtime($logFolder . '/' . $file),
                            'filesize' => filesize($logFolder . '/' . $file)
                        );
                    }
                }
            }
        }
        $dir->close();
        unset($dir);
    }
}

// -----
// Determine the current sort-method chosen by the admin user, sorting the list of matching
// files based on that choice.
//
$sort = 'date_d';
$sort_description = TEXT_MOST_RECENT;
$sort_function = 'sortLogDateDesc';
if (isset($_GET['sort'])) {
    $sort = $_GET['sort'];
    switch ($sort) {
        case 'date_a':
            $sort_description = TEXT_OLDEST;
            $sort_function = 'sortLogDateAsc';
            break;
        case 'size_a':
            $sort_description = TEXT_SMALLEST;
            $sort_function = 'sortLogSizeAsc';
            break;
        case 'size_d':
            $sort_description = TEXT_LARGEST;
            $sort_function = 'sortLogSizeDesc';
            break;
        default:
            $sort = 'date_a';
            break;
    }
}
uasort($logFiles, $sort_function);
reset($logFiles);

// -----
// If more files were found than will be displayed, free up the memory associated with
// those files' entries by popping them off the end of the array.
//
$numLogFiles = count($logFiles);
if ($numLogFiles > $max_logs_to_display) {
    for ($i = 0, $n = $numLogFiles - $max_logs_to_display; $i < $n; $i++) {
        array_pop ($logFiles);
    }
}

// -----
// If any file delete requests have been made, process them first.
//
$action = (isset($_GET['action'])) ? $_GET['action'] : '';
if (zen_not_null($action) && $action == 'delete') {
    if (isset($_POST['dList']) && count($_POST['dList']) != 0) {
        $numFiles = count($_POST['dList']);
        $filesDeleted = 0;
        foreach ($_POST['dList'] as $currentHash => $value) {
            if (array_key_exists($currentHash, $logFiles)) {
                if (is_writeable($logFiles[$currentHash]['name'])) {
                    zen_remove($logFiles[$currentHash]['name']);
                    $filesDeleted++;
                }
            }
        }
        if ($filesDeleted == $numFiles) {
            $messageStack->add_session(sprintf(SUCCESS_FILES_DELETED, $numFiles), 'success');
        } else {
            $messageStack->add_session(sprintf(WARNING_SOME_FILES_DELETED, $filesDeleted, $numFiles), 'warning');
        }
    } else {
        $messageStack->add_session(WARNING_NO_FILES_SELECTED, 'warning');
    }
    zen_redirect (zen_href_link(FILENAME_DISPLAY_LOGS, zen_get_all_get_params(array('action'))));
}

if (isset($_GET['fID'])) {
    if (array_key_exists($_GET['fID'], $logFiles)) {
        $getFile = $_GET['fID'];
    } else {
        unset($_GET['fID']);
        $getFile = key($logFiles);
    }
} elseif (count($logFiles) != 0) {
    $getFile = key($logFiles);
} else {
    $getFile = '';
}

// -----
// "Sanitize" the maximum file-size to be fully read, defaulting if the configured value is not a positive integer.
//
$max_log_file_size = (int)DISPLAY_LOGS_MAX_FILE_SIZE;
if ($max_log_file_size < 1) {
    $max_log_file_size = 80000;
}
?>
    <!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
    <html <?php echo HTML_PARAMS; ?>>
    <head>
        <?php require DIR_WS_INCLUDES . 'admin_html_head.php'; ?>
        <style type="text/css">
            <!--
            #theButtons { padding-top: 10px; margin-top: 10px; border-top: 1px solid black; }
            #dButtons, #dSpace { width: 50%; }
            #dAll { float: right; padding-right: 20px; }
            #dSel { float: right; }
            #fContents { overflow: auto; max-height: <?php echo 23 * $max_logs_to_display; ?>px; }
            #contentsOuter { vertical-align: top; }
            .bigfile { font-weight: bold; color: red; }
            -->
        </style>
        <script type="text/javascript">
            <!--
            function buttonCheck(whichButton) {
                var submitOK = false;
                var elements = document.getElementsByClassName('cBox');
                var n = elements.length;
                if (whichButton == 'all') {
                    submitOK = confirm('<?php echo JS_MESSAGE_DELETE_ALL_CONFIRM; ?>');
                    if (submitOK) {
                        for (var i = 0; i < n; i++) {
                            elements[i].checked = true;
                        }
                    }
                } else {
                    var selected = 0;
                    for (var i = 0; i < n; i++) {
                        if (elements[i].checked) selected++;
                    }
                    if (selected > 0) {
                        submitOK = confirm('<?php echo JS_MESSAGE_DELETE_SELECTED_CONFIRM; ?>');
                    } else {
                        alert('<?php echo WARNING_NO_FILES_SELECTED; ?>');
                    }
                }
                return submitOK;
            }
            // -->
        </script>
    </head>
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
                        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
                                    <td class="pageHeading" align="right"><?php echo zen_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
                                </tr>

                                <tr>
                                    <td class="main"><?php echo ((substr(HTTP_SERVER, 0, 5) != 'https') ? WARNING_NOT_SECURE : '') . sprintf(TEXT_INSTRUCTIONS, $max_log_file_size, $sort_description, (($numLogFiles > $max_logs_to_display) ? $max_logs_to_display : $numLogFiles), $numLogFiles, $files_to_match, $files_to_exclude); ?></td>
                                    <td class="main" align="right"><?php echo zen_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
                                </tr>

                                <tr colspan="2">
                                    <td><?php echo zen_draw_form('logs_form', FILENAME_DISPLAY_LOGS, '', 'get') . '<b>' . DISPLAY_DEBUG_LOGS_ONLY . '</b>&nbsp;&nbsp;' . zen_draw_checkbox_field('debug_only', 'on', (isset($_GET['debug_only'])) ? true : false, '', 'onclick="this.form.submit();"') . zen_draw_hidden_field('sort', $sort) . '</form>'; ?></td>
                                </tr>

                            </table></td>
                    </tr>
                </table></td>
        </tr>

        <tr>
            <td>
                <form id="dlFormID" name="dlForm" action="<?php echo zen_href_link(FILENAME_DISPLAY_LOGS, zen_get_all_get_params (array('action')) . 'action=delete', 'NONSSL'); ?>" method="post"><?php echo zen_draw_hidden_field('securityToken', $_SESSION['securityToken']) . "\n"; ?>
                    <table border="0" width="100%" cellspacing="0" cellpadding="0">

                        <tr>
                            <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td valign="top" width="50%"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                                                <tr class="dataTableHeadingRow">
                                                    <td class="dataTableHeadingContent" align="left"><?php echo TABLE_HEADING_FILENAME; ?></td>
                                                    <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_MODIFIED; ?><br /><a href="<?php echo zen_href_link(FILENAME_DISPLAY_LOGS, zen_get_all_get_params(array('sort')) . 'sort=date_a', 'NONSSL'); ?>"><?php echo LOG_SORT_ASC; ?></a>&nbsp;&nbsp;<a href="<?php echo zen_href_link(FILENAME_DISPLAY_LOGS, zen_get_all_get_params(array('sort')) . 'sort=date_d', 'NONSSL'); ?>"><?php echo LOG_SORT_DESC; ?></a></td>
                                                    <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_FILESIZE; ?><br /><a href="<?php echo zen_href_link (FILENAME_DISPLAY_LOGS, zen_get_all_get_params(array('sort')) . 'sort=size_a', 'NONSSL'); ?>"><?php echo LOG_SORT_ASC; ?></a>&nbsp;&nbsp;<a href="<?php echo zen_href_link(FILENAME_DISPLAY_LOGS, zen_get_all_get_params(array('sort')) . 'sort=size_d', 'NONSSL'); ?>"><?php echo LOG_SORT_DESC; ?></a></td>
                                                    <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_DELETE; ?></td>
                                                    <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
                                                </tr>
                                                <?php
                                                reset($logFiles);
                                                $fileData = '';
                                                $heading = array();
                                                $contents = array();
                                                foreach ($logFiles as $curHash => $curFile) {
                                                    ?>
                                                    <tr>
                                                        <td class="dataTableContent" align="left"><?php echo str_replace(DIR_FS_CATALOG, '/', $curFile['name']); ?></td>
                                                        <td class="dataTableContent" align="center"><?php echo date(DATE_FORMAT . ' H:i:s', $curFile['mtime']); ?></td>
                                                        <td class="dataTableContent<?php echo ($curFile['filesize'] > $max_log_file_size) ? ' bigfile' : ''; ?>" align="center"><?php echo $curFile['filesize']; ?></td>
                                                        <td class="dataTableContent" align="center"><?php echo zen_draw_checkbox_field('dList[' . $curHash . ']', false, false, '', 'class="cBox"'); ?></td>
                                                        <td class="dataTableContent" align="right"><?php if ($getFile == $curHash) { echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . zen_href_link(FILENAME_DISPLAY_LOGS, 'fID=' . $curHash . '&amp;' . zen_get_all_get_params(array('fID'))) . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', ICON_INFO_VIEW) . '</a>'; } ?>&nbsp;</td>
                                                    </tr>
                                                    <?php
                                                    if ($getFile == $curHash) {
                                                        $heading[] = array(
                                                            'text' => '<strong>' . TEXT_HEADING_INFO . '( ' . $curFile['name'] . ')</strong>'
                                                        );
                                                        $fileContent = str_replace(DIR_FS_CATALOG, '/', nl2br(htmlentities(trim(file_get_contents($curFile['name'], false, NULL, 0, $max_log_file_size)), ENT_COMPAT+ENT_IGNORE, CHARSET, false)));
                                                        $contents[] = array(
                                                            'align' => 'left',
                                                            'text' => '<div id="fContents">' . $fileContent . '</div>'
                                                        );
                                                        unset($fileContent);
                                                    }
                                                }
                                                ?>
                                            </table></td>
                                        <?php
                                        if (zen_not_null($heading) && zen_not_null($contents)) {
                                            ?>
                                            <td id="contentsOuter" width="50%">
                                                <?php
                                                $box = new box;
                                                echo $box->infoBox($heading, $contents);
                                                ?>
                                            </td>
                                            <?php
                                        }
                                        ?>
                                    </tr>
                                </table></td>
                        </tr>
                        <?php
                        if ($numLogFiles > 0) {
                            ?>
                            <tr>
                                <td id="theButtons">
                                    <div id="dButtons">
                                        <div id="dSel"><button class="btn btn-primary" type="submit" onclick="return buttonCheck('delete');"><?php echo BUTTON_DELETE_SELECTED; ?></button></div>
                                        <div id="dAll"><button class="btn btn-primary" type="submit" onclick="return buttonCheck('all');"><?php echo BUTTON_DELETE_ALL; ?></button></div>
                                    </div>
                                    <div id="dSpace">&nbsp;</div>
                                </td>
                            </tr>
                            <?php
                        }
                        ?>
                    </table></form></td>
            <!-- body_text_eof //-->
        </tr>
    </table>
    <!-- body_eof //-->

    <!-- footer //-->
    <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
    <!-- footer_eof //-->
    <br />
    </body>
    </html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
