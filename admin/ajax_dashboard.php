<?php
/**
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version Modern Dynamic Dashboard 2026
 * @author ZenExpert - https://zenexpert.com
 */

require('includes/application_top.php');

if (!defined('IS_ADMIN_FLAG')) die('Illegal Access');

// Simple JSON response helper
function json_response($status, $message) {
    echo json_encode(['status' => $status, 'message' => $message]);
    exit;
}

// validate
if (!isset($_POST['layout'])) {
    json_response('error', 'No layout data received');
}

$layout = $_POST['layout'];

// validate structure
// ensure only known zones exist in the data
$allowed_zones = ['main', 'sidebar', 'bottom'];
$clean_layout = [];

foreach ($allowed_zones as $zone) {
    if (isset($layout[$zone]) && is_array($layout[$zone])) {
        $clean_layout[$zone] = [];
        foreach ($layout[$zone] as $widget) {
            // sanitize: filenames should only contain alphanumeric, underscore, dot
            $clean_widget = preg_replace('/[^a-zA-Z0-9_\.]/', '', $widget);
            if (!empty($clean_widget)) {
                $clean_layout[$zone][] = $clean_widget;
            }
        }
    } else {
        $clean_layout[$zone] = [];
    }
}

$json_data = json_encode($clean_layout);

$db->Execute("UPDATE " . TABLE_CONFIGURATION . "
              SET configuration_value = '" . $db->prepare_input($json_data) . "'
              WHERE configuration_key = 'DASHBOARD_WIDGETS_CONFIG'");

json_response('success', 'Layout saved');
