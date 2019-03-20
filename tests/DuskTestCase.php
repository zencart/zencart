<?php

namespace Tests;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Laravel\Dusk\Browser;
use Laravel\Dusk\Chrome\ChromeProcess;
use Laravel\Dusk\Concerns\ProvidesBrowser;
use PHPUnit_Framework_TestCase;

abstract class DuskTestCase extends PHPUnit_Framework_TestCase
{
    use ProvidesBrowser;

    /**
     * The path to the custom Chromedriver binary.
     *
     * @var string|null
     */
    protected static $chromeDriver;

    /**
     * The Chromedriver process instance.
     *
     * @var \Symfony\Component\Process\Process
     */
    protected static $chromeProcess;

    /**
     * Register the base URL with Dusk.
     *
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();

        if (!defined('DIR_FS_ROOT')) {
            define('DIR_FS_ROOT', \getcwd());

            if (file_exists($configFile = 'tests/configure.dusk.php')) {
                require $configFile;
            } elseif (file_exists($configFile = 'includes/local/configure.dusk.php')) {
                require $configFile;
            } elseif (file_exists($configFile = 'includes/local/configure.php')) {
                require $configFile;
            } elseif (file_exists($configFile = 'includes/configure.php')) {
                require $configFile;
            }
        }

        Browser::$baseUrl = $this->baseUrl();

        Browser::$storeScreenshotsAt = './tests/Browser/screenshots';

        Browser::$storeConsoleLogAt = './tests/Browser/console';

        Browser::$userResolver = function () {
            return $this->user();
        };
    }

    /**
     * Determine the application's base URL.
     *
     * @return string
     */
    protected function baseUrl()
    {
        return defined('HTTP_SERVER') ? HTTP_SERVER : 'http://localhost';
    }

    /**
     * Prepare for Dusk test execution.
     *
     * @beforeClass
     * @return void
     */
    public static function prepare()
    {
        static::startChromeDriver();
    }

    /**
     * Create the RemoteWebDriver instance.
     *
     * @return \Facebook\WebDriver\Remote\RemoteWebDriver
     */
    protected function driver()
    {
        $options = (new ChromeOptions)->addArguments([
            '--disable-gpu',
            '--allow-insecure-localhost',
            '--allow-running-insecure-content',
            '--reduce-security-for-testing',
            '--headless',
        ]);

        return RemoteWebDriver::create(
            'http://localhost:9515',
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY,
                $options
            )->setCapability('acceptInsecureCerts', true)
        );
    }

    /**
     * Start the Chromedriver process.
     *
     * @param  array $arguments
     * @return void
     *
     * @throws \RuntimeException
     */
    public static function startChromeDriver(array $arguments = [])
    {
        static::$chromeProcess = static::buildChromeProcess($arguments);

        static::$chromeProcess->start();

        static::afterClass(function () {
            static::stopChromeDriver();
        });
    }

    /**
     * Stop the Chromedriver process.
     *
     * @return void
     */
    public static function stopChromeDriver()
    {
        if (static::$chromeProcess) {
            static::$chromeProcess->stop();
        }
    }

    /**
     * Build the process to run the Chromedriver.
     *
     * @param  array $arguments
     * @return \Symfony\Component\Process\Process
     *
     * @throws \RuntimeException
     */
    protected static function buildChromeProcess(array $arguments = [])
    {
        return (new ChromeProcess(static::$chromeDriver))->toProcess($arguments);
    }

    /**
     * Set the path to the custom Chromedriver.
     *
     * @param  string $path
     * @return void
     */
    public static function useChromedriver($path)
    {
        static::$chromeDriver = $path;
    }

    /**
     * Return the default user to authenticate.
     *
     * @throws \Exception
     */
    protected function user()
    {
        throw new Exception("User resolver has not been set.");
    }

}
