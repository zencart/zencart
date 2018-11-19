<?php
/**
 *
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Aura\Di;

use Aura\Di\ContainerBuilder;

/**
 *
 * Use this extension of \PHPUnit_Framework_TestCase classes to test
 * configuration of services and new instances through a Container.
 *
 * @package Aura.Di
 *
 */
abstract class AbstractContainerConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     * The Container.
     *
     * @var Container
     *
     */
    protected $di;

    /**
     *
     * Sets a new Container into $this->di.
     *
     * @return null
     *
     */
    protected function setUp()
    {
        $builder = new ContainerBuilder();
        $this->di = $builder->newConfiguredInstance(
            $this->getConfigClasses(),
            $this->getAutoResolve()
        );
    }

    /**
     *
     * Returns Config classes to pass to the ContainerBuilder.
     *
     * @return array
     *
     */
    protected function getConfigClasses()
    {
        return [];
    }

    /**
     *
     * Should auto-resolution be enabled?
     *
     * @return bool
     *
     */
    protected function getAutoResolve()
    {
        return true;
    }

    /**
     *
     * Tests that a service is of the expected class.
     *
     * @param string $name The service name.
     *
     * @param string $class The expected class.
     *
     * @return null
     *
     * @dataProvider provideGet
     *
     */
    public function testGet($name, $class)
    {
        if (! $name) {
            $this->markTestSkipped('No service name passed for testGet().');
        }

        $this->assertInstanceOf(
            $class,
            $this->di->get($name)
        );
    }

    /**
     *
     * Provides data for testGet().
     *
     * @return array An array of sequential elements, where each element is
     * itself an array like ['service_name', 'ExpectedClassName'].
     *
     */
    public function provideGet()
    {
        return [
            [null, null],
        ];
    }

    /**
     *
     * Tests that a class can be instantiated through the Container.
     *
     * @param string $class The expected class.
     *
     * @return null
     *
     * @dataProvider provideNewInstance
     *
     */
    public function testNewInstance(
        $class,
        array $params = [],
        array $setters = []
    ) {
        if (! $class) {
            $this->markTestSkipped('No class name passed for testNewInstance().');
        }

        $this->assertInstanceOf(
            $class,
            $this->di->newInstance($class, $params, $setters)
        );
    }

    /**
     *
     * Provides data for testNewInstance().
     *
     * @return array An array of sequential elements, where each element is
     * itself an arra like `['ClassName', [param, param, param],
     * [setter, setter, setter]]`.
     *
     */
    public function provideNewInstance()
    {
        return [
            [null],
        ];
    }
}
