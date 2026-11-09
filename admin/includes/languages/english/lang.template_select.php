<?php
/**
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2022 Jan 09 New in v1.5.8-alpha $
*/
$define = [
    'ERROR_CANNOT_CREATE_CHILD' => 'A child template cannot be created for <code>%s</code>; please try again.',
    'ERROR_COULD_NOT_READ_FILE' => 'Sorry, the file could not be read.',
    'ERROR_CREATING_CHILD' => 'The child template could not be created.',
    'ERROR_CREATING_DIRS' => 'An error occurred while creating the directory structure.',
    'ERROR_CREATING_MANIFEST' => 'An error occurred while creating the template\'s <code>manifest.php</code>; check the logs for details.',
    'ERROR_CREATING_SCREENSHOT' => 'An error occurred while copying the template\'s screenshot image.',
    'ERROR_CREATING_TEMPLATE_SETTINGS' => 'An error occurred while copying the template\'s <code>template_settings.php</code> file.',
    // %s is filled in with one of the TEXT_INFO_* labels used by template_select_create_child.php,
    // with any trailing blanks or colons removed.
    'ERROR_MISSING_VALUE_FOR_CREATE' => 'The <em>%s</em> cannot be blank when creating a child template; please re-enter.',
    'ERROR_NO_TEMPLATE_INFO' => 'An error occurred while creating the <code>template_info.php</code> file.',
    'ERROR_TEMPLATE_DIRECTORY_NOT_UNIQUE' => 'The <b>Template Directory</b> you supplied (<code>%s</code>) is already in use, please enter a different value.',
    'ERROR_TEMPLATE_DIRECTORY_VALUE' => 'The <b>Template Directory</b> must contain only alphanumeric characters, underscores (<code>_</code>) or dashes (<code>-</code>); the first character must be alphabetic.',
    'ERROR_TEMPLATE_KEY_NOT_UNIQUE' => 'The <b>Template Key</b> you supplied (<code>%s</code>) is already in use, please enter a different value.',
    'ERROR_TEMPLATE_KEY_VALUE' => 'The <b>Template Key</b> must contain only alphanumeric characters, underscores (<code>_</code>) or dashes (<code>-</code>); the first character must be alphabetic.',
    'ERROR_TEMPLATE_SELECTION_NOT_AVAILABLE' => 'The selected template is not available. Plugin templates must be installed before they can be assigned.',
    'ERROR_UNEXPECTED_RC' => 'An unexpected error (%1$u) occurred when checking <em>%2$s</em>.',
    'ERROR_UNEXPECTED_RC_CREATE' => 'An unexpected error (%1$u) was received.',
    // %1$s = value entered; %2$s is the field's name; %3$u is the maximum length of the field.
    'ERROR_VALUE_TOO_LONG' => 'The value entered (<code>%1$s</code>) for the <b>%2$s</b> field is longer than %3$u characters; please enter a different value.',

    'HEADING_ACTIVE_TEMPLATES' => 'Active Templates',
    'HEADING_AVAILABLE_TEMPLATES' => 'Available Templates',
    'HEADING_SUBTITLE_CREATE_CHILD' => ' &mdash; Create a Child Template',
    'HEADING_SUBTITLE_PREVIEW_CHILD' => ' &mdash; Preview a Child Template',
    'HEADING_TITLE' => 'Template Selection',

    'IMAGE_CREATE_CHILD' => 'Create Child',
    'IMAGE_NEW_TEMPLATE' => 'Create a new template/language association',

    'SUCCESS_CHILD_TEMPLATE_CREATED' => 'A child template has been successfully created and must be installed prior to use.',

    'TABLE_HEADING_CHILD_VALUE' => 'Child Template Value',
    'TABLE_HEADING_DIRECTORY' => 'Template Directory',
    'TABLE_HEADING_LANGUAGE' => 'Template Language',
    'TABLE_HEADING_NAME' => 'Template Name',
    'TABLE_HEADING_PARENT_DIRECTORY' => 'Parent Directory',
    'TABLE_HEADING_PARENT_VALUE' => 'Parent Template Value',

    'TEXT_CLOSE' => 'Close',
    'TEXT_INFO_CHILD_CREATED' => ' This template was created as a child of %1$s (%2$s) on %3$s.',  //-%1$s (parent template_name), %2$s (parent template_dir), %3$s (date of the creation)
    'TEXT_INFO_CREATE_CHILD' => 'Create a new template based on the <code>%s</code> template.',
    'TEXT_INFO_CREATE_CHILD_DETAILS' => <<<END
    <p>These validations are performed on the form-entry fields below:</p>
    <ol>
        <li><b>Template Key</b><ol type="a">
            <li>Cannot be blank.</li>
            <li>Contains only alphanumeric characters, underscores (<code>_</code>) or dashes (<code>-</code>); the first character must be alphabetic.</li>
            <li>Is unique among all other encapsulated plugins' <code>Plugin Key</code> values.</li>
            <li>Length must be less than or equal to %1\$u.</li>
        </ol></li>
        <li><b>Plugin Name</b><ol type="a">
            <li>Cannot be blank.</li>
            <li>HTML tags are not allowed and will be removed if supplied.</li>
            <li>Length must be less than or equal to %2\$u.</li>
        </ol></li>
        <li><b>Template Name</b><ol type="a">
            <li>Cannot be blank.</li>
            <li>HTML tags are not allowed and will be removed if supplied.</li>
        </ol></li>
        <li><b>Template Directory</b><ol type="a">
            <li>Cannot be blank.</li>
            <li>Contains only alphanumeric characters, underscores (<code>_</code>) or dashes (<code>-</code>); the first character must be alphabetic.</li>
            <li>Is unique among all other templates' <code>template_dir</code> values.</li>
            <li>Length must be less than or equal to %3\$u.</li>
        </ol></li>
    </ol>
    END,
    'TEXT_INFO_CREATE_CHILD_SUMMARY' => 'Show/hide additional information',
    'TEXT_INFO_DEFAULT_LANGUAGE' => 'Default (all)',
    'TEXT_INFO_DEFAULT_TEMPLATE' => 'This template is used by default / when an installed language does not have an assigned template.',
    'TEXT_INFO_DELETE_INTRO' => 'Delete this template-language association',
    'TEXT_INFO_EDIT_INTRO' => 'Change the Template',
    'TEXT_INFO_HEADING_DELETE_TEMPLATE' => 'Delete Template association',
    'TEXT_INFO_HEADING_EDIT_TEMPLATE' => 'Edit Template Settings',
    'TEXT_INFO_HEADING_NEW_TEMPLATE' => 'Associate Template with a language',
    'TEXT_INFO_HEADING_LANGUAGE' => 'Language',
    'TEXT_INFO_INSERT_INTRO' => 'Choose below to associate a template with a language',
    'TEXT_INFO_LANGUAGE_NAME' => 'Language Name',
    'TEXT_INFO_PREVIEW_CHILD' => '',
    'TEXT_TEMPLATE_SELECT_INFO' => '<p>Use this tool to change the template used to display the storefront. The default template is normally used for <strong>all</strong> languages. Adding template-to-language assignments is typically used when supporting RTL languages alongside LTR languages.</p><p>It is possible to view a template in a <em>private</em> session, for example when testing a new template: see the online Help <strong>?</strong></p>',
    'TEXT_INFO_PLUGIN_NAME' => 'Plugin Name:',
    'TEXT_INFO_TEMPLATE_AUTHOR' => 'Template Author: ',
    'TEXT_INFO_TEMPLATE_DESCRIPTION' => 'Template Description:',
    'TEXT_INFO_TEMPLATE_DIRECTORY' => 'Template Directory:',
    'TEXT_INFO_TEMPLATE_KEY' => 'Template Key:',
    'TEXT_INFO_TEMPLATE_NAME' => 'Template Name:',
    'TEXT_INFO_TEMPLATE_VERSION' => 'Template Version: ',
    'TEXT_MODAL_HEADING_INTRO' => 'Template Settings: ',
    'TEXT_VIEW_TEMPLATE_SETTINGS' => 'View Template Settings',
];
return $define;
