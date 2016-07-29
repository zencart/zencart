<?php
namespace Aura\Autoload;

class LoaderTest extends \PHPUnit_Framework_TestCase
{
    protected $loader;

    protected $base_dir;

    protected function setup()
    {
        $this->loader = new Loader;
    }

    public function testRegisterAndUnregister()
    {
        $this->loader->register();

        $functions = spl_autoload_functions();
        list($actual_object, $actual_method) = array_pop($functions);

        $this->assertSame($this->loader, $actual_object);
        $this->assertSame('loadClass', $actual_method);

        // now unregister it so we don't pollute later tests
        $this->loader->unregister();
    }

    public function testLoadClass()
    {
        $class = 'Aura\Autoload\Foo';

        $this->loader->addPrefix('Aura\Autoload\\', __DIR__);

        $expect_file = $this->nds(__DIR__ . '/Foo.php');
        $actual_file = $this->nds($this->loader->loadClass($class));

        $this->assertSame($expect_file, $actual_file);

        // is it actually loaded?
        $this->assertTrue(in_array($class, get_declared_classes()));

        // is it recorded as loaded?
        $expect = array($class => $expect_file);
        $actual = $this->loader->getLoadedClasses();
        $this->assertSame($expect, $actual);
    }

    public function testLoadClassMissing()
    {
        $this->loader->addPrefix('Aura\Autoload\\', __DIR__);
        $class = 'Aura\Autoload\MissingClass';
        $this->loader->loadClass($class);
        $loaded = $this->loader->getLoadedClasses();
        $this->assertFalse(isset($loaded[$class]));
    }

    public function testAddPrefix()
    {
        // append
        $this->loader->addPrefix('Foo\Bar', '/path/to/foo-bar/tests');

        // prepend
        $this->loader->addPrefix('Foo\Bar', '/path/to/foo-bar/src', true);

        $actual = $this->nds($this->loader->getPrefixes());
        $expect = array(
            'Foo\Bar\\' => array(
                $this->nds('/path/to/foo-bar/src/'),
                $this->nds('/path/to/foo-bar/tests/'),
            ),
        );
        $this->assertSame($expect, $actual);
    }

    public function testSetPrefixes()
    {
        $this->loader->setPrefixes(array(
            'Foo\Bar' => $this->nds('/foo/bar'),
            'Baz\Dib' => $this->nds('/baz/dib'),
            'Zim\Gir' => $this->nds('/zim/gir'),
        ));

        $actual = $this->loader->getPrefixes();
        $expect = array(
            'Foo\Bar\\' => array($this->nds('/foo/bar/')),
            'Baz\Dib\\' => array($this->nds('/baz/dib/')),
            'Zim\Gir\\' => array($this->nds('/zim/gir/')),
        );
        $this->assertSame($expect, $actual);
    }

    public function testLoadExplicitClass()
    {
        $class = 'Aura\Autoload\Bar';
        $file  = $this->nds(__DIR__ . '/Bar.php');
        $this->loader->setClassFiles(array(
            $class => $file,
        ));

        $actual_file = $this->nds($this->loader->loadClass($class));
        $this->assertSame($file, $actual_file);

        // is it actually loaded?
        $this->assertTrue(in_array($class, get_declared_classes()));

        // is it recorded as loaded?
        $expect = array($class => $file);
        $actual = $this->loader->getLoadedClasses();
        $this->assertSame($expect, $actual);
    }

    public function testLoadExplicitClassMissing()
    {
        $class = 'Aura\Autoload\MissingClass';
        $file  = $this->nds(__DIR__ . '/MissingClass.php');
        $this->loader->setClassFiles(array($class => $file));

        $this->assertFalse($this->loader->loadClass($class));

        $loaded = $this->loader->getLoadedClasses();
        $this->assertFalse(isset($loaded[$class]));
    }

    public function testAddClassFiles()
    {
        $series_1 = array(
            'FooBar'  => $this->nds('/path/to/FooBar.php'),
            'BazDib'  => $this->nds('/path/to/BazDib.php'),
        );

        $series_2 = array(
            'ZimGir'  => $this->nds('/path/to/ZimGir.php'),
            'IrkDoom' => $this->nds('/path/to/IrkDoom.php'),
        );

        $expect = array(
            'FooBar'  => $this->nds('/path/to/FooBar.php'),
            'BazDib'  => $this->nds('/path/to/BazDib.php'),
            'ZimGir'  => $this->nds('/path/to/ZimGir.php'),
            'IrkDoom' => $this->nds('/path/to/IrkDoom.php'),
        );

        $this->loader->addClassFiles($series_1);
        $this->loader->addClassFiles($series_2);

        $actual = $this->loader->getClassFiles();
        $this->assertSame($expect, $actual);
    }

    public function testSetClassFiles()
    {
        $this->loader->setClassFiles(array(
            'FooBar' => $this->nds('/path/to/FooBar.php'),
            'BazDib' => $this->nds('/path/to/BazDib.php'),
            'ZimGir' => $this->nds('/path/to/ZimGir.php'),
        ));

        $this->loader->setClassFile('IrkDoom', $this->nds('/path/to/IrkDoom.php'));

        $expect = array(
            'FooBar'  => $this->nds('/path/to/FooBar.php'),
            'BazDib'  => $this->nds('/path/to/BazDib.php'),
            'ZimGir'  => $this->nds('/path/to/ZimGir.php'),
            'IrkDoom' => $this->nds('/path/to/IrkDoom.php'),
        );

        $actual = $this->loader->getClassFiles();
        $this->assertSame($expect, $actual);
    }

    public function testGetDebug()
    {
        $this->loader->addPrefix('Foo\Bar', '/path/to/foo-bar');
        $this->loader->loadClass('Foo\Bar\Baz');

        $actual = $this->loader->getDebug();

        $expect = array(
            'Loading Foo\\Bar\\Baz',
            'No explicit class file',
            'Foo\\Bar\\: /path/to/foo-bar/Baz.php not found',
            'Foo\\: no base dirs',
            'Foo\\Bar\\Baz not loaded',
        );

        $this->assertSame($expect, $actual);
    }

    public function testPsr0Loading()
    {
        $this->loader->addPrefix('Baz\Qux', __DIR__ . '/Baz/Qux');
        $actual = $this->nds($this->loader->loadClass('Baz\Qux\Quux'));
        $expect = $this->nds(__DIR__ . '/Baz/Qux/Quux.php');
        $this->assertSame($expect, $actual);
    }

    // normalize directory separators in file names for windows compatibilitys
    protected function nds($file)
    {
        return str_replace('/', DIRECTORY_SEPARATOR, $file);
    }
}
