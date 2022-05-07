<?php


namespace Tests\Browser\Traits;


trait ConfigureFileConcerns
{

    protected function createInitialConfigures()
    {
        $configFile = 'NOT FOUND';
        $this->saveConfigures(DIR_FS_ROOT);
        if (file_exists($configFile = DIR_FS_ROOT . '/not_for_release/testFramework/Browser/zencartConfigures/local/admin.' . $this->user . '.configure.php')) {
            copy($configFile, DIR_FS_ROOT . '/admin/includes/configure.php');
        } elseif (file_exists($configFile = DIR_FS_ROOT . '/not_for_release/testFramework/Browser/zencartConfigures/admin.default.configure.php')) {
            copy($configFile, DIR_FS_ROOT . 'admin/includes/configure.php');
        }
        echo "admin config fle = " . $configFile . "\n";
//        if (file_exists($configFile = DIR_FS_ROOT . '/not_for_release/testFramework/Browser/zencartConfigures/local/catalog.' . $this->user . '.configure.php')) {
//            copy($configFile, DIR_FS_ROOT . '/includes/configure.php');
//        } elseif (file_exists($configFile = DIR_FS_ROOT . '/not_for_release/testFramework/Browser/zencartConfigures/catalog.default.configure.php')) {
//            copy($configFile, DIR_FS_ROOT . 'includes/configure.php');
//        }
//        echo "store config fle = " . $configFile . "\n";
    }

    public function copyFile($fileToRemove)
    {
        copy($fileToRemove, $fileToRemove . '.testsave');
    }

    public function createFile($file)
    {
        $f = fopen($file, 'w');
        fclose($f);
    }

    public function makeEmptyConfigures($rootPath)
    {
        $this->saveConfigures($rootPath);
        $file = $rootPath . '/includes/configure.php';
        if (file_exists($file)) {
            unlink($file);
            touch($file);
            chmod($file, 0777);
        }

        $file = $rootPath . '/admin/includes/configure.php';
        if (file_exists($file)) {
            unlink($file);
            touch($file);
            chmod($file, 0777);
        }
    }

    protected function saveConfigures($rootPath)
    {
        $dest = $rootPath . '/admin/includes/configure.php';
        $this->saveConfigureFile($dest);
        $dest = $rootPath . '/includes/configure.php';
        $this->saveConfigureFile($dest);
    }

    protected function saveConfigureFile($dest)
    {
        if (file_exists($dest)) {
            copy($dest, $dest . '.config.save');
        }
    }

}
