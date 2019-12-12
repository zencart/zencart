<?php
/**
 * @package tests
 * @copyright Copyright 2003-2018 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Zcwilt Thu Nov 1 17:28:42 2018 +0000 New in v1.5.6 $
 */
require_once(__DIR__ . '/../support/zcTestCase.php');

class testAdminNotifications extends zcTestCase
{
    public function setUp()
    {
        parent::setUp();
        require_once(DIR_FS_CATALOG . '/admin/includes/classes/AdminNotifications.php');
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
                   ->setMethods(['getNotificationInfo', 'getSavedState', 'getStoreCountryIso3', 'getCurrentDate', 'pruneSavedState'])
                   ->getMock();

        $this->an->method('getNotificationInfo')->willReturn($this->dummy);

    }


    public function testBasicMock()
    {
        $r = $this->an->getNotifications('', 1);
        // no store country will be set as we are not mocking it so result will only return
        // notifications with no countries.
        $this->assertTrue(count($r) == 0);
    }

    public function testWithSimpleLocationNoCountry()
    {
        $r = $this->an->getNotifications('payment', 1);
        // no store country will be set as we are not mocking it so result will only return
        // notifications with no countries.
        $this->assertTrue(count($r) == 1);
    }

    public function testWithSimpleLocationWithCountry()
    {
        $this->an->method('getStoreCountryIso3')->willReturn('USA');
        $this->an->method('getCurrentDate')->willReturn(new DateTime("now"));
        $r = $this->an->getNotifications('payment', 1);
        $this->assertTrue(count($r) == 5);
    }

    public function testWithComplexLocationWithCountry()
    {
        $this->an->method('getStoreCountryIso3')->willReturn('USA');
        $this->an->method('getCurrentDate')->willReturn(new DateTime("now"));

        $r = $this->an->getNotifications('payment-square', 1);

        $this->assertTrue(count($r) == 1);
    }

    public function testWithDateYesterday()
    {
        $datetime = (new DateTime())->sub(new DateInterval('P1D'));
        $this->an->method('getStoreCountryIso3')->willReturn('USA');
        $this->an->method('getCurrentDate')->willReturn($datetime);
        $r = $this->an->getNotifications('payment', 1);
        $this->assertTrue(count($r) == 6);
    }

    public function testWithDateTomorrow()
    {
        $datetime = (new DateTime())->add(new DateInterval('P1D'));
        $this->an->method('getStoreCountryIso3')->willReturn('USA');
        $this->an->method('getCurrentDate')->willReturn($datetime);
        $r = $this->an->getNotifications('payment', 1);
        $this->assertTrue(count($r) == 4);
    }
}
