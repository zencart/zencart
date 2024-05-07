<?php

namespace Tests\Support\Traits;

use Illuminate\Database\Capsule\Manager as Capsule;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;

trait GeneralConcerns
{
    protected HttpBrowser $browser;

    public static function detectUser()
    {
        if (isset($_SERVER['IS_DDEV_PROJECT'])) {
            return 'ddev';
        }
        $user = $_SERVER['USER'] ?? $_SERVER['MY_USER'];

        return $user;
    }

    public static function loadConfigureFile($context)
    {
        if (defined('HTTP_SERVER')) {
            return;
        }
        $user = self::detectUser();
        echo 'This user = ' . $user . PHP_EOL;
        $basePath = $configFile = TESTCWD . 'Support/configs/';
        $configFile =  $basePath . $user . '.' . $context . '.configure.php';
        if (!file_exists($configFile)) {
            die('could not find config file ' .$configFile);
        }
        echo $configFile . PHP_EOL;
        $file = require($configFile);
        return $file;
    }


    public static function loadMigrationAndSeeders($mainConfigs = [])
    {
        self::databaseSetup(); //setup Capsule
        self::runDatabaseLoader($mainConfigs);
    }

    public function createHttpBrowser()
    {
        $this->browser = new HttpBrowser(HttpClient::create());
    }

    public static function locateElementInPageSource(string $element_lookup_text, string $page_source, int $length = 1500): string
    {
        $position = strpos($page_source, $element_lookup_text);
        // if not found, return whole $page_source; but if found, only return a portion of the page
        return ($position === false) ? $page_source : substr($page_source, $position, $length);
    }

    /**
     * @param $page
     * @return mixed
     * @todo refactor - use zen_href_link
     */
    protected function buildStoreLink($page)
    {
        $URI = HTTP_SERVER . '/index.php?main_page='.$page;
        return $URI;
    }
    protected function buildAdminLink($page)
    {
        $URI = HTTP_SERVER . '/admin/index.php?cmd='.$page;
        return $URI;
    }

}
