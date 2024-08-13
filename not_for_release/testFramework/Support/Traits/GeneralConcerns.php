<?php

namespace Tests\Support\Traits;

use App\Models\PluginControl;
use App\Models\PluginControlVersion;
use Illuminate\Database\Capsule\Manager as Capsule;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Filesystem\Filesystem;


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


    protected function browserAdminLogin() 
    {
        $this->runCustomSeeder('StoreWizardSeeder');
        $this->browser->request('GET', HTTP_SERVER . '/admin');
        $response = $this->browser->getResponse();
        $this->assertStringContainsString('Admin Login', (string)$response->getContent() );
        $this->browser->submitForm('Submit', [
            'admin_name' => 'Admin',
            'admin_pass' => 'password',
        ]);
    }

    // PLUGIN STUFF

    protected function installPluginToFilesystem($pluginName, $version)
    {
        $this->addPluginToFileSystem($pluginName, $version);
    }

    protected function removePlugin($pluginName, $version)
    {
        $this->removePluginFromFileSystem($pluginName, $version);
    }

    protected function removePluginFromFileSystem($pluginName, $version)
    {
        $filesystem = new Filesystem();
        if (is_dir(DIR_FS_CATALOG . 'zc_plugins/' . $pluginName . '/' . $version)) {
            $filesystem->remove(DIR_FS_CATALOG . 'zc_plugins/' . $pluginName . '/' . $version);
        }
    }

    protected function addPluginToFileSystem($pluginName, $version)
    {
        $srcDirectory = DIR_FS_CATALOG . 'not_for_release/testFramework/Support/plugins/' . $pluginName;
        $destinationDirectory = DIR_FS_CATALOG . 'zc_plugins/' . $pluginName . '/';
        $filesystem = new Filesystem();
        if (!is_dir(DIR_FS_CATALOG . 'zc_plugins/' . $pluginName)) {
            $filesystem->mkdir($destinationDirectory);
        }
        $filesystem->mirror($srcDirectory, $destinationDirectory);
    }
}
