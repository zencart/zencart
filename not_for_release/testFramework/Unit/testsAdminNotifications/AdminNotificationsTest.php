<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

use Tests\Support\zcUnitTestCase;

class AdminNotificationsTest extends zcUnitTestCase
{
    public ?AdminNotifications $an = null;
    public array $dummy = [];

    public function setUp(): void
    {
        parent::setUp();
        require_once DIR_FS_ADMIN . 'includes/classes/AdminNotifications.php';
        $this->dummy = [
        'square1' => [
            'target'        => 'payment-square',
            'banner-group'  => '1',
            'banner-html'   => null,
            'start-date'    => null,
            'end-date'      => null,
            'can-forget'    => true,
            'countries'     => ['USA', 'CAN']
        ],
        'square2' => [
            'target'        => 'payment',
            'banner-group'  => '1',
            'banner-html'   => null,
            'start-date'    => null,
            'end-date'      => null,
            'can-forget'    => false,
            'countries'     => null
        ],
        'html1'   => [
            'target'        => 'payment',
            'banner-group'  => null,
            'banner-html'   => '<strong>FOO BAR</strong>',
            'start-date'    => null,
            'end-date'      => null,
            'can-forget'    => true,
            'countries'     => ['USA', 'CAN']
        ],
        'square3' => [
            'target'        => 'payment',
            'banner-group'  => '1',
            'banner-html'   => null,
            'start-date'    => null,
            'end-date'      => null,
            'can-forget'    => true,
            'countries'     => ['USA', 'CAN']
        ],
        'square4' => [
            'target'        => 'payment',
            'banner-group'  => '1',
            'banner-html'   => null,
            'start-date'    => new DateTime(),
            'end-date'      => null,
            'can-forget'    => true,
            'countries'     => ['USA', 'CAN']
        ],
        'square5' => [
            'target'        => 'payment',
            'banner-group'  => '1',
            'banner-html'   => null,
            'start-date'    => (new DateTime())->add(new DateInterval('P1D')),
            'end-date'      => null,
            'can-forget'    => true,
            'countries'     => ['USA', 'CAN']
        ],
        'square6' => [
            'target'        => 'payment',
            'banner-group'  => '1',
            'banner-html'   => null,
            'start-date'    => null,
            'end-date'      => (new DateTime())->sub(new DateInterval('P1D')),
            'can-forget'    => true,
            'countries'     => ['USA', 'CAN']
        ],
        ];

        $this->an = $this->getMockBuilder(AdminNotifications::class)
                   ->disableOriginalConstructor()
                   ->onlyMethods(['getNotificationInfo', 'getSavedState', 'getStoreCountryIso3', 'getCurrentDate', 'pruneSavedState'])
                   ->getMock();

        $this->an->method('getNotificationInfo')->willReturn($this->dummy);

    }


    public function testBasicMock(): void
    {
        $r = $this->an->getNotifications('', 1);
        // no store country will be set as we are not mocking it so result will only return
        // notifications with no countries.
        $this->assertEmpty($r);
    }

    public function testWithSimpleLocationNoCountry(): void
    {
        $r = $this->an->getNotifications('payment', 1);
        // no store country will be set as we are not mocking it so result will only return
        // notifications with no countries.
        $this->assertCount(1, $r);
    }

    public function testWithSimpleLocationWithCountry(): void
    {
        $this->an->method('getStoreCountryIso3')->willReturn('USA');
        $this->an->method('getCurrentDate')->willReturn(new DateTime("now"));
        $r = $this->an->getNotifications('payment', 1);
        $this->assertCount(5, $r);
    }

    public function testWithComplexLocationWithCountry(): void
    {
        $this->an->method('getStoreCountryIso3')->willReturn('USA');
        $this->an->method('getCurrentDate')->willReturn(new DateTime("now"));

        $r = $this->an->getNotifications('payment-square', 1);

        $this->assertCount(1, $r);
    }

    public function testWithDateYesterday(): void
    {
        $yesterday = (new DateTime())->sub(new DateInterval('P1D'));
        $this->an->method('getStoreCountryIso3')->willReturn('USA');
        $this->an->method('getCurrentDate')->willReturn($yesterday);
        $r = $this->an->getNotifications('payment', 1);
        $this->assertCount(6, $r);
    }

    public function testWithDateTomorrow(): void
    {
        $tomorrow = (new DateTime())->add(new DateInterval('P1D'));
        $this->an->method('getStoreCountryIso3')->willReturn('USA');
        $this->an->method('getCurrentDate')->willReturn($tomorrow);
        $r = $this->an->getNotifications('payment', 1);
        $this->assertCount(4, $r);
    }
}
