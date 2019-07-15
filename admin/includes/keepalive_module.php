<?php 
// ADMIN KEEPALIVE_MODULE v2.0
// Admin session timeout warning alerter
// Prompts to extend login session after 2/3 of the allowed session time has expired without mouse activity or form submission.

if (!defined('TEXT_TIMEOUT_WARNING')) define('TEXT_TIMEOUT_WARNING', '**WARNING**');
if (!defined('TEXT_TIMEOUT_TIME_REMAINING')) define('TEXT_TIMEOUT_TIME_REMAINING', ' Time remaining:');
if (!defined('TEXT_TIMEOUT_SECONDS')) define('TEXT_TIMEOUT_SECONDS', 'seconds!');
if (!defined('TEXT_TIMEOUT_ARE_YOU_STILL_THERE')) define('TEXT_TIMEOUT_ARE_YOU_STILL_THERE', 'Are you still there?');
if (!defined('TEXT_TIMEOUT_WILL_LOGOUT_SOON')) define('TEXT_TIMEOUT_WILL_LOGOUT_SOON', 'You have been inactive, and will soon be logged out automatically.');
if (!defined('TEXT_TIMEOUT_STAY_LOGGED_IN')) define('TEXT_TIMEOUT_STAY_LOGGED_IN', 'Continue Longer');
if (!defined('TEXT_TIMEOUT_LOGOUT_NOW')) define('TEXT_TIMEOUT_LOGOUT_NOW', 'Logout Now');
if (!defined('TEXT_TIMEOUT_TIMED_OUT_TITLE')) define('TEXT_TIMEOUT_TIMED_OUT_TITLE', 'Logged Out.');
if (!defined('TEXT_TIMEOUT_LOGIN_AGAIN')) define('TEXT_TIMEOUT_LOGIN_AGAIN', 'Login Again');
if (!defined('TEXT_TIMEOUT_TIMED_OUT_MESSAGE')) define('TEXT_TIMEOUT_TIMED_OUT_MESSAGE', 'Your session has timed out. You were inactive, so we logged you out automatically.');

// Read default timeout value from the site's configuration:
$timeoutAfter = ini_get('session.gc_maxlifetime');
if ((int)$timeoutAfter < 30) $timeoutAfter = 1440;
$camefrom = basename($PHP_SELF) . (empty($params = zen_get_all_get_params()) ? '' : '?' . trim($params, '&'));
?>

<link rel="stylesheet" type="text/css" href="includes/css/jAlert.css">
<script src="includes/javascript/jAlert.min.js"></script>
<script src="includes/javascript/jTimeout.min.js"></script>
<script title="jTimeout-Init">
$(function(){
   $.jTimeout(
    {
    'flashTitle': true, //whether or not to flash the tab/title bar when about to timeout, or after timing out
    'flashTitleSpeed': 500, //how quickly to switch between the original title, and the warning text
    'flashingTitleText': '<?php echo addslashes(TEXT_TIMEOUT_WARNING); ?>', //what to show in the tab/title bar when about to timeout, or after timing out
    'timeoutAfter': <?php echo (int)$timeoutAfter; ?>, //passed from server side. 1440 is generally the default timeout in PHP
    'extendOnMouseMove': true, //Whether or not to extend the session when the mouse is moved
    'mouseDebounce': 120, //How many seconds between extending the session when the mouse is moved (instead of extending a billion times within 5 seconds)
    'extendUrl': 'keepalive.php', //URL to request in order to extend the session.
    'logoutUrl': 'logoff.php', //URL to request in order to force a logout after the timeout.
    'loginUrl': '<?php echo $camefrom; ?>', //URL to send a customer to when they want to log back in
    'secondsPrior': <?php echo (int)$timeoutAfter/3; ?>, //how many seconds before timing out to run the next callback (onPriorCallback)
    'onPriorCallback': function(timeout, seconds){
        $.jAlert({
            'id': 'jTimeoutAlert',
            'title': '<?php echo addslashes(TEXT_TIMEOUT_ARE_YOU_STILL_THERE); ?>',
            'content': '<b><?php echo addslashes(TEXT_TIMEOUT_WILL_LOGOUT_SOON); ?> <?php echo addslashes(TEXT_TIMEOUT_TIME_REMAINING); ?> <span class="jTimeout_Countdown">' + seconds + '</span> <?php echo addslashes(TEXT_TIMEOUT_SECONDS); ?></b>',
            'theme': 'red',
            'closeBtn': false,
            'onOpen': function (alert) {
                timeout.startPriorCountdown(alert.find('.jTimeout_Countdown'));
            },
            'btns': [
                {
                    'text': '<?php echo addslashes(TEXT_TIMEOUT_STAY_LOGGED_IN); ?>',
                    'theme': 'green',
                    'onClick': function (e, btn) {
                        e.preventDefault();
                        timeout.options.onClickExtend(timeout);
                        btn.parents('.jAlert').closeAlert();
                        return false;
                    }
                },
                {
                    'text': '<?php echo addslashes(TEXT_TIMEOUT_LOGOUT_NOW); ?>',
                    'theme': 'black',
                    'onClick': function (e, btn) {
                        e.preventDefault();
                        window.location.href = timeout.options.logoutUrl;
                        return false;
                    }
                }
            ]
        });
    },
    'onTimeout': function(timeout){
        /* Alert User */
        $.jAlert({
            'id': 'jTimedoutAlert',
            'title': '<?php echo addslashes(TEXT_TIMEOUT_TIMED_OUT_TITLE); ?>',
            'content': '<b><?php echo addslashes(TEXT_TIMEOUT_TIMED_OUT_MESSAGE); ?></b>',
            'theme': 'red',
            'btns': {
                'text': '<?php echo addslashes(TEXT_TIMEOUT_LOGIN_AGAIN); ?>',
                'href': timeout.options.loginUrl,
                'theme': 'blue',
                'closeAlert': false
            },
            'closeOnClick': false,
            'closeBtn': false,
            'closeOnEsc': false
        });
        /* Force logout */
        $.get(timeout.options.logoutUrl);
        $.jTimeout().destroy();
    }
  }
);
   
//   $.jTimeout.reset(); //will reset the timer to timeoutAfter above
});
</script>
