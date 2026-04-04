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
     * Expects a POSTed 'layout' form-urlencoded parameter
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
        if (str_contains($raw, '&securityToken=')) { // token already validated if we get this far
            // if data is sent as form-urlencoded, extract the layout parameter
            parse_str($raw, $parsed);
            $data = $parsed['layout'] ?? '';
        }
        $layout = json_decode($data, true);

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
                    // extra precaution in case a ghost element somehow gets through
                    if (!is_string($widget) || empty(trim($widget))) {
                        continue;
                    }
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

        if ($json_data === '{"main":[],"sidebar":[],"bottom":[]}') {
            return $this->response('problem', 'layout not parsed.', true);
        }

        $db->Execute("UPDATE " . TABLE_ADMIN . "
              SET dashboard_layout = '" . $db->prepare_input($json_data) . "'
              WHERE admin_id = " . $_SESSION['admin_id']);

        return $this->response('success', 'Layout saved');
    }

    protected function response(string $status, string $message, bool $error = false): array
    {
        return ['status' => $status, 'message' => $message, 'error' => $error];
    }
}
