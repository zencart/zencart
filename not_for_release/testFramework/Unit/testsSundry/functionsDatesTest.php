<?php

use Tests\Support\zcUnitTestCase;

class functionDatesTest extends zcUnitTestCase
{
    public function setup(): void
    {
        parent::setup();
        require_once DIR_FS_CATALOG . 'includes/functions/functions_dates.php';
    }

    public function testIsLeapYear()
    {
        $result = zen_is_leap_year(2000);
        $this->assertTrue($result);
        $result = zen_is_leap_year(1999);
        $this->assertFalse($result);
        $result = zen_is_leap_year(2020);
        $this->assertTrue($result);
        $result = zen_is_leap_year(2024);
        $this->assertTrue($result);
        $result = zen_is_leap_year(2100);
        $this->assertTrue($result);

    }
}
