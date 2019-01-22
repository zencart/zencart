<?php
use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\visitor\vfsStreamPrintVisitor;
use org\bovigo\vfs\visitor\vfsStreamStructureVisitor;
use ZenCart\Translator\LanguageLoader;
use ZenCart\Translator\Translator;

require_once(__DIR__ . '/../support/zcTestCase.php');

class testTranslator extends zcTestCase
{
    protected $preserveGlobalState = FALSE;
    protected $runTestInSeparateProcess = TRUE;

    public function setup()
    {
        parent::setup();
        $loader = new \Aura\Autoload\Loader;
        $loader->register();
        $loader->addPrefix('\ZenCart\Translator', DIR_APP_LIBRARY . 'zencart/Translator/src');
        $loader->addPrefix('\Illuminate\Support', DIR_APP_LIBRARY . 'illuminate/support');
        $this->root = vfsStream::copyFromFileSystem(__DIR__ .  '/../support/translator', vfsStream::setup('root'));
        $this->loader = new LanguageLoader(vfsStream::url('root') . '/');
        $_SESSION['languages_code'] = 'en';
    }

    public function testSimpleInstantiation()
    {
        $t = new Translator($this->loader, 'en', 'en');
        $this->assertInstanceOf(Translator::class, $t);
    }


    public function testUseLegacyKey()
    {
        if (!defined('LEGACY_KEY')) define('LEGACY_KEY', 'legacy key');
        $t = new Translator($this->loader, 'en', 'en');
        $item = $t->trans('LEGACY_KEY');
        $this->assertTrue($item === 'legacy key');
    }

    public function testGetDefaultkey()
    {
        $t = new Translator($this->loader, 'en', 'en');
        $item = $t->trans('admin/common.header-titles.home');
        $this->assertTrue($item === 'OR Admin Home');
    }

    public function testGetLocalkey()
    {
        $t = new Translator($this->loader, 'en', 'en');
        $item = $t->trans('admin/common.header-titles.version');
        $this->assertTrue($item === 'NEW Version FFFFFFFF');
    }
    public function testGetNamespacedKey()
    {
        $t = new Translator($this->loader, 'en', 'en');
        $item = $t->trans('myPlugin::plugin.plugin-test');
        $this->assertTrue($item === 'Plugin Test');
    }

    public function testInterpolation()
    {
        $t = new Translator($this->loader, 'en', 'en');
        $item = $t->trans('admin/common.interpolated', ['plovers' => 2, 'jewels' => 3, 'magicword' => 'PLUGH']);
        $this->assertTrue($item == 'I see 2 plovers, and 3 shiny jewels. Some one says PLUGH');
    }

}
