<?php
namespace Aura\Web\Request;

class ValuesTest extends \PHPUnit_Framework_TestCase
{
    public function testGet()
    {
        $values = new Values(array('foo' => 'bar'));

        $actual = $values->get('foo');
        $this->assertSame('bar', $actual);

        $actual = $values->get('baz');
        $this->assertNull($actual);

        // return alt
        $actual = $values->get('baz', 'dib');
        $this->assertSame('dib', $actual);

        // return all
        $actual = $values->get();
        $this->assertSame(array('foo' => 'bar'), $actual);
    }

    public function testGetBool()
    {
        $values = new Values(array(
            'truthy' => 'y',
            'falsy' => 'off',
            'neither' => 'doom',
        ));

        $this->assertTrue($values->getBool('truthy'));
        $this->assertFalse($values->getBool('falsy'));
        $this->assertNull($values->getBool('neither'));
        $this->assertNull($values->getBool('missing'));
    }

    public function testGetInt()
    {
        $values = new Values(array(
            'int' => '88',
            'float' => '12.34',
            'string' => 'doom',
        ));

        $this->assertSame(88, $values->getInt('int'));
        $this->assertSame(12, $values->getInt('float'));
        $this->assertSame(0, $values->getInt('string'));
        $this->assertNull($values->getInt('missing'));
    }

    public function testGetFloat()
    {
        $values = new Values(array(
            'int' => '88',
            'float' => '12.34',
            'string' => 'doom',
        ));

        $this->assertSame(88.0, $values->getFloat('int'));
        $this->assertSame(12.34, $values->getFloat('float'));
        $this->assertSame(0.0, $values->getFloat('string'));
        $this->assertNull($values->getFloat('missing'));
    }
}
