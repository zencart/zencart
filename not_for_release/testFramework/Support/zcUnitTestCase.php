<?php
/**
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Support;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 *
 */
abstract class zcUnitTestCase extends TestCase
{
    protected $preserveGlobalState = false;

    /**
     * @return void
     *
     * set some defines where necessary
     */
    public function setUp(): void
    {
        UnitTestBootstrap::initialize();
    }


    public function mockIterator(MockObject $iteratorMock, array $items)
    {
        $iteratorData = new \stdClass();
        $iteratorData->array = $items;
        $iteratorData->position = 0;

        $iteratorMock->method('rewind')
            ->willReturnCallback(
                function () use ($iteratorData) {
                    $iteratorData->position = 0;
                }
            );

        $iteratorMock->method('current')
            ->willReturnCallback(
                function () use ($iteratorData) {
                    return $iteratorData->array[$iteratorData->position];
                }
            );

        $iteratorMock->method('key')
            ->willReturnCallback(
                function () use ($iteratorData) {
                    return $iteratorData->position;
                }
            );

        $iteratorMock->method('next')
            ->willReturnCallback(
                function () use ($iteratorData) {
                    $iteratorData->position++;
                }
            );

        $iteratorMock->method('valid')
            ->willReturnCallback(
                function () use ($iteratorData) {
                    return isset($iteratorData->array[$iteratorData->position]);
                }
            );

        $iteratorMock->method('count')
            ->willReturnCallback(
                function () use ($iteratorData) {
                    return sizeof($iteratorData->array);
                }
            );

        return $iteratorMock;
    }

    /**
     * @param $url
     * @param $expected
     * @return void
     */
    protected function assertURLGenerated($url, $expected)
    {
        return $this->assertEquals($expected, $url, 'An incorrect URL was generated.');
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }
}
