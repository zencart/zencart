<?php
/**
 * @package tests
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */

use Behat\MinkExtension\Context\MinkContext;

/**
 * @package tests
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  $
 */

/**
 * Class MainContext
 */
class MainContext extends MinkContext
{
    protected $baseUrl;
    protected $configParams;

    use behatExtensionsTrait;
    use ZenCartSpecificSteps;
    use ZenCartCouponHelpersTrait;

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
        $this->setTimeZone();
        $this->loadLocalConfig();
        $this->baseUrl = $this->configParams['base_url'];
    }

    /**
     * @param null $tz
     */
    public function setTimeZone($tz = null)
    {
        $TZ = $tz ?: 'UTC';
        putenv('TZ=' . $TZ);
        date_default_timezone_set($TZ);
    }

    /**
     *
     */
    protected function loadLocalConfig()
    {
        $user = isset($_SERVER['USER']) ? $_SERVER['USER'] : 'EXAMPLE';
        if ($user == 'EXAMPLE' && isset($_ENV['USER'])) {
            $user = $_ENV['USER'];
        }

        $path = dirname(__DIR__);

        $travis = $user == 'travis' || (isset($_SERVER['TRAVIS']) && $_SERVER['TRAVIS'] == 'true') || (isset($_ENV['TRAVIS']) && $_ENV['TRAVIS'] == 'true');
        if ($travis && file_exists($path . '/config/localconfig_travis.php')) {
            $user = 'travis';
        }

        echo "\nSeeking config file: " . $path . '/config/localconfig_' . $user . '.php' . "\n\n";
        if ($travis) {
            include($path . '/config/localconfig_travis.php');
        } elseif (isset($user) && $user != '' && file_exists($path . '/config/localconfig_' . $user . '.php')) {
            include($path . '/config/localconfig_' . $user . '.php');
        } elseif (file_exists($path . '/config/localconfig_EXAMPLE.php')) {
            include($path . '/config/localconfig_EXAMPLE.php');
        } else {
            die('COULD NOT FIND CONFIG FILE');
        }
        $this->setLocalConfigParams();
    }

    /**
     *
     */
    protected function setLocalConfigParams()
    {
        $this->configParams['admin_password_main'] = $this->configParams['admin_password_install'];
        if ($this->configParams['serverSchema'] == 'http://') {
            $this->configParams['admin_password_main'] = $this->configParams['admin_password_install'] . '1';
        }
    }


    /**
     * @param string $sql
     * @return bool|mysqli_result
     */
    public function doDbQuery($sql = '')
    {
        if (!isset($this->dbActive)) {
            $this->dbActive = false;
        }
        if ($sql == '') {
            return false;
        }
        if (!$this->dbActive) {
            $this->dbLink = mysqli_connect($this->configParams['db_host'], $this->configParams['db_user'],
                $this->configParams['db_password']);
            if ($this->dbLink) {
                mysqli_select_db($this->dbLink, $this->configParams['db_name']);
                $this->dbActive = true;
            } else {
                echo 'MySQL error: ' . mysqli_errno($this->dbLink) . ' ' . mysqli_error($this->dbLink);
                sleep(10);
                die('Script aborted. ' . __FILE__ . ':(' . __LINE__ . ')');
            }
        }
        $result = mysqli_query($this->dbLink, $sql);
        return $result;
    }

    public function getCustomerIdFromEmail($customerEmail)
    {
        $sql = "SELECT customers_id FROM " . $this->configParams['db_prefix'] . "customers WHERE customers_email_address = '" . $customerEmail . "'";
        $q = $this->doDbQuery($sql);
        if ($q === false || $q->num_rows == 0) {
            return false;
        }
        $result = mysqli_fetch_assoc($q);

        return $result['customers_id'];
    }


}
