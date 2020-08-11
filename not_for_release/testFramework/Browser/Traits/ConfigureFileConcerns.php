<?php


namespace Tests\Browser\Traits;


trait ConfigureFileConcerns
{
    protected function createInitialConfigures()
    {
        $dest = DIR_FS_ROOT . 'admin/includes/configure.php';
        $this->removeFile($dest);
        $dest = DIR_FS_ROOT . 'includes/configure.php';
        $this->removeFile($dest);

        $user = $_SERVER['USER'];
        if (file_exists($configFile = DIR_FS_ROOT . 'not_for_release/testFramework/Browser/zencartConfigures/admin.' . $user . '.configure.php')) {
            copy($configFile, DIR_FS_ROOT . 'admin/includes/configure.php');
        } elseif (file_exists($configFile = DIR_FS_ROOT . 'not_for_release/testFramework/Browser/zencartConfigures/admin.default.configure.php')) {
            copy($configFile, DIR_FS_ROOT . 'admin/includes/configure.php');
        }
        if (file_exists($configFile = DIR_FS_ROOT . 'not_for_release/testFramework/Browser/zencartConfigures/catalog.' . $user . '.configure.php')) {
            copy($configFile, DIR_FS_ROOT . 'includes/configure.php');
        } elseif (file_exists($configFile = DIR_FS_ROOT . 'not_for_release/testFramework/Browser/zencartConfigures/catalog.default.configure.php')) {
            copy($configFile, DIR_FS_ROOT . 'includes/configure.php');
        }
        //echo 'using zencart config file = '. $configFile;
    }

    public function removeFile($fileToRemove)
    {
        @unlink($fileToRemove);
    }

    public function createFile($file)
    {
        $f = fopen($file, 'w');
        fclose($f);
    }

    public function makeEmptyConfigures($rootPath)
    {
        $file = $rootPath . 'includes/configure.php';#
        $this->removeFile($file);
        $this->createFile($file);
        chmod($file, 0777);

        $file = $rootPath . 'admin/includes/configure.php';#
        $this->removeFile($file);
        $this->createFile($file);
        chmod($file, 0777);
    }

}
