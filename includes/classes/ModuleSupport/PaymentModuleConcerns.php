<?php

namespace Zencart\ModuleSupport;

use App\Models\Configuration;
use App\Models\ZonesToGeoZone;
use Zencart\Traits\ObserverManager;

trait PaymentModuleConcerns
{
    use ObserverManager, GeneralModuleConcerns;

    public function update_status(): void
    {
        global $order;
        $this->logger->log('info', $this->messagePrefix('updating status'));

        if ($this->enabled === false) {
            $this->logger->log('warning', $this->messagePrefix('update status - disabled'));
            return;
        }
        if ($this->getDefine('MODULE_PAYMENT_%%_ZONE', 0) == 0) {
            $this->logger->log('warning', $this->messagePrefix('update status - zone = 0'));

            return;
        }
        if (!isset($order->billing['country']['id'])) {
            $this->logger->log('warning', $this->messagePrefix('update status - no country'));
            return;
        }

        $checkFlag = false;
        $this->logger->log('info', $this->messagePrefix('setting status - ' . ($checkFlag === true ? 'enabled' : 'disabled')));
        $checkZone = ZonesToGeoZone::where('geo_zone_id', $this->getDefine('MODULE_PAYMENT_%%_ZONE', 0))->where('zone_country_id', $order->billing['country']['id'])->orderBy('zone_id')->get();
        foreach ($checkZone as $zone) {
            if ($zone->zone_id < 1) {
                $checkFlag = true;
                $this->logger->log('info', $this->messagePrefix('update status enabled - zone id < 1'));
                break;
            }
            if ($zone->zone_id == $order->billing['zone_id']) {
                $checkFlag = true;
                $this->logger->log('info', $this->messagePrefix('update status disabled - zone id matches billing zone id'));
                break;
            }
        }
        $this->logger->log('info', $this->messagePrefix('updated status - ' . ($checkFlag ? 'enabled' : 'disabled')));
        $this->enabled = $checkFlag;
    }

    public function javascript_validation(): string
    {
        return false;
    }

    public function selection(): array
    {
        return [
            'id' => $this->code,
            'module' => $this->title
        ];
    }

    public function pre_confirmation_check()
    {
        return false;

    }

    public function confirmation()
    {
        return [
            'title' => $this->title
        ];
    }

    public function process_button()
    {
        return false;
    }
    public function clear_payments()
    {

    }

    public function before_process()
    {
        return false;
    }

    public function after_order_create($orders_id)
    {

    }

    public function after_process()
    {
        return false;
    }
    public function admin_notification($zf_order_id)
    {

    }

    public function check(): bool
    {
        if (isset($this->_check)) {
            return $this->_check;
        }
        $_check = Configuration::where('configuration_key', 'MODULE_PAYMENT_' . strtoupper($this->code) . '_STATUS')->first();
        $this->_check = $_check ? 1 : 0;
        return $this->_check;
    }

    public function install()
    {
        global $messageStack;
        if ($this->getDefine('MODULE_PAYMENT_%%_STATUS', null)) {
            $messageStack->add_session($this->getDefine('MODULE_PAYMENT_%%_ERROR_TEXT_ALREADY_INSTALLED'), 'error');
            zen_redirect(zen_href_link(FILENAME_MODULES, 'set=payment&module=' . $this->code, 'NONSSL'));
            return;
        }
        foreach ($this->configurationKeys as $configurationKey => $configurationValues) {
            $configurationValues['configuration_key'] = $configurationKey;
            $config = new Configuration($configurationValues);
            $config->save();
        }
    }

    public function keys(): array
    {
        return array_keys($this->configurationKeys);
    }

    public function remove()
    {
        $define = 'MODULE_PAYMENT_' . strtoupper($this->code) . '_%';
        Configuration::where('configuration_key', 'LIKE', $define)->delete();
    }
}
