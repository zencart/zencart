<?php
// -----
// Part of the "Display Logs" plugin for Zen Cart v2.2.0 or later
//
// Copyright (c) 2012-2026, Vinos de Frutas Tropicales (lat9)
//
if (!defined('IS_ADMIN_FLAG') || IS_ADMIN_FLAG !== true) {
    exit('Invalid Access');
}

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
// If the current admin isn't a superuser, redirect to the "denied" page
// and note the occurrence in the admin activity log.
//
if (!zen_is_superuser()) {
    zen_record_admin_activity('Attempted access to unauthorized page [display_logs]. Redirected to DENIED page instead.', 'notice');
    zen_redirect(zen_href_link(FILENAME_DENIED, '', 'SSL'));
}

// -----
// If debug-logs-only has been selected, display only those files.  If multiple file prefixes are
// to be either included or excluded, wrap that value with parenthese to make preg_match "happy".
//
if (isset($_GET['debug_only'])) {
    $files_to_match = 'myDEBUG-(adm-)?[0-9]+-[0-9]+-[0-9]+((-deprecated)|(-warning)|(-error))?';
    $files_to_exclude = '';
} else {
    $files_to_match = str_replace(' ', '', zen_config('DISPLAY_LOGS_INCLUDED_FILES', ''));
    if (strpos($files_to_match, '|') !== false) {
        $files_to_match = "($files_to_match)";
    }
    $files_to_match .= '.*';

    $files_to_exclude = str_replace(' ', '', zen_config('DISPLAY_LOGS_EXCLUDED_FILES', ''));
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
$max_logs_to_display = (int)zen_config('DISPLAY_LOGS_MAX_DISPLAY');
if ($max_logs_to_display < 1) {
    $max_logs_to_display = 20;
}

// -----
// Gather the current log files.
//
$logFiles = [];
foreach ([DIR_FS_LOGS, DIR_FS_SQL_CACHE, DIR_FS_CATALOG . '/includes/modules/payment/paypal/logs'] as $logFolder) {
    $logFolder = rtrim($logFolder, '/');
    $dir = @dir($logFolder);
    if ($dir !== null) {
        while ($file = $dir->read()) {
            if ($file !== '.' && $file !== '..' && !str_starts_with($file, '.')) {
                if (preg_match('/^' . $files_to_match . '\.log$/', $file)) {
                    if ($files_to_exclude === '' || !preg_match('/^' . $files_to_exclude . '\.log$/', $file)) {
                        $hash = hash('sha1', $logFolder . '/' . $file);
                        $logFiles[$hash] = [
                            'name'  => $logFolder . '/' . $file,
                            'mtime' => filemtime($logFolder . '/' . $file),
                            'filesize' => filesize($logFolder . '/' . $file)
                        ];
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
        array_pop($logFiles);
    }
}

// -----
// If any file delete requests have been made, process them first.
//
$action = $_GET['action'] ?? '';
if ($action === 'delete') {
    if (count($_POST['dList'] ?? []) !== 0) {
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
        if ($filesDeleted === $numFiles) {
            $messageStack->add_session(sprintf(SUCCESS_FILES_DELETED, $numFiles), 'success');
        } else {
            $messageStack->add_session(sprintf(WARNING_SOME_FILES_DELETED, $filesDeleted, $numFiles), 'warning');
        }
    } else {
        $messageStack->add_session(WARNING_NO_FILES_SELECTED, 'warning');
    }
    zen_redirect(zen_href_link(FILENAME_DISPLAY_LOGS, zen_get_all_get_params(['action'])));
}

if (isset($_GET['fID'])) {
    if (array_key_exists($_GET['fID'], $logFiles)) {
        $getHash = $_GET['fID'];
    } else {
        unset($_GET['fID']);
        $getHash = array_key_first($logFiles);
    }
} elseif (count($logFiles) !== 0) {
    $getHash = array_key_first($logFiles);
} else {
    $getHash = '';
}

// -----
// "Sanitize" the maximum file-size to be fully read, defaulting if the configured value is not a positive integer.
//
$max_log_file_size = (int)zen_config('DISPLAY_LOGS_MAX_FILE_SIZE');
if ($max_log_file_size < 1) {
    $max_log_file_size = 80000;
}
?>
<!doctype html >
<html <?= HTML_PARAMS ?>>
<head>
    <?php require DIR_WS_INCLUDES . 'admin_html_head.php'; ?>
    <script>
        function buttonCheck(whichButton) {
            var submitOK = false;
            var elements = document.getElementsByClassName('cBox');
            var n = elements.length;
            if (whichButton == 'all') {
                submitOK = confirm('<?= JS_MESSAGE_DELETE_ALL_CONFIRM ?>');
                if (submitOK) {
                    for (var i = 0; i < n; i++) {
                        elements[i].checked = true;
                    }
                }
            } else if (whichButton == 'inverse') {
                for (var i = 0; i < n; i++) {
                    elements[i].checked = !elements[i].checked;
                }
            } else {
                var selected = 0;
                for (var i = 0; i < n; i++) {
                    if (elements[i].checked) selected++;
                }
                if (selected > 0) {
                    submitOK = confirm('<?= JS_MESSAGE_DELETE_SELECTED_CONFIRM ?>');
                } else {
                    alert('<?= WARNING_NO_FILES_SELECTED ?>');
                }
            }
            return submitOK;
        }
    </script>
</head>
<body>
<!-- header //-->
<?php require DIR_WS_INCLUDES . 'header.php'; ?>
<!-- header_eof //-->

<!-- body //-->
<div class="container-fluid">
    <h1><?= HEADING_TITLE ?></h1>
    <p>
        <?= (!str_starts_with(HTTP_SERVER, 'https') ? WARNING_NOT_SECURE : '') .
            sprintf(
                TEXT_INSTRUCTIONS,
                $max_log_file_size,
                $sort_description,
                (($numLogFiles > $max_logs_to_display) ? $max_logs_to_display : $numLogFiles),
                $numLogFiles,
                $files_to_match,
                $files_to_exclude,
                zen_icon('circle-info', IMAGE_ICON_INFO, '2x', true)
            ) ?>
    </p>

    <?= zen_draw_form('logs_form', FILENAME_DISPLAY_LOGS, '', 'get') .
            '<b>' . DISPLAY_DEBUG_LOGS_ONLY . '</b>&nbsp;&nbsp;' .
            zen_draw_checkbox_field('debug_only', 'on', isset($_GET['debug_only']), '', 'onclick="this.form.submit();"') .
            zen_draw_hidden_field('sort', $sort) .
        '</form>' ?>

    <?= zen_draw_form('dlForm', FILENAME_DISPLAY_LOGS, zen_get_all_get_params(['action']) . 'action=delete') ?>
    <div class="row mt-2">
        <!-- body_text //-->
        <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 configurationColumnLeft">
            <table class="table table-hover" role="listbox">
                <thead>
                <tr class="dataTableHeadingRow">
                    <th class="dataTableHeadingContent"><?= TABLE_HEADING_FILENAME ?></th>
                    <th class="dataTableHeadingContent text-center">
                        <?= TABLE_HEADING_MODIFIED ?><br>
                        <a class="me-2" href="<?= zen_href_link(FILENAME_DISPLAY_LOGS, zen_get_all_get_params(['sort']) . 'sort=date_a') ?>">
                            <?= TEXT_ASC ?>
                        </a>
                        <a href="<?= zen_href_link(FILENAME_DISPLAY_LOGS, zen_get_all_get_params(['sort']) . 'sort=date_d') ?>">
                            <?= TEXT_DESC ?>
                        </a>
                    </th>
                    <th class="dataTableHeadingContent text-center">
                        <?= TABLE_HEADING_FILESIZE ?><br>
                        <a class="me-2" href="<?= zen_href_link (FILENAME_DISPLAY_LOGS, zen_get_all_get_params(['sort']) . 'sort=size_a') ?>">
                            <?= TEXT_ASC ?>
                        </a>
                        <a href="<?= zen_href_link(FILENAME_DISPLAY_LOGS, zen_get_all_get_params(['sort']) . 'sort=size_d') ?>">
                            <?= TEXT_DESC ?>
                        </a>
                    </th>
                    <th class="dataTableHeadingContent text-center"><?= TABLE_HEADING_DELETE ?></th>
                    <th class="dataTableHeadingContent text-right"><?= TABLE_HEADING_ACTION ?></th>
                </tr>
                </thead>
                <tbody>
<?php
$fileData = '';
foreach ($logFiles as $curHash => $curFile) {
?>
                <tr>
                    <td class="dataTableContent"><?= str_replace(DIR_FS_CATALOG, '/', $curFile['name']) ?></td>
                    <td class="dataTableContent text-center"><?= date(DATE_FORMAT . ' H:i:s', $curFile['mtime']) ?></td>
                    <td class="dataTableContent<?= ($curFile['filesize'] > $max_log_file_size) ? ' text-danger' : '' ?> text-center">
                        <?= $curFile['filesize'] ?>
</td>
                    <td class="dataTableContent text-center">
                        <?= zen_draw_checkbox_field('dList[' . $curHash . ']', false, false, '', 'class="cBox"') ?>
                    </td>
                    <td class="dataTableContent text-right">
<?php
    if ($getHash === $curHash) {
        $fileData = $curFile;
        echo zen_icon('caret-right', '', '2x', true);
    } else {
        echo
            '<a href="' . zen_href_link(FILENAME_DISPLAY_LOGS, zen_get_all_get_params(['fID'])) . '&fID=' . $curHash . '">' .
                zen_icon('circle-info', IMAGE_ICON_INFO, '2x', true) .
            '</a>';
    }
}
?>
                    </td>
                </tr>
                </tbody>
            </table>
<?php
if ($numLogFiles > 0) {
?>
            <div class="row text-center">
                <button class="btn btn-info" type="submit" onclick="return buttonCheck('inverse');"><?= BUTTON_INVERT_SELECTED ?></button>
                <button class="btn btn-danger" type="submit" onclick="return buttonCheck('delete');"><?= BUTTON_DELETE_SELECTED ?></button>
                <button class="btn btn-danger" type="submit" onclick="return buttonCheck('all');"><?= BUTTON_DELETE_ALL ?></button>
            </div>
<?php
}
?>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 configurationColumnRight">
<?php
    $heading = [];
    $contents = [];
    if (is_array($fileData)) {
        $heading[] = [
            'text' => '<strong>' . TEXT_HEADING_INFO . '(' . str_replace(DIR_FS_CATALOG, '/', $fileData['name']) . ')</strong>',
        ];
        $fileContent = str_replace(
            DIR_FS_CATALOG,
            '/',
            nl2br(htmlentities(trim(file_get_contents($fileData['name'], false, null, 0, $max_log_file_size)), ENT_COMPAT+ENT_IGNORE, CHARSET, false), false)
        );
        $contents[] = [
             'text' => '<div id="fContents">' . $fileContent . '</div>',
        ];
        unset($fileContent);
    }

    if (!empty($heading) && !empty($contents)) {
        $box = new box();
        echo $box->infoBox($heading, $contents);
    }
?>
        </div>
    </div>
    <?= '</form>' ?>
</div>
<!-- body_eof //-->

<!-- footer //-->
<?php require DIR_WS_INCLUDES . 'footer.php'; ?>
<!-- footer_eof //-->

</body>
</html>
<?php require DIR_WS_INCLUDES . 'application_bottom.php'; ?>
