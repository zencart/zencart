<?php

/**
 * WebTest.php
 *
 * @since     2015-02-21
 * @category  Library
 * @package   Color
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2015-2024 Nicola Asuni - Tecnick.com LTD
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-color
 *
 * This file is part of tc-lib-color software library.
 */

namespace Test;

/**
 * Web Color class test
 *
 * @since     2015-02-21
 * @category  Library
 * @package   Color
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2015-2024 Nicola Asuni - Tecnick.com LTD
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-color
 */
class WebTest extends TestUtil
{
    protected function getTestObject(): \Com\Tecnick\Color\Web
    {
        return new \Com\Tecnick\Color\Web();
    }

    public function testGetHexFromName(): void
    {
        $web = $this->getTestObject();
        $res = $web->getHexFromName('aliceblue');
        $this->assertEquals('f0f8ffff', $res);
        $res = $web->getHexFromName('color.yellowgreen');
        $this->assertEquals('9acd32ff', $res);
    }

    public function testGetHexFromNameInvalid(): void
    {
        $this->bcExpectException('\\' . \Com\Tecnick\Color\Exception::class);
        $web = $this->getTestObject();
        $web->getHexFromName('invalid');
    }

    public function testGetNameFromHex(): void
    {
        $web = $this->getTestObject();
        $res = $web->getNameFromHex('f0f8ffff');
        $this->assertEquals('aliceblue', $res);
        $res = $web->getNameFromHex('9acd32ff');
        $this->assertEquals('yellowgreen', $res);
    }

    public function testGetNameFromHexBad(): void
    {
        $this->bcExpectException('\\' . \Com\Tecnick\Color\Exception::class);
        $web = $this->getTestObject();
        $web->getNameFromHex('012345');
    }

    public function testExtractHexCode(): void
    {
        $web = $this->getTestObject();
        $res = $web->extractHexCode('abc');
        $this->assertEquals('aabbccff', $res);
        $res = $web->extractHexCode('#abc');
        $this->assertEquals('aabbccff', $res);
        $res = $web->extractHexCode('abcd');
        $this->assertEquals('aabbccdd', $res);
        $res = $web->extractHexCode('#abcd');
        $this->assertEquals('aabbccdd', $res);
        $res = $web->extractHexCode('112233');
        $this->assertEquals('112233ff', $res);
        $res = $web->extractHexCode('#112233');
        $this->assertEquals('112233ff', $res);
        $res = $web->extractHexCode('11223344');
        $this->assertEquals('11223344', $res);
        $res = $web->extractHexCode('#11223344');
        $this->assertEquals('11223344', $res);
    }

    public function testExtractHexCodeBad(): void
    {
        $this->bcExpectException('\\' . \Com\Tecnick\Color\Exception::class);
        $web = $this->getTestObject();
        $web->extractHexCode('');
    }

    public function testGetRgbObjFromHex(): void
    {
        $web = $this->getTestObject();
        $rgb = $web->getRgbObjFromHex('#87ceebff');
        $this->assertEquals('#87ceebff', $rgb->getRgbaHexColor());
    }

    public function testGetRgbObjFromHexBad(): void
    {
        $this->bcExpectException('\\' . \Com\Tecnick\Color\Exception::class);
        $web = $this->getTestObject();
        $web->getRgbObjFromHex('xx');
    }

    public function testGetRgbObjFromName(): void
    {
        $web = $this->getTestObject();
        $rgb = $web->getRgbObjFromName('skyblue');
        $this->assertEquals('#87ceebff', $rgb->getRgbaHexColor());
    }

    public function testGetRgbObjFromNameBad(): void
    {
        $this->bcExpectException('\\' . \Com\Tecnick\Color\Exception::class);
        $web = $this->getTestObject();
        $web->getRgbObjFromName('xx');
    }

    public function testNormalizeValue(): void
    {
        $web = $this->getTestObject();
        $res = $web->normalizeValue('50%', 50);
        $this->assertEquals(0.5, $res);
        $res = $web->normalizeValue(128, 255);
        $this->bcAssertEqualsWithDelta(0.5, $res);
    }

    public function testGetColorObj(): void
    {
        $web = $this->getTestObject();
        $res = $web->getColorObj('');
        $this->assertNull($res);
        $res = $web->getColorObj('t()');
        $this->assertNull($res);
        $res = $web->getColorObj('["T"]');
        $this->assertNull($res);
        $res = $web->getColorObj('transparent');
        $this->assertNull($res);
        $res = $web->getColorObj('color.transparent');
        $this->assertNull($res);
        $res = $web->getColorObj('royalblue');
        $this->assertNotNull($res);
        $this->assertEquals('#4169e1ff', $res->getRgbaHexColor());
        $res = $web->getColorObj('#1a2b3c4d');
        $this->assertNotNull($res);
        $this->assertEquals('#1a2b3c4d', $res->getRgbaHexColor());
        $res = $web->getColorObj('#1a2b3c');
        $this->assertNotNull($res);
        $this->assertEquals('#1a2b3cff', $res->getRgbaHexColor());
        $res = $web->getColorObj('#1234');
        $this->assertNotNull($res);
        $this->assertEquals('#11223344', $res->getRgbaHexColor());
        $res = $web->getColorObj('#123');
        $this->assertNotNull($res);
        $this->assertEquals('#112233ff', $res->getRgbaHexColor());
        $res = $web->getColorObj('["G",0.5]');
        $this->assertNotNull($res);
        $this->assertEquals('#808080ff', $res->getRgbaHexColor());
        $res = $web->getColorObj('["RGB",0.25,0.50,0.75]');
        $this->assertNotNull($res);
        $this->assertEquals('#4080bfff', $res->getRgbaHexColor());
        $res = $web->getColorObj('["CMYK",0.666,0.333,0,0.25]');
        $this->assertNotNull($res);
        $this->assertEquals('#4080bfff', $res->getRgbaHexColor());
        $res = $web->getColorObj('g(50%)');
        $this->assertNotNull($res);
        $this->assertEquals('#808080ff', $res->getRgbaHexColor());
        $res = $web->getColorObj('g(128)');
        $this->assertNotNull($res);
        $this->assertEquals('#808080ff', $res->getRgbaHexColor());
        $res = $web->getColorObj('rgb(25%,50%,75%)');
        $this->assertNotNull($res);
        $this->assertEquals('#4080bfff', $res->getRgbaHexColor());
        $res = $web->getColorObj('rgb(64,128,191)');
        $this->assertNotNull($res);
        $this->assertEquals('#4080bfff', $res->getRgbaHexColor());
        $res = $web->getColorObj('rgba(25%,50%,75%,0.85)');
        $this->assertNotNull($res);
        $this->assertEquals('#4080bfd9', $res->getRgbaHexColor());
        $res = $web->getColorObj('rgba(64,128,191,0.85)');
        $this->assertNotNull($res);
        $this->assertEquals('#4080bfd9', $res->getRgbaHexColor());
        $res = $web->getColorObj('hsl(210,50%,50%)');
        $this->assertNotNull($res);
        $this->assertEquals('#4080bfff', $res->getRgbaHexColor());
        $res = $web->getColorObj('hsla(210,50%,50%,0.85)');
        $this->assertNotNull($res);
        $this->assertEquals('#4080bfd9', $res->getRgbaHexColor());
        $res = $web->getColorObj('cmyk(67%,33%,0,25%)');
        $this->assertNotNull($res);
        $this->assertEquals('#3f80bfff', $res->getRgbaHexColor());
        $res = $web->getColorObj('cmyk(67,33,0,25)');
        $this->assertNotNull($res);
        $this->assertEquals('#3f80bfff', $res->getRgbaHexColor());
        $res = $web->getColorObj('cmyka(67,33,0,25,0.85)');
        $this->assertNotNull($res);
        $this->assertEquals('#3f80bfd9', $res->getRgbaHexColor());
        $res = $web->getColorObj('cmyka(67%,33%,0,25%,0.85)');
        $this->assertNotNull($res);
        $this->assertEquals('#3f80bfd9', $res->getRgbaHexColor());
    }

    /**
     * @return array<string[]>
     */
    public static function getBadColor(): array
    {
        return [['g(-)'], ['rgb(-)'], ['hsl(-)'], ['cmyk(-)']];
    }

    /**
     * @dataProvider getBadColor
     */
    public function testGetColorObjBad(string $bad): void
    {
        $this->bcExpectException('\\' . \Com\Tecnick\Color\Exception::class);
        $web = $this->getTestObject();
        $web->getColorObj($bad);
    }

    public function testGetRgbSquareDistance(): void
    {
        $web = $this->getTestObject();
        $cola = [
            'red' => 0,
            'green' => 0,
            'blue' => 0,
        ];
        $colb = [
            'red' => 1,
            'green' => 1,
            'blue' => 1,
        ];
        $dist = $web->getRgbSquareDistance($cola, $colb);
        $this->assertEquals(3, $dist);

        $cola = [
            'red' => 0.5,
            'green' => 0.5,
            'blue' => 0.5,
        ];
        $colb = [
            'red' => 0.5,
            'green' => 0.5,
            'blue' => 0.5,
        ];
        $dist = $web->getRgbSquareDistance($cola, $colb);
        $this->assertEquals(0, $dist);

        $cola = [
            'red' => 0.25,
            'green' => 0.50,
            'blue' => 0.75,
        ];
        $colb = [
            'red' => 0.50,
            'green' => 0.75,
            'blue' => 1.00,
        ];
        $dist = $web->getRgbSquareDistance($cola, $colb);
        $this->assertEquals(0.1875, $dist);
    }

    public function testGetClosestWebColor(): void
    {
        $web = $this->getTestObject();
        $col = [
            'red' => 1,
            'green' => 0,
            'blue' => 0,
        ];
        $color = $web->getClosestWebColor($col);
        $this->assertEquals('red', $color);

        $col = [
            'red' => 0,
            'green' => 1,
            'blue' => 0,
        ];
        $color = $web->getClosestWebColor($col);
        $this->assertEquals('lime', $color);

        $col = [
            'red' => 0,
            'green' => 0,
            'blue' => 1,
        ];
        $color = $web->getClosestWebColor($col);
        $this->assertEquals('blue', $color);

        $col = [
            'red' => 0.33,
            'green' => 0.4,
            'blue' => 0.18,
        ];
        $color = $web->getClosestWebColor($col);
        $this->assertEquals('darkolivegreen', $color);
    }
}
