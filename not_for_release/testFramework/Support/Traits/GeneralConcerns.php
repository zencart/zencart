<?php

namespace Tests\Support\Traits;

use Illuminate\Database\Capsule\Manager as Capsule;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;

trait GeneralConcerns
{
    public static function detectUser()
    {
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

        require($configFile);
        require_once('database_tables.php');

    }


    public static function loadMigrationAndSeeders()
    {
        self::databaseSetup(); //setup Capsule
        self::runMigrations();
        self::runInitialSeeders();
    }

    public function createHttpBrowser()
    {
        $this->browser = new HttpBrowser(HttpClient::create());
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
