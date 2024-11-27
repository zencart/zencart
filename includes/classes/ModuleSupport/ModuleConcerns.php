<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license https://opensource.org/license/mit MIT License
 * @version 
 */

namespace Zencart\ModuleSupport;

use App\Models\Configuration;
use App\Models\ZonesToGeoZone;
use Zencart\Traits\NotifierManager;
use Zencart\Traits\ObserverManager;

trait ModuleConcerns
{
    use ObserverManager, NotifierManager;
    
    public function update_status(): void
    {
        global $order;
        $this->logger->log('info', $this->messagePrefix('updating status'));

        if ($this->enabled === false) {
            $this->logger->log('warning', $this->messagePrefix('update status - disabled'));
            return;
        }
        if ($this->getDefine('ZONE', 0) == 0) {
            $this->logger->log('warning', $this->messagePrefix('update status - zone = 0'));

            return;
        }
        if (!isset($order->billing['country']['id'])) {
            $this->logger->log('warning', $this->messagePrefix('update status - no country'));
            return;
        }

        $checkFlag = false;
        $this->logger->log('info', $this->messagePrefix('setting status - ' . ($checkFlag === true ? 'enabled' : 'disabled')));
        $checkZone = ZonesToGeoZone::where('geo_zone_id', $this->getDefine('ZONE', 0))->where('zone_country_id', $order->billing['country']['id'])->orderBy('zone_id')->get();
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
        $notifier= 'NOTIFY_' . $this->getModuleContext() . '_' . strtoupper($this->defineName) . '_UPDATE_STATUS';
        $this->notify($notifier, [], $this->enabled);

    }
    public function check(): bool
    {
        if (isset($this->_check)) {
            return $this->_check;
        }
        $_check = Configuration::where('configuration_key', 'MODULE_' . $this->getModuleContext() . '_' . strtoupper($this->defineName) . '_STATUS')->first();
        $this->_check = $_check ? 1 : 0;
        return $this->_check;
    }
    public function install()
    {
        global $messageStack;
         if ($this->getDefine('STATUS', null)) {
            $messageStack->add_session('Module already installed', 'error');
            zen_redirect(zen_href_link(FILENAME_MODULES, 'set=' . $this->getModuleContext(false) . '&module=' . $this->defineName, 'NONSSL'));
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
        $define = 'MODULE_' . $this->getModuleContext() . '_' . strtoupper($this->defineName) . '_%';
        Configuration::where('configuration_key', 'LIKE', $define)->delete();
    }
}
