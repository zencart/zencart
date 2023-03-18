<?php
/**
 * @copyright Copyright 2003-2023 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2023 Feb 10 Modified in v1.5.8a $
 */
zen_define_default('DIR_WS_EDITORS', 'editors/');

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

/**
 * List of potential editors apps
 *
 * CONSTANTS are used for language-specific display names, and are defined in /YOUR_ADMIN_FOLDER/includes/languages/extra_definitions/editor_MYEDITOR.php
 * - You'll define the language-specific description of MYEDITOR in the constant EDITOR_MYEDITOR:
 * <?php
 * define('EDITOR_MYEDITOR', 'A description of myeditor.');
 *
 * To add additional editors, add your own entries to the $editors_list array by creating a
 * NEW FILE in /YOUR_ADMIN_FOLDER/includes/extra_functions/editor_MYEDITOR.php containing:
 *
 * <?php
 * if (!isset($editors_list)) {
 *     $editors_list = [];
 * }
 * $editors_list['MYEDITOR'] = [
 *     'desc' => EDITOR_MYEDITOR,       //- This is the language constant you created above.
 *     'handler' => 'myeditor.php',
 *     'special_needs' => '',
 * ];
 *
 * NOTE: THERE SHOULD BE NO NEED TO EDIT ANYTHING BELOW THIS LINE:
 */
if (!isset($editors_list)) {
    $editors_list = [];
}

/**
 * Note the key associated with the plain-text editor.  It'll
 * be needed if an unsupported or misconfigured HTML editor is the
 * current editor-of-choice.
 */
$plain_editor_key = count($editors_list) + 1;
$editors_list['NONE'] = [
    'desc' => EDITOR_NONE,
    'handler' => '',
    'special_needs' => '',
];                 // Plain-text editor
$editors_list['CKEDITOR'] = [
    'desc' => EDITOR_CKEDITOR,
    'handler' => 'ckeditor.php',
    'special_needs' => '',
];
if (is_dir(DIR_FS_CATALOG . DIR_WS_EDITORS . 'tiny_mce')) {
    $editors_list['TINYMCE'] = [
        'desc' => EDITOR_TINYMCE,
        'handler' => 'tinymce.php',
        'special_needs' => '',
    ];
}

/**
 * Prepare pulldown menu for use in various pages where editor selections should be offered
 */
$editors_pulldown = [];
$i = 0;
foreach ($editors_list as $key => $value) {
    $i++;
    $editors_pulldown[] = [
        'id' => $i,
        'text' => $value['desc'],
        'key' => $key
    ];
}

/**
 * Account for the fact that the editor might not be valid.  For instance, on an upgrade
 * it might still be set for the HTMLAREA editor!  If the preferred editor is no longer
 * valid, use 'NONE' as the default.
 */
$preferred_editor = HTML_EDITOR_PREFERENCE;
if (!in_array(HTML_EDITOR_PREFERENCE, array_keys($editors_list))) {
    $preferred_editor = 'NONE';
    $current_editor_key = $plain_editor_key;
    $messageStack->add(sprintf(ERROR_EDITOR_NOT_FOUND, HTML_EDITOR_PREFERENCE), 'error');
}

/**
 * Session default is set if no preference has been chosen during this login session.
 */
if (!isset($_SESSION['html_editor_preference_status'])) {
    $_SESSION['html_editor_preference_status'] = $preferred_editor;
}

/**
 * If a new preference has been selected via a pulldown menu, set the details:
 */
$new_editor_choice = (isset($_GET['action']) && $_GET['action'] === 'set_editor' && isset($_GET['reset_editor'])) ? (int)$_GET['reset_editor'] : -1;

/**
 * Set a few variables for use in admin pages
 *
 * $_SESSION['html_editor_preference_status'] = the key name of the selected editor for this session
 * $current_editor_key = the numerical index pointer as default for the pulldown menu drawn when offering editor selection
 * $editor_handler = the path to the handler file containing the logic required for <HEAD> insertion to activate editor features
 *
 */
foreach ($editors_pulldown as $key => $value) {
    if ($new_editor_choice === $value['id']) {
        $_SESSION['html_editor_preference_status'] = $value['key'];
    }
    if ($_SESSION['html_editor_preference_status'] === $value['key']) {
        $current_editor_key = $value['id'];
    }
}

$editor_handler = DIR_WS_INCLUDES . $editors_list[$_SESSION['html_editor_preference_status']]['handler'];
$editor_handler = ($editor_handler === DIR_WS_INCLUDES) ? '' : $editor_handler;
/* if handler not found, reset to NONE */
if ($editor_handler !== '' && !is_file($editor_handler)) {
    $editor_handler = '';
    $_SESSION['html_editor_preference_status'] = 'NONE';
    $current_editor_key = $plain_editor_key;
}

/**
 * Debug code:
 */
if (false) {
    echo '<br><pre>'; print_r($_GET); echo '</pre>';
    echo '<br>new_editor_choice = ' . $new_editor_choice;
    echo '<br>current_editor_key = ' . $current_editor_key;
    echo '<br>$_SESSION[html_editor_preference_status] = ' . $_SESSION['html_editor_preference_status'];
    echo '<br>editor_handler = ' . $editor_handler;
    echo '<br><pre>'; print_r($editors_list); echo '</pre>';
    echo '<br><pre>'; print_r($editors_pulldown); echo '</pre>';
    //die('debug end');
}
