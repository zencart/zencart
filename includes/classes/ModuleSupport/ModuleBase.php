<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license https://opensource.org/license/mit MIT License
 * @version 
 */

namespace Zencart\ModuleSupport;

use Aura\Autoload\Loader;
use Zencart\Logger\Logger;
use Zencart\Logger\Loggers\ModuleLogger;

abstract class ModuleBase
{
    /**
     * $_check is used to check the configuration key set up
     * @var int
     */
    public int $_check;
    /**
     * @var string
     */
    public string $description;
    /**
     * $enabled determines whether this module shows or not... in catalog.
     *
     * @var boolean
     */
    public bool $enabled;
    /**
     * $sort_order is the order priority of this payment module when displayed
     * @var int
     */
    public ?int $sort_order;
    /**
     * $code determines the internal 'code' name used to designate "this" payment module
     * see //@todo modulesupport icw link to documentation
     * @var string
     */
    public string $code = '';
    /**
     * $defineName is a string used to build a module define from a template string
     * see //@todo modulesupport icw link to documentation
     * @var string
     */
    protected string $defineName = '';
    /**
     * a version string for the module
     * if set will be displayed in the admin
     * @var string
     */
    protected string $version = '';
    /**
     * @var string
     */
    public string $title = '';
    /**
     * @var array
     */
    protected array $configurationKeys;
    /**
     * @var int
     */
    protected int $zone;
    /**
     * @var array
     */
    protected array $configureErrors = [];
    /**
     * reference to the Logger object
     * @var Logger
     */
    protected Logger $logger;

    /**
     * language suffixes to check for store title
     * @var array
     */
    protected $storeLanguageSuffixes = ['TITLE', 'TEXT_TITLE', 'CATALOG_TITLE'];
    /**
     * language suffixes to check for admin title
     * @var array
     */
    protected $adminLanguageSuffixes = ['TITLE_ADMIN', 'TEXT_TITLE_ADMIN', 'ADMIN_TITLE'];
    

    public function __construct()
    {
        /**
         * @var Loader $psr4Autoloader
         */
        global $psr4Autoloader;

        if (empty($this->code)) {
            throw new \Exception('module parameter not set - code');
        }
        if (empty($this->defineName)) {
            throw new \Exception('module parameter not set - defineName');
        }
        $psr4Autoloader = $this->autoloadSupportClasses($psr4Autoloader);
        $loggerOptions = ['channel' => $this->getModuleContext(), 'prefix' => $this->code];
        $this->logger = new ModuleLogger($loggerOptions);
        $this->logger->pushHandlers(['handlers' => $this->getDefine('DEBUG_MODE')]);
        $this->configurationKeys = $this->setConfigurationKeys();
        $this->description = $this->getDescription();
        $this->sort_order = $this->getSortOrder();
    }
     /**
     * @param string $defineTemplate
     * @param $default
     * @return mixed
     */
    protected function getDefine(string $defineSuffix, $default = null): mixed
    {
        $define = $this->buildDefine($defineSuffix);
        if (!defined($define)) {
            return $default;
        }
        return constant($define);
    }
    /**
     * @param $defineTemplate
     * @return string
     */
    protected function buildDefine(string $defineSuffix): string
    {
        $define = 'MODULE_' . strtoupper($this->getModuleContext()) . '_' . strtoupper($this->defineName) . '_' . strtoupper($defineSuffix);
        return $define;
    }
    /**
     * Summary of messagePrefix
     * @param string $message
     * @return string
     */
    protected function messagePrefix(string $message): string
    {
        return $this->title . ': ' . $message;
    }
    /**
     * @return string
     */
    protected function getTitle(): string
    {
        $title = $this->getTitleFromLanguageSuffix($this->storeLanguageSuffixes);
        if (IS_ADMIN_FLAG === true) {
            $title = $this->getAdminTitle($title);
        }
        return $title ?? '';
    }
    /**
     * @param mixed $defaultTitle
     * @return string
     */
    protected function getAdminTitle(?string $defaultTitle = null): string
    {
        $title = $this->getTitleFromLanguageSuffix($this->adminLanguageSuffixes);
        $title = $title ?? $defaultTitle;

        if (!empty($this->version) && $this->version) {
            $title = $title . '[' . $this->version . ']';
        }
        if (method_exists($this, 'checkNonFatalConfigureStatus')) {
            $this->checkNonFatalConfigureStatus();
        }
        if (empty($this->configureErrors)) {
            return $title;
        }
        foreach ($this->configureErrors as $configureError) {
            $title .= '<span class="alert">' . $configureError . '</span>';
        }
        return $title;
    }

    protected function getTitleFromLanguageSuffix(array $languageSuffixes): ?string
    {
        $title = null;
        foreach ($languageSuffixes as $languageSuffix) {
            $title = $this->getDefine($languageSuffix);
            if (!empty($title)) {
                break;
            }
        }
        return $title;
    }
    /**
     * @return array
     */
    protected function setConfigurationKeys(): array
    {
        $local = [];
        $common = $this->setCommonConfigurationKeys();
        if (method_exists($this, 'addCustomConfigurationKeys')) {
            $local = $this->addCustomConfigurationKeys();
        }
        return array_merge($common, $local);
    }
    /**
     * @return string
     */
    protected function getDescription(): string
    {
        return $this->getDefine('DESCRIPTION') ?? '';
    }
    /**
     * @return int|null
     */
    protected function getSortOrder(): int
    {
        $defineValue = $this->getDefine('SORT_ORDER');
        return $defineValue ?? 0;
    }
    /**
     * @return int
     */
    protected function getZone(): int
    {
        $defineValue = $this->getDefine('ZONE');
        return $defineValue ?? 0;
    }
    /**
     * @return bool
     */
    protected function getDebugMode(): bool
    {
        $defineValue = $this->getDefine('DEBUG_MODE');
        return ($defineValue === 'Yes') ?? false;
    }
    /**
     * is the payment module enabled.
     * This allows a check for configuration values through a method
     * @return bool
     */
    protected function isEnabled(): bool
    {
        $enabled = true;
        if (method_exists($this, 'checkFatalConfigureStatus')) {
            $enabled = $this->checkFatalConfigureStatus();
        }
        if (!$enabled) {
            return false;
        }
        $defineValue = $this->getDefine('STATUS');
        return isset($defineValue) && $defineValue === 'True';
    }
    /**
     * 
     * 
     * @param \Aura\Autoload\Loader $psr4Autoloader
     * @return \Aura\Autoload\Loader
     */
    protected function autoloadSupportClasses(Loader $psr4Autoloader): Loader
    {
        if (method_exists($this, 'moduleAutoloadSupportClasses')) {
            $this->moduleAutoloadSupportClasses($psr4Autoloader);
        }
        return $psr4Autoloader;
    }
}
