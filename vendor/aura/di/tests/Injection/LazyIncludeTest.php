<?php
namespace Aura\Di\Injection;

class LazyIncludeTest extends \PHPUnit_Framework_TestCase
{
    public function test__invoke()
    {
        $file = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'lazy_array.php';
        $lazyValueStub = $this->getMockBuilder('Aura\Di\Injection\LazyInterface')
            ->getMock();
        $lazyValueStub->method('__invoke')
             ->willReturn($file);
        $lazyInclude = new LazyInclude($lazyValueStub);
        $actual = $lazyInclude->__invoke();
        $expected = ['foo' => 'bar'];
        $this->assertSame($expected, $actual);
    }
}
