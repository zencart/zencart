<?php


namespace ZenCart\PluginManager;

use App\Model\ModelFactory;

class PluginManager
{

    public function __construct(ModelFactory $modelFactory)
    {
        $this->modelFactory = $modelFactory;
    }

    public function buildPluginListToDb()
    {
        $plugins = $this->modelFactory->make('plugins');
        $dbList = $plugins->all()->toArray();
        $fileList = $this->getPluginsFromFileSystem();
        $diff = array_diff($fileList, $dbList);
        foreach ($diff as $key => $item) {
            $plugins->create($item);
        }
    }

    protected function getPluginsFromFileSystem()
    {
        $rootPath = DIR_FS_CATALOG . 'app/plugins/';
        $pluginList = [];
        $dir = new \DirectoryIterator($rootPath);
        foreach ($dir as $fileinfo) {
            if ($fileinfo->isDir() && !$fileinfo->isDot()) {
                $def = require(DIR_FS_CATALOG . 'app/plugins/' . $fileinfo->getFilename() . '/definition.php');
                $pluginList[$fileinfo->getFilename()] = ['plugin_name'        => $def['name'],
                                                         'plugin_key' => $fileinfo->getFilename(),
                                                         'plugin_description' => $def['description'],
                                                         'plugin_version' => $def['version'],
                                                         'plugin_group' => 0,
                                                         'plugin_status' => 0,
                                                         'plugin_locked' => 0,
                                                         'plugin_internal_state' => 0,
                                                         'plugin_definition'  => json_encode($def)];
            }
        }
        return $pluginList;
    }

}