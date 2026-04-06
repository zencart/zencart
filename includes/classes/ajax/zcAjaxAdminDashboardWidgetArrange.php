<?php
/**
 * zcAjaxAdminDashboardWidgetArrange
 *
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license https://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: New in v3.0.0 $
 * @since ZC v3.0.0
 */
class zcAjaxAdminDashboardWidgetArrange extends base
{
    /**
     * Save the new dashboard widget arrangement.
     * Reads the posted layout data, validates and sanitizes it, and updates the admin's dashboard_layout in the database.
     * Returns a structured response indicating success or error.
     *
     * @since ZC v3.0.0
     */
    public function save(): array|string
    {
        global $db;
        // -----
        // Deny access unless running under the admin.
        //
        if (!defined('IS_ADMIN_FLAG') || IS_ADMIN_FLAG !== true) {
            return 'false';
        }

        $raw = file_get_contents('php://input');
        $raw = preg_replace('/&securityToken=[0-9A-Fa-f]+/', '', $raw);
        parse_str($raw, $parsed);
        $data = $parsed['layout'] ?? '';
        $layout = json_decode($data, true);
        $jserr = json_last_error();
        $jsmsg = json_last_error_msg();

        if (empty($layout)) {
            return $this->response('error', 'No layout data received. ' . $jserr . ' ' . $jsmsg, true);
        }

        // validate structure
        // ensure only known zones exist in the data
        $allowed_zones = ['main', 'sidebar', 'bottom'];
        $clean_layout = [];

        foreach ($allowed_zones as $zone) {
            if (isset($layout[$zone]) && is_array($layout[$zone])) {
                $clean_layout[$zone] = [];
                foreach ($layout[$zone] as $widget) {
                    // filter out empty elements that the ajax drag-drop might send
                    if (!is_string($widget) || empty(trim($widget))) {
                        continue;
                    }
                    // sanitize: filenames should only contain alphanumeric, underscore, dot, hyphen
                    $clean_widget = preg_replace('/[^a-zA-Z0-9_.-]/', '', $widget);
                    if (empty($clean_widget)) {
                        continue;
                    }
                    // require a .php extension (case-insensitive)
                    if (!str_ends_with(strtolower($clean_widget), '.php')) {
                        continue;
                    }
                    $clean_layout[$zone][] = $clean_widget;
                }
            } else {
                $clean_layout[$zone] = [];
            }
        }

        $json_data = json_encode($clean_layout);

        if ($json_data === false || $json_data === json_encode($allowed_zones)) {
            return $this->response('problem', 'layout not parsed.', true);
        }

        $db->Execute("UPDATE " . TABLE_ADMIN . "
              SET dashboard_layout = '" . $db->prepare_input($json_data) . "'
              WHERE admin_id = " . (int)$_SESSION['admin_id']);

        return $this->response('success', 'Layout saved');
    }

    protected function response(string $status, string $message, bool $error = false): array
    {
        return ['status' => $status, 'message' => $message, 'error' => $error];
    }
}
