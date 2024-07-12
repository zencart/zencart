<?php

namespace Zencart\ModuleSupport;

use Aura\Autoload\Loader;
use Carbon\Carbon;
use Zencart\Logger\Logger;
use Zencart\Logger\Loggers\ModuleLogger;

abstract class PaymentModuleAbstract
{
    /**
     * $_check is used to check the configuration key set up
     * @var int
     */
    protected int $_check;
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
     * $order_status is the order status to set after processing the payment
     * @var int
     */
    public int $order_status;
    /**
     * $sort_order is the order priority of this payment module when displayed
     * @var int
     */
    public ?int $sort_order;
    /**
     * $code determines the internal 'code' name used to designate "this" payment module
     * @var string
     */
    public string $code = '';
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

    protected Logger $logger;

    /**
     * @throws \Exception
     * @todo add a better exception
     */
    public function __construct()
    {
        /**
         * @var Loader $psr4Autoloader
         * @var \Order $order
         */
        global $order, $psr4Autoloader;

        if (empty($this->code)) {
            throw new \Exception('payment module parameter not set - code');
        }
        if (empty($this->version)) {
            throw new \Exception('payment module parameter not set - version');
        }

        $psr4Autoloader = $this->autoloadSupportClasses($psr4Autoloader);
        $loggerOptions = ['channel' => 'payment', 'prefix' => $this->code];
        $this->logger = new ModuleLogger($loggerOptions);
        $this->logger->pushHandlers(['handlers' => $this->getDefine('MODULE_PAYMENT_%%_DEBUG_MODE')]);
        $this->configurationKeys = $this->setCommonConfigurationKeys();
        $this->configurationKeys = array_merge($this->configurationKeys, $this->addCustomConfigurationKeys());
        $this->description = $this->getDescription();
        $this->sort_order = $this->getSortOrder();
        $this->zone = $this->getZone();
        $this->enabled = $this->isEnabled();
        $this->title = $this->getTitle();
        $this->logger->log('info', $this->messagePrefix('Called Constructor'));
        if ((int)$this->getDefine('MODULE_PAYMENT_%%_ORDER_STATUS_ID', 0) > 0) {
            $this->order_status = (int)$this->getDefine('MODULE_PAYMENT_%%_ORDER_STATUS_ID');
        }
        if (is_object($order)) $this->update_status();
    }

    /**
     * @return array
     */
    abstract protected function addCustomConfigurationKeys(): array;

    /**
     * @return array
     */
    public function getConfigurationKeys(): array
    {
        return $this->configurationKeys;
    }

    /**
     * @return array
     */
    protected function setCommonConfigurationKeys(): array
    {
        $configKeys = [];
        $key = $this->buildDefine('MODULE_PAYMENT_%%_STATUS');
        $configKeys[$key] = [
            'configuration_value' => 'False',
            'configuration_title' => 'Enable this module',
            'configuration_description' => 'Do you want to accept payments using this module',
            'configuration_group_id' => 6,
            'sort_order' => 1,
            'date_added' => Carbon::now(),
            'set_function' => "zen_cfg_select_option(array('True', 'False'), ",
        ];
        $key = $this->buildDefine('MODULE_PAYMENT_%%_SORT_ORDER');
        $configKeys[$key] = [
            'configuration_value' => 0,
            'configuration_title' => 'Sort order of display.',
            'configuration_description' => 'Sort order of display. Lowest is displayed first.',
            'configuration_group_id' => 6,
            'sort_order' => 1,
            'date_added' => Carbon::now(),
        ];
        $key = $this->buildDefine('MODULE_PAYMENT_%%_ZONE');
        $configKeys[$key] = [
            'configuration_value' => 0,
            'configuration_title' => 'Payment Zone',
            'configuration_description' => 'If a zone is selected, only enable this payment method for that zone.',
            'configuration_group_id' => 6,
            'sort_order' => 1,
            'date_added' => Carbon::now(),
            'set_function' => "zen_cfg_pull_down_zone_classes(",
        ];
        $key = $this->buildDefine('MODULE_PAYMENT_%%_DEBUG_MODE');
        $configKeys[$key] = [
            'configuration_value' => '--none--',
            'configuration_title' => 'Use debug mode',
            'configuration_description' => 'Debug Mode adds extra logging to file, email and console output',
            'configuration_group_id' => 6,
            'sort_order' => 1,
            'date_added' => Carbon::now(),
            'set_function' => "zen_cfg_select_multioption(array('File', 'Email', 'BrowserConsole'), ",
        ];
        return $configKeys;
    }

    /**
     * @return void
     */
    protected function getTitle(): string
    {
        $title = $this->getDefine('MODULE_PAYMENT_%%_TEXT_TITLE');
        if (IS_ADMIN_FLAG === true) {
            $title = $this->getAdminTitle();
        }
        return $title ?? '';
    }

    /**
     * @return string
     */
    protected function getAdminTitle(): string
    {
        $title = $this->getDefine('MODULE_PAYMENT_%%_TEXT_TITLE_ADMIN');
        $title = $title ?? $this->getDefine('MODULE_PAYMENT_%%_TEXT_TITLE');
        $title = $title . '['. $this->version . ']';
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

    /**
     * @return string
     */
    protected function getDescription(): string
    {
        return $this->getDefine('MODULE_PAYMENT_%%_TEXT_DESCRIPTION') ?? '';
    }

    /**
     * @return int|null
     */
    protected function getSortOrder(): ?int
    {
        $defineValue = $this->getDefine('MODULE_PAYMENT_%%_SORT_ORDER');
        return $defineValue ?? null;
    }

    /**
     * @return int
     */
    protected function getZone(): int
    {
        $defineValue = $this->getDefine('MODULE_PAYMENT_%%_ZONE');
        return $defineValue ?? 0;
    }

    /**
     * @return bool
     */
    protected function getDebugMode(): bool
    {
        $defineValue = $this->getDefine('MODULE_PAYMENT_%%_DEBUG_MODE');
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
        $defineValue = $this->getDefine('MODULE_PAYMENT_%%_STATUS');
        return isset($defineValue) && $defineValue === 'True';
    }

    /**
     * Note: This is a stub method as it might not be used in all payment modules
     *
     * @return void
     */
    protected function autoloadSupportClasses(Loader $psr4Autoloader): Loader
    {
        $psr4Autoloader->addPrefix('Zencart\ModuleSupport', DIR_FS_CATALOG . DIR_WS_MODULES . 'payment/stripe_pay/ModuleSupport/');
        $psr4Autoloader->addPrefix('Monolog', DIR_FS_CATALOG . DIR_WS_MODULES . 'payment/stripe_pay/monolog/src/Monolog/');
        $psr4Autoloader->addPrefix('Zencart\Logger', DIR_FS_CATALOG . DIR_WS_MODULES . 'payment/stripe_pay/Logger/');
        if (method_exists($this, 'moduleAutoloadSupportClasses')) {
            $this->moduleAutoloadSupportClasses($psr4Autoloader);
        }
        return $psr4Autoloader;
    }

    protected function messagePrefix(string $message): string
    {
        return $this->title . ': ' . $message;
    }
}
