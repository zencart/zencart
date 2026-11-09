<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: neekfenwick 2023 Dec 09 Modified in v2.0.0-alpha1 $
 */
use Zencart\DbRepositories\LayoutBoxRepository;
use Zencart\DbRepositories\PluginControlRepository;
use Zencart\PluginSupport\PluginStatus;
use Zencart\Templates\TemplateChild;
use Zencart\Templates\TemplateSelect;

require 'includes/application_top.php';

if (isset($_GET['tID'])) {
    $selected_template = (int)$_GET['tID'];
}

/**
 * Use the global PluginManager class instance to pull in any plugins that
 * have not yet been registered in the database.
 *
 * That enables this tool to check information in template packages
 * that are present in the file-system but not installed.
 */
$pluginManager->inspectAndUpdate();

/**
 * Retrieve the currently-selectable templates.
 */
$templateSelect = new TemplateSelect();
$template_info = $templateSelect->getSelectableTemplates();

$action = $_GET['action'] ?? '';
switch ($action) {
    /**
     * Known actions that don't need additional information; handled
     * by /templates/template_select_listing.php.
     */
    case 'new':
    case 'edit':
    case 'delete':
        break;

    /**
     * Activates a template for use on the storefront.
     */
    case 'activate':
        $templateKey = (string)($_POST['ln'] ?? '');
        if (!isset($_POST['lang']) || $templateSelect->templateIsSelectable($templateKey) === false) {
            $messageStack->add_session(ERROR_TEMPLATE_SELECTION_NOT_AVAILABLE, 'error');
            zen_redirect(zen_href_link(FILENAME_TEMPLATE_SELECT, zen_get_all_get_params(['action'])));
        }

        /**
         * Attempt to register the template for a given language, recording the
         * action in a admin activity-log record. If the action isn't successful,
         * that implies that the tool's form has an issue or an admin is fiddling
         * with the HTML. No success/fail message is set, intentionally.
         */
        $rc = $templateSelect->registerNewTemplate($templateKey, (int)$_POST['lang']);
        if ($rc !== false) {
            $action_result = 'Successful';
            $activity_status = 'info';
        } else {
            $action_result = 'Unsuccessful';
            $activity_status = 'warning';
        }
        zen_record_admin_activity("$action_result activation of $templateKey for language id#" . (int)$_POST['lang'], $activity_status);
        zen_redirect(zen_href_link(FILENAME_TEMPLATE_SELECT));
        break;

    /**
     * Updates the template in use on the storefront for a previously-selected
     * language, implying that there is already a template associated for the
     * language in the database.
     */
    case 'update':
        $templateKey = (string)($_POST['ln'] ?? '');
        if (!isset($_POST['tID']) || $templateSelect->templateIsSelectable($templateKey) === false) {
            $messageStack->add_session(ERROR_TEMPLATE_SELECTION_NOT_AVAILABLE, 'error');
            zen_redirect(zen_href_link(FILENAME_TEMPLATE_SELECT, zen_get_all_get_params(['action'])));
        }

        /**
         * Attempt to update the template for a given template_id, recording the
         * action in a admin activity-log record. If the action isn't successful,
         * that implies that the tool's form has an issue or an admin is fiddling
         * with the HTML. No success/fail message is set, intentionally.
         */
        $rc = $templateSelect->updateTemplateNameForId((int)$_POST['tID'], $templateKey);
        if ($rc === TemplateSelect::SETTINGS_OK) {
            $action_result = 'successful';
            $activity_status = 'info';
        } else {
            $action_result = 'unsuccessful';
            $activity_status = 'warning';
        }
        zen_record_admin_activity("Template directory change to $templateKey for id#" . (int)$_POST['tID'] . " was $action_result.", $activity_status);
        zen_redirect(zen_href_link(FILENAME_TEMPLATE_SELECT, zen_get_all_get_params(['action'])));
        break;

    case 'deleteconfirm':
        if (isset($_POST['tID'])) {
            $templateSelect->deregisterTemplateId((int)$_POST['tID']);
        }
        zen_redirect(zen_href_link(FILENAME_TEMPLATE_SELECT));
        break;

    /**
     * Variables created are used in admin/includes/templates/template_select_create_child.php.
     */
    case 'createchild':
        if (!isset($_GET['tKey'])) {
            zen_redirect(zen_href_link(FILENAME_TEMPLATE_SELECT));
        }
        $parent_template_directory = (string)$_GET['tKey'];
        $template_child = new TemplateChild($pluginManager, $parent_template_directory);
        if ($template_child->canCreate() === false) {
            $messageStack->add_session(sprintf(ERROR_CANNOT_CREATE_CHILD, zen_output_string_protected($parent_template_directory)), 'error');
            zen_redirect(zen_href_link(FILENAME_TEMPLATE_SELECT));
        }

        $template_name = '';
        $template_key = '';
        $template_key_max_len = $template_child->getTemplateKeyMaxLength();
        $template_directory = '';
        $template_directory_max_len = $template_child->getTemplateDirectoryMaxLength();
        $plugin_name = '';
        $plugin_name_max_len = $template_child->getPluginNameMaxLength();

        $parent_template_name = $template_info[$parent_template_directory]['name'];
        $parent_template_key = $template_info[$parent_template_directory]['plugin_key'];
        $parent_plugin_name = $template_info[$parent_template_directory]['manifest']['pluginName'];
        break;

    /**
     * Variables created are used in admin/includes/templates/template_select_preview_child.php and/or
     * admin/includes/templates/template_select_create_child.php.
     *
     * Note: The same variables are checked for both actions.
     */
    case 'previewchild':
    case 'savechild':
        if (!isset($_POST['tKey'])) {
            zen_redirect(zen_href_link(FILENAME_TEMPLATE_SELECT));
        }
        $parent_template_directory = (string)$_POST['tKey'];
        $template_child = new TemplateChild($pluginManager, $parent_template_directory);
        if ($template_child->canCreate() === false) {
            $messageStack->add_session(sprintf(ERROR_CANNOT_CREATE_CHILD, zen_output_string_protected($parent_template_directory)), 'error');
            zen_redirect(zen_href_link(FILENAME_TEMPLATE_SELECT));
        }

        /**
         * Set the supplied child-template information into the TemplateChild class.
         */
        $template_name = $_POST['template_name'] ?? '';
        $template_key = $_POST['template_key'] ?? '';
        $plugin_name = $_POST['plugin_name'] ?? '';
        $template_directory = $_POST['template_directory'] ?? '';
        $template_child->setChildInfo($template_name, $template_key, $plugin_name, $template_directory);

        $template_key_max_len = $template_child->getTemplateKeyMaxLength();
        $template_directory_max_len = $template_child->getTemplateDirectoryMaxLength();
        $plugin_name_max_len = $template_child->getPluginNameMaxLength();

        $error = false;
        $has_error = [];

        /**
         * Each of the form-fields set by template_select_create_child.php must be
         * supplied and not empty.
         */
        $rc = $template_child->validateTemplateName($template_name);
        if ($rc !== TemplateChild::ERROR_NONE) {
            $error = true;
            $has_error['template_name'] = 'has-error';
            $field_label = rtrim(TEXT_INFO_TEMPLATE_NAME, ' :');
            if ($rc === TemplateChild::ERROR_EMPTY) {
                $messageStack->add(sprintf(ERROR_MISSING_VALUE_FOR_CREATE, $field_label), 'error');
            } else {
                $messageStack->add(sprintf(ERROR_UNEXPECTED_RC, $rc, $field_label), 'error');
            }
        }

        $rc = $template_child->validateTemplateKey($template_key);
        if ($rc !== TemplateChild::ERROR_NONE) {
            $error = true;
            $has_error['template_key'] = 'has-error';
            $field_label = rtrim(TEXT_INFO_TEMPLATE_KEY, ' :');
            switch ($rc) {
                case TemplateChild::ERROR_EMPTY:
                    $messageStack->add(sprintf(ERROR_MISSING_VALUE_FOR_CREATE, $field_label), 'error');
                    break;
                case TemplateChild::ERROR_INVALID_SUBDIR:
                    $messageStack->add(ERROR_TEMPLATE_KEY_VALUE, 'error');
                    break;
                case TemplateChild::ERROR_DUPLICATE_SUBDIR:
                    $messageStack->add(sprintf(ERROR_TEMPLATE_KEY_NOT_UNIQUE, zen_output_string_protected($template_key)), 'error');
                    break;
                case TemplateChild::ERROR_VALUE_TOO_LONG:
                    $messageStack->add(sprintf(ERROR_VALUE_TOO_LONG, zen_output_string_protected($template_key), $field_label, $template_key_max_len), 'error');
                    break;
                default:
                    $messageStack->add(sprintf(ERROR_UNEXPECTED_RC, $rc, $field_label), 'error');
                    break;
            }
        }

        $rc = $template_child->validatePluginName($plugin_name);
        if ($rc !== TemplateChild::ERROR_NONE) {
            $error = true;
            $has_error['plugin_name'] = 'has-error';
            $field_label = rtrim(TEXT_INFO_PLUGIN_NAME, ' :');
            switch ($rc) {
                case TemplateChild::ERROR_EMPTY:
                    $messageStack->add(sprintf(ERROR_MISSING_VALUE_FOR_CREATE, $field_label), 'error');
                    break;
                case TemplateChild::ERROR_VALUE_TOO_LONG:
                    $messageStack->add(sprintf(ERROR_VALUE_TOO_LONG, zen_output_string_protected($plugin_name), $field_label, $plugin_name_max_len), 'error');
                    break;
                default:
                    $messageStack->add(sprintf(ERROR_UNEXPECTED_RC, $rc, $field_label), 'error');
                    break;
            }
        }

        $rc = $template_child->validateTemplateDirectory($template_directory);
        if ($rc !== TemplateChild::ERROR_NONE) {
            $error = true;
            $has_error['template_directory'] = 'has-error';
            $field_label = rtrim(TEXT_INFO_TEMPLATE_DIRECTORY, ' :');
            switch ($rc) {
                case TemplateChild::ERROR_EMPTY:
                    $messageStack->add(sprintf(ERROR_MISSING_VALUE_FOR_CREATE, $field_label), 'error');
                    break;
                case TemplateChild::ERROR_INVALID_SUBDIR:
                    $messageStack->add(ERROR_TEMPLATE_DIRECTORY_VALUE, 'error');
                    break;
                case TemplateChild::ERROR_DUPLICATE_SUBDIR:
                    $messageStack->add(sprintf(ERROR_TEMPLATE_DIRECTORY_NOT_UNIQUE, zen_output_string_protected($template_directory)), 'error');
                    break;
                case TemplateChild::ERROR_VALUE_TOO_LONG:
                    $messageStack->add(sprintf(ERROR_VALUE_TOO_LONG, zen_output_string_protected($template_directory), $field_label, $template_directory_max_len), 'error');
                    break;
                default:
                    $messageStack->add(sprintf(ERROR_UNEXPECTED_RC, $rc, $field_label), 'error');
                    break;
            }
        }

        $parent_template_name = $template_info[$parent_template_directory]['name'];
        $parent_template_key = $template_info[$parent_template_directory]['plugin_key'];
        $parent_plugin_name = $template_info[$parent_template_directory]['manifest']['pluginName'];
        if ($error === true) {
            $action = 'createchild';
        }
        if ($error === true || $action === 'previewchild') {
            break;
        }

        /**
         * Otherwise, the to-be-created child template's information has been validated and the
         * template creation begins ...
         */
        $rc = $template_child->createChildTemplate();
        if ($rc !== TemplateChild::ERROR_NONE) {
            $error = true;
            $message = ERROR_CREATING_CHILD . ' ';
            switch ($rc) {
                case TemplateChild::ERROR_CREATING_DIRS:
                    $messageStack->add($message . ERROR_CREATING_DIRS, 'error');
                    break;
                case TemplateChild::ERROR_NO_TEMPLATE_INFO:
                    $messageStack->add($message . ERROR_NO_TEMPLATE_INFO, 'error');
                    break;
                case TemplateChild::ERROR_CREATING_SCREENSHOT:
                    $messageStack->add($message . ERROR_CREATING_SCREENSHOT, 'error');
                    break;
                case TemplateChild::ERROR_CREATING_TEMPLATE_SETTINGS:
                    $messageStack->add($message . ERROR_CREATING_TEMPLATE_SETTINGS, 'error');
                    break;
                case TemplateChild::ERROR_CREATING_MANIFEST:
                    $messageStack->add($message . ERROR_CREATING_MANIFEST, 'error');
                    break;
                default:
                    $messageStack->add($message . sprintf(ERROR_UNEXPECTED_RC_CREATE, $rc), 'error');
                    break;
            }
            $action = 'createchild';
            $template_child->removeChildTemplatePlugin($template_key);
            break;
        }

        /**
         * The child template's file-system structure has now been successfully created.
         * Copy/update the parent template's layout_boxes table structure to the just-created
         * child.
         */
        $layout_boxes = new LayoutBoxRepository($db);
        $layout_boxes->copyTemplateToTemplate($parent_template_directory, $template_directory);

        /**
         * Redirect to the Plugin Manager (with the child-template selected) so that
         * the template's plugin can be installed (required for the template to be used).
         */
        $messageStack->add_session(SUCCESS_CHILD_TEMPLATE_CREATED, 'success');
        zen_redirect(zen_href_link(FILENAME_PLUGIN_MANAGER, "colKey=$template_key&action=install"));
        break;

    default:
        $action = '';
        break;
}
?>
<!doctype html>
<html <?= HTML_PARAMS ?>>
<head>
    <?php require DIR_WS_INCLUDES . 'admin_html_head.php'; ?>
</head>
<body>
    <!-- header //-->
    <?php require DIR_WS_INCLUDES . 'header.php'; ?>
    <!-- header_eof //-->
    <!-- body //-->
<?php
/**
 * The rendering for the various actions are quite different from each
 * other, so the template portion pulled in depends on what's
 * being rendered (for readability).
 */
if ($action === 'createchild') {
    require DIR_WS_INCLUDES . 'templates/template_select_create_child.php';
} elseif ($action === 'previewchild') {
    require DIR_WS_INCLUDES . 'templates/template_select_preview_child.php';
} else {
    require DIR_WS_INCLUDES . 'templates/template_select_listing.php';
}
?>
    <!-- footer //-->
<?php require DIR_WS_INCLUDES . 'footer.php'; ?>
    <!-- footer_eof //-->
    <!-- body_eof //-->
</body>
</html>
<?php require DIR_WS_INCLUDES . 'application_bottom.php'; ?>
