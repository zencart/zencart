<?php

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\Support\zcUnitTestCase;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class functionsDatesTest extends zcUnitTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->defineDateConstants();
        $this->setDateFormatter();
        require_once DIR_FS_CATALOG . 'includes/functions/functions_dates.php';
    }

    #[DataProvider('dateRawProvider')]
    public function testDateRaw(?string $date, bool $reverse, string $expected): void
    {
        $this->assertSame($expected, zen_date_raw($date, $reverse));
    }

    public static function dateRawProvider(): array
    {
        return [
            'normal date' => ['12/31/2025', false, '20251231'],
            'reversed date' => ['12/31/2025', true, '31122025'],
            'empty date becomes zero date' => ['', false, '00010101'],
            'null date becomes zero date' => [null, false, '00010101'],
            'zero date string is normalized to zero date' => ['0001-01-01', false, '00010101'],
            'mysql zero date is normalized to zero date' => ['0000-00-00', false, '00010101'],
        ];
    }

    #[DataProvider('validDateProvider')]
    public function testValidDate(string $date, ?string $format, bool $expected): void
    {
        $this->assertSame($expected, zen_valid_date($date, $format));
    }

    public static function validDateProvider(): array
    {
        return [
            'valid default slashes' => ['02/29/2024', null, true],
            'valid default dashes' => ['02-29-2024', null, true],
            'invalid non-leap day' => ['02/29/2023', null, false],
            'valid compact custom format' => ['20240229', 'Ymd', true],
            'invalid compact custom format' => ['20230229', 'Ymd', false],
        ];
    }

    #[DataProvider('formattedDateProvider')]
    public function testLongAndShortDates(string $rawDate, string $expectedLong, string $expectedShort): void
    {
        $this->assertSame($expectedLong, zen_date_long($rawDate));
        $this->assertSame($expectedShort, zen_date_short($rawDate));
    }

    public static function formattedDateProvider(): array
    {
        return [
            'date with time' => [
                '2024-02-29 13:45:06',
                '%A %d %B, %Y|2024-02-29 13:45:06',
                '02/29/2024',
            ],
            'date only' => [
                '2024-02-29',
                '%A %d %B, %Y|2024-02-29 00:00:00',
                '02/29/2024',
            ],
        ];
    }

    #[DataProvider('emptyRawDateProvider')]
    public function testLongAndShortDatesReturnFalseForEmptyOrZeroDates(?string $rawDate): void
    {
        $this->assertFalse(zen_date_long($rawDate));
        $this->assertFalse(zen_date_short($rawDate));
    }

    public static function emptyRawDateProvider(): array
    {
        return [
            'null' => [null],
            'empty string' => [''],
            'zero date' => ['0001-01-01 00:00:00'],
            'mysql zero date' => ['0000-00-00 00:00:00'],
        ];
    }

    public function testDatetimeShort(): void
    {
        $this->assertSame('m/d/Y %H:%M:%S|2024-02-29 13:45:06', zen_datetime_short('2024-02-29 13:45:06'));
        $this->assertFalse(zen_datetime_short('0001-01-01 00:00:00'));
        $this->assertFalse(zen_datetime_short(null));
    }

    public function testDatetimeWithoutSeconds(): void
    {
        $this->assertSame('%m/%d/%Y %H:%M|2024-02-29 13:45:00', zen_datetime_without_seconds('2024-02-29 13:45:06'));
        $this->assertFalse(zen_datetime_without_seconds('0001-01-01 00:00:00'));
    }

    #[DataProvider('formatDateRawProvider')]
    public function testFormatDateRaw(string $date, string $formatOut, ?string $formatIn, string $expected): void
    {
        $this->assertSame($expected, zen_format_date_raw($date, $formatOut, $formatIn));
    }

    public static function formatDateRawProvider(): array
    {
        return [
            'admin date picker to mysql' => ['02-29-2024', 'mysql', null, '2024-02-29'],
            'admin date picker to raw' => ['02-29-2024', 'raw', null, '20240229'],
            'admin date picker to raw reverse' => ['02-29-2024', 'raw-reverse', null, '29022024'],
            'custom input format' => ['2024/02/29', 'mysql', 'yyyy/mm/dd', '2024-02-29'],
            'malformed input format returns original date' => ['2024/02/29', 'mysql', 'yyyy', '2024/02/29'],
            'null string is returned as-is' => ['null', 'mysql', null, 'null'],
            'empty string is returned as-is' => ['', 'mysql', null, ''],
        ];
    }

    #[DataProvider('checkDateProvider')]
    public function testCheckDate(string $date, string $format, bool $expected, array $expectedDateArray): void
    {
        $dateArray = [];

        $this->assertSame($expected, zen_checkdate($date, $format, $dateArray));
        $this->assertSame($expectedDateArray, $dateArray);
    }

    public static function checkDateProvider(): array
    {
        return [
            'valid slash date' => ['02/29/2024', 'mm/dd/yyyy', true, [2024, 2, 29]],
            'invalid non-leap day' => ['02/29/2023', 'mm/dd/yyyy', false, []],
            'invalid month' => ['13/01/2024', 'mm/dd/yyyy', false, []],
            'invalid day' => ['02/30/2024', 'mm/dd/yyyy', false, []],
            'compact date is rejected' => ['02292024', 'mmddyyyy', false, []],
        ];
    }

    public function testIsLeapYear(): void
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
        $this->assertFalse($result);
    }

    public function testDateDiff(): void
    {
        $this->assertSame(1, zen_date_diff('2024-02-28', '2024-02-29'));
        $this->assertSame(-1, zen_date_diff('2024-03-01', '2024-02-29'));
        $this->assertSame(366, zen_date_diff('2024-02-29 13:45:06', '2025-03-01 01:02:03'));
    }

    public function testDatetimeOverlapSupportsNullStartDates(): void
    {
        $this->assertTrue(zen_datetime_overlap(null, null));
        $this->assertTrue(zen_datetime_overlap(null, '2024-02-29 00:00:00', null, '2024-03-01 00:00:00', false));
    }

    public function testDatetimeOverlapSupportsDateSpanArrays(): void
    {
        $span1 = ['start' => '2024-02-01 00:00:00', 'end' => '2024-02-29 23:59:59'];
        $span2 = ['start' => '2024-02-15 00:00:00', 'end' => '2024-03-01 00:00:00'];
        $span3 = ['start' => '2024-03-01 00:00:00', 'end' => '2024-03-31 23:59:59'];

        $this->assertTrue(zen_datetime_overlap($span1, $span2, null, null, false));
        $this->assertFalse(zen_datetime_overlap($span1, $span3, null, null, false));
    }

    public function testDatetimeOverlapFailsSafeForMalformedDateSpanArrays(): void
    {
        $warnings = [];
        set_error_handler(function (int $errno, string $errstr) use (&$warnings): bool {
            $warnings[] = [$errno, $errstr];
            return true;
        }, E_USER_WARNING);

        try {
            $this->assertTrue(zen_datetime_overlap(['start' => '2024-02-01 00:00:00'], '2024-02-15 00:00:00'));
        } finally {
            restore_error_handler();
        }

        $this->assertSame([[E_USER_WARNING, 'Missing date/time array key(s) start and/or end.']], $warnings);
    }

    public function testCountDays(): void
    {
        $this->assertSame(3.0, zen_count_days('2024-02-01', '2024-02-05', 'd'));
        $this->assertSame(3, zen_count_days('2024-01-15', '2024-03-16', 'm'));
        $this->assertSame(0, zen_count_days('2024-01-01', '2024-01-15', 'm'));
        $this->assertSame(0, zen_count_days('2024-01-01', '2024-01-15', 'unknown'));
    }

    public function testDatetimeToSqlFormat(): void
    {
        $this->assertSame('2024-02-29 13:45:06', datetime_to_sql_format('13:45:06 Feb 29, 2024 UTC'));
    }

    public function testConvertToLocalTimeZone(): void
    {
        $this->assertSame('2024-02-29 13:45:06', convertToLocalTimeZone('2024-02-29 13:45:06', 'UTC'));
        $this->assertSame('2024-02-29 18:45:06', convertToLocalTimeZone('2024-02-29 13:45:06', 'America/Toronto'));
    }

    private function defineDateConstants(): void
    {
        $constants = [
            'DATE_FORMAT' => 'm/d/Y',
            'DATE_FORMAT_DATE_PICKER' => 'mm-dd-yy',
            'DATE_FORMAT_LONG' => '%A %d %B, %Y',
            'DATE_FORMAT_SHORT' => '%m/%d/%Y',
            'DATE_TIME_FORMAT' => 'm/d/Y %H:%M:%S',
            'DATE_TIME_FORMAT_WITHOUT_SECONDS' => '%m/%d/%Y %H:%M',
            'DOB_FORMAT_STRING' => 'mm/dd/yyyy',
        ];

        foreach ($constants as $name => $value) {
            if (!defined($name)) {
                define($name, $value);
            }
        }
    }

    private function setDateFormatter(): void
    {
        $GLOBALS['zcDate'] = new class {
            public function output(string $format, int $timestamp = 0): string
            {
                return $format . '|' . date('Y-m-d H:i:s', $timestamp);
            }
        };
    }
}
