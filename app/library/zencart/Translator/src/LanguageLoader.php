<?php


namespace ZenCart\Translator;


class LanguageLoader
{
    protected $fsPath;

    public function __construct($fsRootPath = DIR_FS_CATALOG)
    {
        $this->fsRootPath = $fsRootPath;
        $this->basePath = 'app/resources/lang/';
    }

    public function load($locale, $group, $namespace)
    {
        if (isset($namespace)) {
            $this->basePath = 'app/plugins/' . $namespace . '/resources/lang/';
        }
        $dirs = ['default', 'local'];

        $lines = [];
        foreach ($dirs as $dir) {
            $filePath = $this->fsRootPath . $this->basePath . $dir . '/' . $locale . '/' . $group . '.php';
            if (file_exists($filePath)) {
                $line = require($filePath);
                $lines = array_replace_recursive($lines, $line);
            }
        }

        return count($lines) ? $lines : null;
    }

    public function getRootFsPath()
    {
        return $this->fsRootPath;
    }

}