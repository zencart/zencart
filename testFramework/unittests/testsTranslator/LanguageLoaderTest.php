<?php
use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\visitor\vfsStreamPrintVisitor;
use org\bovigo\vfs\visitor\vfsStreamStructureVisitor;
use ZenCart\Translator\LanguageLoader;

require_once(__DIR__ . '/../support/zcTestCase.php');

class testLanguageLoader extends zcTestCase
{
    protected $preserveGlobalState = FALSE;
    protected $runTestInSeparateProcess = TRUE;

    public function setup()
    {
        parent::setup();
        $loader = new \Aura\Autoload\Loader;
        $loader->register();
        $loader->addPrefix('\ZenCart\Translator', DIR_APP_LIBRARY . 'zencart/Translator/src');
        $this->root = vfsStream::copyFromFileSystem(__DIR__ .  '/../support/translator', vfsStream::setup('root'));
    }

    public function testBasicFS ()
    {
        $this->assertTrue($this->root->hasChild('app'));
        $this->assertTrue($this->root->hasChild('app/resources'));
    }

    public function testBasicLanguageLoaderFilesExist()
    {
        $ll = new LanguageLoader(vfsStream::url('root') . '/');
        $this->assertTrue(file_exists($ll->getRootFsPath() . 'app/resources/lang/default/en/admin/common.php'));
        $this->assertTrue(file_exists($ll->getRootFsPath() . 'app/resources/lang/local/en/admin/common.php'));
        $this->assertFalse(file_exists($ll->getRootFsPath() . 'app/resources/lang/local/en/admin/auth.php'));
    }

    public function testBasicLoadLines()
    {
        $ll = new LanguageLoader(vfsStream::url('root') . '/');
        $locale = 'en';
        $group = 'admin/common';
        $namespace = null;
        $lines = $ll->load($locale, $group, $namespace);
        $this->assertTrue(count($lines) === 2);
        $this->assertTrue(count($lines['header-titles']) === 4);
        $this->assertTrue($lines['header-titles']['version'] === 'NEW Version FFFFFFFF');
    }

    public function testFailedLoadLines()
    {
        $ll = new LanguageLoader(vfsStream::url('root') . '/');
        $locale = 'en';
        $group = 'admin/unknown';
        $namespace = null;
        $lines = $ll->load($locale, $group, $namespace);
        $this->assertNull($lines);
    }

    public function testPluginLoadLines()
    {
        $ll = new LanguageLoader(vfsStream::url('root') . '/');
        $locale = 'en';
        $group = 'plugin';
        $namespace = 'myPlugin';
        $lines = $ll->load($locale, $group, $namespace);
        $this->assertTrue(count($lines) === 1);
        $this->assertTrue($lines['plugin-test'] === 'Plugin Test');
    }

}
