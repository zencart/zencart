<?php

use App\Models\PluginControl;
use App\Models\PluginControlVersion;
use Zencart\DbRepositories\PluginControlRepository;
use Zencart\DbRepositories\PluginControlVersionRepository;
use Zencart\PluginManager\PluginManager;
use Zencart\Traits\InteractsWithPlugins;
use Zencart\Traits\ObserverManager;

class AutoZenTestPlugin
{
    use ObserverManager;
    use InteractsWithPlugins;

    public function __construct()
    {
        // test instantiating the plugin_manager, to ensure dependent classes can be loaded as expected.
        $plugin_manager = new PluginManager(new PluginControlRepository, new PluginControlVersionRepository);

        // test alias
        $plugin_manager_alias = new PluginManager(new PluginControl, new PluginControlVersion);

        // test that the plugin manager can successfully list known plugins, which confirms db connectivity.
        $plugin_info = $plugin_manager->getInstalledPlugins();
        if (empty($plugin_info)) {
            throw new \RuntimeException('PluginManager failed to retrieve plugin information from the database.');
        }

        // Test trait operation.
        $this->detectZcPluginDetails(__DIR__);
        if (empty($this->zcPluginContext) || $this->zcPluginContext !== 'admin') {
            throw new \RuntimeException('AutoZenTestPlugin failed to detect that it is running in the admin context.');
        }

        // Test attaching to a notification that is triggered in the admin footer, to confirm that admin-side notifications are working and that the plugin can successfully attach to them.
        // We do this attach "after" the above instantiations, since if they fail, then the attach() shouldn't run (meaning the update() will never fire), thus the associated tests will fail.
        $this->attach($this, ['NOTIFY_ADMIN_FOOTER_END']);
    }

    // Since we're only attaching to one notification, we'll use the universal update() call:
    public function update(&$class, $eventID, ...$data): void
    {
        echo '<div class="alert alert-info" role="alert">' . ZEN_TEST_PLUGIN_TEST_FOOTER . '</div>';
    }
}
