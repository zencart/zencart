<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Feb 29 Modified in v1.5.7 $
 *
 * Designed for ZC >= v1.5.4
 *
 * NOTE: A far more PSR-3 compliant approach will be implemented in a future version. This is a simplified implementation in the meantime.
 *
 * At present, we implement:
 *  INFO = general logged activity
 *  NOTICE = storeowner should pay attention to these, as they show login attempts and potential entry of malicious script tags by rogue employees
 *  WARNING = CRUD activities related to adding/editing/removing admin users and payment modules
 *  Higher levels go beyond the scope of this implementation at this time
 */

class zcObserverLogEventListener extends base {

  /**
   * using integer values implemented by monolog API
   */
  const DEBUG = 100;
  const INFO = 200; // user logs in, other basic logged activity
  const NOTICE = 250; // uncommon
  const WARNING = 300; //Exceptional occurrences that are not errors
  const ERROR = 400; // Runtime errors
  const CRITICAL = 500; // Application error
  const ALERT = 550; // site down
  const EMERGENCY = 600; // urgent
  /**
   * Logging levels from syslog protocol defined in RFC 5424
   * @var array $levels Logging levels
   */
  protected static $levels = array(
          100 => 'DEBUG',
          200 => 'INFO',
          250 => 'NOTICE',
          300 => 'WARNING',
          400 => 'ERROR',
          500 => 'CRITICAL',
          550 => 'ALERT',
          600 => 'EMERGENCY',
  );

  public function __construct(notifier $zco_notifier = null) {
    if (!$zco_notifier) $zco_notifier = new notifier;
    $this->notifier = $zco_notifier;
    $this->notifier->attach($this, array('NOTIFY_ADMIN_ACTIVITY_LOG_EVENT', 'NOTIFY_ADMIN_ACTIVITY_LOG_RESET'));
  }

  public function updateNotifyAdminActivityLogEvent(&$class, $eventID, $message_to_log = '', $requested_severity = '')
  {
    $log_data = self::prepareLogdata($message_to_log, $requested_severity);
    /**
     * Now tell all log-writers to fire, using the curated data
    */
    $this->notifier->notify('NOTIFY_ADMIN_FIRE_LOG_WRITERS', $log_data);
  }

  static function prepareLogdata($message_to_log = '', $requested_severity = '')
  {
    global $PHP_SELF;
    $flagged = 0;
    $notes = $specific_message = $gzpostdata = $postdata = '';
    $severity = self::INFO;

    /**
     * Parse associated POST data
     * This is to specifically avoid logging certain confidential details
     * and also to draw attention to certain warnings which deserve highlighting for the benefit of storeowners, since they should be monitoring their logs
     */
    $postdata = $_POST;
    $postdata = self::filterArrayElements($postdata);
    $postdata = self::ensureDataIsUtf8($postdata);
    $notes = self::parseForMaliciousContent(print_r($postdata, true));
    /**
     * Since the POST data was an array, we json-encode the parsed POST data for storage in the logging system
     */
    $postdata = json_encode($postdata);

    /**
     * Handle specific message to be logged, if passed
     */
    if ($message_to_log != '' && $message_to_log != 'POST')
    {
      $data = $message_to_log;
      if (is_array($message_to_log))
      {
        $data = self::filterArrayElements($data);
        $data = self::ensureDataIsUtf8($data);
        $data = print_r($data, true);
      }
      $specific_message = $data;
      $notes2 = self::parseForMaliciousContent(print_r($data, true));
      $notes = $notes . (strlen($notes) > 0 ? '; ' : '') . $notes2;
    }
    if ($specific_message == '')
    {
      $specific_message = "Accessed page [" . basename($PHP_SELF) . "]";
      if (isset($_REQUEST['action'])) $specific_message .= ' with action=' . $_REQUEST['action'] . '. Review page_parameters and postdata for details.';
    }

    /**
     * Set severity flags
     * If $notes is not false, then that means the malicious-content detector found things which should be deemed remarkable, so we elevate the severity to 'notice'
     */
    if ($notes !== false && $notes != '')
    {
      $severity = self::NOTICE;
      $flagged = 1;
    }

    /**
     * escalate severity if requested level is higher than calculated level
     */
    $levels = self::$levels;
    $levels_lookup = array_flip($levels);

    $integer_requested_severity = $requested_severity;
    if (is_string($requested_severity) && $requested_severity != '') {
      if (isset($levels_lookup[strtoupper($requested_severity)])) {
        $integer_requested_severity = $levels_lookup[strtoupper($requested_severity)];
      }
    }
    if ($integer_requested_severity > $severity) {
      $severity = $integer_requested_severity;
    }
    if ($severity > self::INFO) $flagged = 1;


    /**
     * Prepare an array of data to be passed to all log-writers (which are observers listening for the Fire event, called below)
     */
    $log_data = array(
        'event_epoch_time'=> time(),
        'admin_id'        => (isset($_SESSION['admin_id'])) ? (int)$_SESSION['admin_id'] : 0,
        'page_accessed'   => basename($PHP_SELF) . (!isset($_SESSION['admin_id']) || (int)$_SESSION['admin_id'] == 0 ? ' ' . (isset($_POST['admin_name']) ? $_POST['admin_name'] : (isset($_POST['admin_email']) ? $_POST['admin_email'] : '') ) : ''),
        'page_parameters' => preg_replace('/(&amp;|&)$/', '', zen_get_all_get_params()),
        'specific_message'=> $specific_message,
        'ip_address'      => substr($_SERVER['REMOTE_ADDR'],0,45),
        'postdata'        => $postdata,
        'flagged'         => $flagged,
        'attention'       => ($notes === false ? '' : $notes),
        'severity'        => strtolower($levels[$severity]),  // converts int to corresponding string
    );

    return $log_data;
  }

  /**
   * Filter out things which ought not to be recorded in logs, such as actual pass words
   */
  static function filterArrayElements($data)
  {
    foreach ($data as $key=>$nul) {
      if (in_array($key, array('x','y','secur'.'ityTo'.'ken','admi'.'n_p'.'ass','pass'.'word','confirm', 'newpwd-'.$_SESSION['securityToken'],'oldpwd-'.$_SESSION['securityToken'],'confpwd-'.$_SESSION['securityToken']))) unset($data[$key]);
    }
    return $data;
  }
  /**
   * In order to json_encode the data for storage, it must be utf8, so we encode each element
   */
  static function ensureDataIsUtf8($data) {
    if (strtolower(CHARSET) == 'utf-8') return $data;
    foreach ($data as $key=>$nul) {
        if (is_string($nul)) $data[$key] = utf8_encode($nul);
        if (is_array($nul)) {
          foreach ($nul as $key2=>$val) {
            if (is_string($val)) $data[$key][$key2] = utf8_encode($val);
          if (is_array($val)) $data[$key][$key2] = utf8_encode(print_r($val, true));
        }
      }
    }
    return $data;
  }

  /**
   * Look for any risky kinds of incoming POST data which might be flagged under PCI safety rules
   */
  static function parseForMaliciousContent($string)
  {
    $matches = '';
    if (preg_match_all('~(file://|<iframe|<frame|<embed|<script|<meta)~i', $string, $matches)) {
      return htmlspecialchars(WARNING_REVIEW_ROGUE_ACTIVITY . ' [' . implode(' and ', $matches[1]) . ']', ENT_COMPAT, CHARSET, true);
    } else {
      return false;
    }
  }

  public function updateNotifyAdminActivityLogReset()
  {
    $this->notifier->notify('NOTIFY_ADMIN_FIRE_LOG_WRITER_RESET');
  }

}
// end of class



/**
 * helper function
 * (violates PSR-0 putting it in this file, but in a true PSR-0 situation this helper wouldn't even be necessary, so putting it here since it suits context)
 * (so, in case it's not obvious, this IS supposed to be a global function, and NOT contained inside the class above)
 */
function zen_record_admin_activity($message, $severity)
{
  global $zco_notifier;
  $zco_notifier->notify('NOTIFY_ADMIN_ACTIVITY_LOG_EVENT', $message, $severity);
}
