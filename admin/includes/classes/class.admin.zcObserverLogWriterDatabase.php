<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2024 Sep 21 Modified in v2.1.0-beta1 $
 */
use Zencart\Traits\ObserverManager;

class zcObserverLogWriterDatabase
{
    use ObserverManager;

    public function __construct()
    {
        $this->attach($this, ['NOTIFY_ADMIN_FIRE_LOG_WRITERS', 'NOTIFY_ADMIN_FIRE_LOG_WRITER_RESET']);

        $this->checkLogSchema();
    }

    public function updateNotifyAdminFireLogWriters(&$class, string $eventID, array $log_data): void
    {
        $this->initLogsTable();

        // -----
        // Don't record accesses coming from the keep-alive timer.
        //
        if ($log_data['page_accessed'] === 'keepalive.php') {
            return;
        }

        /**
         * gzip the passed postdata so that it takes less storage space in the database
         */
        $gzpostdata = gzdeflate($log_data['postdata'], 7);

        /**
         * map incoming log data to db schema
         */
        global $db;
        $sql_data_array = [
            'access_date' => 'now()',
            'admin_id' => (int)$log_data['admin_id'],
            'page_accessed' => $db->prepare_input($log_data['page_accessed']),
            'page_parameters' => $db->prepare_input($log_data['page_parameters']),
            'ip_address' => $db->prepare_input($log_data['ip_address']),
            'gzpost' => $gzpostdata,
            'flagged' => (int)$log_data['flagged'],
            'attention' => $db->prepare_input($log_data['attention']),
            'severity' => $db->prepare_input($log_data['severity']),
            'logmessage' => $this->preserveSpecialCharacters($db->prepare_input($log_data['specific_message'])),
        ];
        zen_db_perform(TABLE_ADMIN_ACTIVITY_LOG, $sql_data_array);
    }

    /**
     * PCI requires that if the log table is blank, that the logs be initialized
     * So this simply tests whether the table has any records, and if not, adds an initialization entry
     */
    protected function initLogsTable(): void
    {
        global $db;

        $sql = "SELECT ip_address from " . TABLE_ADMIN_ACTIVITY_LOG . " LIMIT 1";
        $result = $db->Execute($sql);
        if ($result->EOF) {
            $admin_id = $_SESSION['admin_id'] ?? 0;
            $sql_data_array = [
                'access_date' => 'now()',
                'admin_id' => (int)$admin_id,
                'page_accessed' =>  'Log found to be empty. Logging started.',
                'page_parameters' => '',
                'ip_address' => $db->prepare_input(substr($_SERVER['REMOTE_ADDR'],0,45)),
                'gzpost' => '',
                'flagged' => 0,
                'attention' => '',
                'severity' => 'notice',
                'logmessage' =>  'Log found to be empty. Logging started.',
            ];
            zen_db_perform(TABLE_ADMIN_ACTIVITY_LOG, $sql_data_array);
        }
    }

    protected function checkLogSchema(): void
    {
        // adds 'logmessage' field of type mediumtext
        global $db, $sniffer;

        if ($sniffer->field_exists(TABLE_ADMIN_ACTIVITY_LOG, 'logmessage') === false) {
            $sql = "ALTER TABLE " . TABLE_ADMIN_ACTIVITY_LOG . " ADD COLUMN logmessage mediumtext NOT NULL";
            $db->Execute($sql);
        }
 
        // add 'severity' field of type varchar(9), if not already present
        if ($sniffer->field_exists(TABLE_ADMIN_ACTIVITY_LOG, 'severity') === true) {
            return;
        }

        $sql = "ALTER TABLE " . TABLE_ADMIN_ACTIVITY_LOG . " ADD COLUMN severity varchar(9) NOT NULL default 'info', ADD INDEX idx_severity_zen (severity)";
        $db->Execute($sql);

        $sql = "UPDATE " . TABLE_ADMIN_ACTIVITY_LOG . " SET severity = 'notice' WHERE flagged = 1";
        $db->Execute($sql);

        // Init the logs if necessary
        $this->initLogsTable();

        // Log the schema change
        $admin_id = $_SESSION['admin_id'] ?? 0;
        $sql_data_array = [
            'access_date' => 'now()',
            'admin_id' => (int)$admin_id,
            'ip_address' => $db->prepare_input(substr($_SERVER['REMOTE_ADDR'], 0, 45)),
            'gzpost' => '',
            'flagged' => 1,
            'attention' => '',
            'severity' => 'notice',
            'logmessage' => 'Updated database schema to allow for tracking [severity] in logs. NOTE: Severity levels before this date did not draw extra attention to add/remove of admin users or payment modules (CRUD operations), so old occurrences will have severity of INFO; new occurrences will have the severity of WARNING.',
        ];
        zen_db_perform(TABLE_ADMIN_ACTIVITY_LOG, $sql_data_array);
    }

    protected function preserveSpecialCharacters(string $string): string
    {
        return str_replace('\n', "\n", $string);
    }

    public function updateNotifyAdminFireLogWriterReset(): void
    {
        global $db;

        $db->Execute("TRUNCATE TABLE " . TABLE_ADMIN_ACTIVITY_LOG);
        
        $admin_id = $_SESSION['admin_id'] ?? 0;
        $admname = '{' . preg_replace('/[^\w]/', '*', zen_get_admin_name() ?? '[Unknown/NotLoggedIn]') . '[' . $admin_id . ']}';

        $sql_data_array = [
            'access_date' => 'now()',
            'admin_id' => (int)$admin_id,
            'page_accessed' =>  'Log reset by ' . $admname . '.',
            'page_parameters' => '',
            'ip_address' => $db->prepare_input(substr($_SERVER['REMOTE_ADDR'], 0, 45)),
            'gzpost' => '',
            'flagged' => 0,
            'attention' => '',
            'severity' => 'warning',
            'logmessage' =>  'Log reset by ' . $admname . '.',
        ];
        zen_db_perform(TABLE_ADMIN_ACTIVITY_LOG, $sql_data_array);
    }
}
