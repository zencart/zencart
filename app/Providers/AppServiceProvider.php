<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Zencart\PluginManager\PluginManager;
use App\Models\PluginControl;
use App\Models\PluginControlVersion;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        /**
         * Add the admin path to view directories
         *
         */
        if (defined('DIR_FS_ADMIN')) {
            $currentPaths = config('view.paths');
            $currentPaths[] = DIR_FS_ADMIN . 'includes/templates/views';
            config(['view.paths' => $currentPaths]);
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (DEBUG_AUTOLOAD || (defined('STRICT_ERROR_REPORTING') && STRICT_ERROR_REPORTING == true)) {
            @ini_set('display_errors', TRUE);
            error_reporting(defined('ILLUMINATE_ERROR_REPORTING_LEVEL') ? ILLUMINATE_ERROR_REPORTING_LEVEL : 0);
        } else {
            error_reporting(0);
        }
        if ($this->app->runningInConsole()) return;
        $pluginManager = new PluginManager(new PluginControl, new PluginControlVersion);
        $installedPlugins = $pluginManager->getInstalledPlugins();
        $this->app->instance('installedPlugins', $installedPlugins);
    }
}
