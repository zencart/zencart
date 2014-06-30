<?php
/**
 * @package plugins
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: DrByte  Tue Aug 28 14:21:34 2012 -0400 New in v1.5.3 $
 *
 * Designed for ZC >= v1.5.3
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

  public function __construct() {
    global $zco_notifier;
    $zco_notifier->attach($this, array('NOTIFY_ADMIN_ACTIVITY_LOG_EVENT'));
  }

  public function updateNotifyAdminActivityLogEvent(&$class, $eventID, $message_to_log = '', $requested_severity = '')
  {
    global $zco_notifier, $PHP_SELF;
    $flagged = 0;
    $notes = $gzpostdata = $postdata = '';
    $severity = self::INFO;

    /**
     * Parse associated POST data
     * This is to specifically avoid logging certain confidential details
     * and also to draw attention to certain warnings which deserve highlighting for the benefit of storeowners, since they should be monitoring their logs
     */
    $postdata = $_POST;
    $postdata = $this->filterArrayElements($postdata);
    $postdata = $this->ensureDataIsUtf8($postdata);
    $notes = $this->parseForMaliciousContent(print_r($postdata, true));
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
        $data = $this->filterArrayElements($data);
        $data = $this->ensureDataIsUtf8($data);
      }
      $specific_message = $data;
      $notes2 = $this->parseForMaliciousContent(print_r($data, true));
      $notes = $notes . (strlen($notes) > 0 ? '; ' : '') . $notes2;
    }

    /**
     * Set severity flags
     * If $notes is not false, then that means the malicious-content detector found things which should be deemed remarkable, so we elevate the severity to 'notice'
     */
    if ($notes !== FALSE && $notes != '')
    {
      $severity = self::NOTICE;
      $flagged = 1;
    }

    /**
     * escalate severity if requested level is higher than calculated level
     */
    $levels = static::$levels;
    $levels_lookup = array_flip($levels);

    if (is_string($requested_severity) && $requested_severity != '') {
      if (isset($levels_lookup[strtoupper($requested_severity)])) {
        $integer_requested_severity = $levels_lookup[strtoupper($requested_severity)];
      }
    } else {
      $integer_requested_severity = $requested_severity;
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
        'attention'       => ($notes === FALSE ? '' : $notes),
        'severity'        => strtolower($levels[$severity]),  // converts int to corresponding string
    );

    /**
     * Now tell all log-writers to fire, using the curated data
     */
    $zco_notifier->notify('NOTIFY_ADMIN_FIRE_LOG_WRITERS', $log_data);
  }

  /**
   * Filter out things which ought not to be recorded in logs, such as actual pass words
   */
  private function filterArrayElements($data)
  {
    foreach ($data as $key=>$nul) {
      if (in_array($key, array('x','y','secur'.'ityTo'.'ken','admi'.'n_p'.'ass','pass'.'word','confirm', 'newpwd-'.$_SESSION['securityToken'],'oldpwd-'.$_SESSION['securityToken'],'confpwd-'.$_SESSION['securityToken']))) unset($data[$key]);
    }
    return $data;
  }
  /**
   * In order to json_encode the data for storage, it must be utf8, so we encode each element
   */
  private function ensureDataIsUtf8($data) {
    foreach ($data as $key=>$nul) {
      if (strtolower(CHARSET) != 'utf-8') {
        if (is_string($nul)) $data[$key] = utf8_encode($nul);
        if (is_array($nul)) {
          foreach ($nul as $key2=>$val) {
            if (is_string($val)) $data[$key][$key2] = utf8_encode($val);
            if (is_array($val)) $data[$key][$key2] = utf8_encode(print_r($val, TRUE));
          }
        }
      }
    }
    return $data;
  }

  /**
   * Look for any risky kinds of incoming POST data which might be flagged under PCI safety rules
   */
  private function parseForMaliciousContent($string)
  {
    $matches = '';
    if (preg_match_all('~(file://|<iframe|<frame|<embed|<script|<meta)~i', $string, $matches)) {
      return htmlspecialchars(WARNING_REVIEW_ROGUE_ACTIVITY . ' [' . implode(' and ', $matches[1]) . ']', ENT_COMPAT, CHARSET, TRUE);
    } else {
      return FALSE;
    }
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
