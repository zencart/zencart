<?php
/**
 * @package plugins
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: DrByte  Jun 30 2014 Modified in v1.5.4 $
 *
 * Designed for ZC >= v1.5.4
 *
 */

class zcObserverLogWriterDatabase extends base {

  public function __construct(notifier $zco_notifier = null) {
    if (!$zco_notifier) $zco_notifier = new notifier;
    $this->notifier = $zco_notifier;
    $this->notifier->attach($this, array('NOTIFY_ADMIN_FIRE_LOG_WRITERS', 'NOTIFY_ADMIN_FIRE_LOG_WRITER_RESET'));
    $this->checkLogSchema();
  }

  public function updateNotifyAdminFireLogWriters(&$class, $eventID, $log_data)
  {
    global $db;
    $this->initLogsTable();
    $sql_data_array = $this->dbPrepareLogData($log_data);
    $db->perform(TABLE_ADMIN_ACTIVITY_LOG, $sql_data_array);
  }

  public function dbPrepareLogData($log_data)
  {
    global $db;
    /**
     * gzip the passed postdata so that it takes less storage space in the database
     */
    $gzpostdata = gzdeflate($log_data['postdata'], 7);

    /**
     * map incoming log data to db schema
     */
    $sql_data_array= array(array('fieldName'=>'access_date', 'value'=>'now()', 'type'=>'passthru'),
            array('fieldName'=>'admin_id',      'value'=> $log_data['admin_id'], 'type'=>'integer'),
            array('fieldName'=>'page_accessed', 'value'=> $log_data['page_accessed'], 'type'=>'string'),
            array('fieldName'=>'page_parameters', 'value'=> $log_data['page_parameters'], 'type'=>'string'),
            array('fieldName'=>'ip_address',    'value'=> $log_data['ip_address'], 'type'=>'string'),
            array('fieldName'=>'gzpost',        'value'=> $gzpostdata, 'type'=>'string'),
            array('fieldName'=>'flagged',       'value'=> $log_data['flagged'], 'type'=>'integer'),
            array('fieldName'=>'attention',     'value'=> $log_data['attention'], 'type'=>'string'),
            array('fieldName'=>'severity',      'value'=> $log_data['severity'], 'type'=>'string'),
            array('fieldName'=>'logmessage',    'value'=> $this->preserveSpecialCharacters($log_data['specific_message']), 'type'=>'string'),
    );

    return $sql_data_array;
  }

  /**
   * PCI requires that if the log table is blank, that the logs be initialized
   * So this simply tests whether the table has any records, and if not, adds an initialization entry
   */
  public function initLogsTable()
  {
    global $db;
    $sql = "SELECT ip_address from " . TABLE_ADMIN_ACTIVITY_LOG . " LIMIT 1";
    $result = $db->Execute($sql);

    if (count($result) == 0) {
      $log_data = array(
        'event_epoch_time'=> time(),
        'admin_id'        => (isset($_SESSION['admin_id'])) ? (int)$_SESSION['admin_id'] : 0,
        'page_accessed'   => 'Log found to be empty. Logging started.',
        'page_parameters' => '',
        'specific_message'=> 'Log found to be empty. Logging started.',
        'ip_address'      => substr($_SERVER['REMOTE_ADDR'],0,45),
        'postdata'        => '',
        'flagged'         => 0,
        'attention'       => '',
        'severity'        => 'notice',
      );
      $sql_data_array = $this->dbPrepareLogData($log_data);
      $db->perform(TABLE_ADMIN_ACTIVITY_LOG, $sql_data_array);
    }
  }

  public function checkLogSchema()
  {
    // adds 'logmessage' field of type mediumtext
    global $db;
    $sql = "show fields from " . TABLE_ADMIN_ACTIVITY_LOG;
    $result = $db->Execute($sql);

    $found_logmessage = false;
    foreach ($result as $row => $val) {
      if ($val['Field'] == 'logmessage') {
        $found_logmessage = true;
      }
    }
    if (!$found_logmessage)
    {
      $sql = "ALTER TABLE " . TABLE_ADMIN_ACTIVITY_LOG . " ADD COLUMN logmessage mediumtext NOT NULL default ''";
      $db->Execute($sql);
    }
    // add 'severity' field of type varchar(9)
    $sql = "show fields from " . TABLE_ADMIN_ACTIVITY_LOG;
    $result = $db->Execute($sql);
    foreach ($result as $row => $val) {
      if ($val['Field'] == 'severity') {
        return true; // exists, so return with no error
      }
    }
    $sql = "ALTER TABLE " . TABLE_ADMIN_ACTIVITY_LOG . " ADD COLUMN severity varchar(9) NOT NULL default 'info'";
    $db->Execute($sql);
    $sql = "UPDATE " . TABLE_ADMIN_ACTIVITY_LOG . " SET severity='notice' where flagged=1";
    $db->Execute($sql);

    // Init the logs if necessary
    $this->initLogsTable();

    // Log the schema change
    $log_data = array(
      'event_epoch_time'=> time(),
      'admin_id'        => (isset($_SESSION['admin_id'])) ? (int)$_SESSION['admin_id'] : 0,
      'page_accessed'   => '',
      'page_parameters' => '',
      'specific_message'=> 'Updated database schema to allow for tracking [severity] in logs. NOTE: Severity levels before this date did not draw extra attention to add/remove of admin users or payment modules (CRUD operations), so old occurrences will have severity of INFO; new occurrences will have the severity of WARNING.',
      'ip_address'      => substr($_SERVER['REMOTE_ADDR'],0,45),
      'postdata'        => '',
      'flagged'         => 1,
      'attention'       => '',
      'severity'        => 'notice',
    );
    $sql_data_array = $this->dbPrepareLogData($log_data);
    $db->perform(TABLE_ADMIN_ACTIVITY_LOG, $sql_data_array);
  }

  public function preserveSpecialCharacters($string)
  {
    $find_chars = array('\n');
    $replace_chars = array("\n");
    $translated = str_replace($find_chars, $replace_chars, $string);
    return $translated;
  }

  /**
   * PCI requires that if the log table is reset, that the reset be logged
   * This does both.
   */
  public function updateNotifyAdminFireLogWriterReset()
  {
    global $db;
    $db->Execute("truncate table " . TABLE_ADMIN_ACTIVITY_LOG);
    $admname = '{' . preg_replace('/[^\w]/', '*', zen_get_admin_name()) . '[' . (int)$_SESSION['admin_id'] . ']}';
    $log_data = array(
            'event_epoch_time'=> time(),
            'admin_id'        => (isset($_SESSION['admin_id'])) ? (int)$_SESSION['admin_id'] : 0,
            'page_accessed'   => 'Log reset by ' . $admname . '.',
            'page_parameters' => '',
            'specific_message'=> 'Log reset by ' . $admname . '.',
            'ip_address'      => substr($_SERVER['REMOTE_ADDR'],0,45),
            'postdata'        => '',
            'flagged'         => 0,
            'attention'       => '',
            'severity'        => 'warning',
    );
    $sql_data_array = $this->dbPrepareLogData($log_data);
    $db->perform(TABLE_ADMIN_ACTIVITY_LOG, $sql_data_array);
  }

}
