<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 *
 * Coverage for zen_get_field_length_violations(), which create_account uses to reject values
 * wider than their database column before any INSERT runs (see #7983).
 */

namespace Tests\Unit\testsSundry;

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\Support\zcUnitTestCase;

#[RunTestsInSeparateProcesses]
class FieldLengthViolationsTest extends zcUnitTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        require_once DIR_FS_CATALOG . 'includes/classes/traits/NotifierManager.php';
        require_once DIR_FS_CATALOG . 'includes/classes/traits/ObserverManager.php';
        require_once DIR_FS_CATALOG . 'includes/classes/class.base.php';
        require_once DIR_FS_CATALOG . 'includes/classes/class.notifier.php';
        require_once DIR_FS_CATALOG . 'includes/classes/db/mysql/query_factory.php';
        require_once DIR_FS_CATALOG . 'includes/functions/functions_general_shared.php';

        $GLOBALS['zco_notifier'] = new \notifier();

        // SHOW COLUMNS results for the two tables create_account writes to, as queryFactoryMeta objects.
        $schemas = [
            'customers' => [
                'customers_firstname' => 'varchar(32)',
                'customers_notes' => 'text',
            ],
            'address_book' => [
                'entry_gender' => 'char(1)',
                'entry_street_address' => 'varchar(128)',
                'entry_country_id' => 'int(11)',
            ],
        ];
        $db = $this->createStub(\queryFactory::class);
        $db->method('metaColumns')->willReturnCallback(static function (string $table) use ($schemas): array {
            $columns = [];
            foreach ($schemas[$table] ?? [] as $name => $type) {
                $columns[strtoupper($name)] = new \queryFactoryMeta([
                    'Field' => $name, 'Type' => $type, 'Null' => 'NO', 'Key' => '', 'Default' => '', 'Extra' => '',
                ]);
            }
            return $columns;
        });
        $GLOBALS['db'] = $db;
    }

    public function testValuesWithinTheColumnWidthPass(): void
    {
        $this->assertSame([], \zen_get_field_length_violations([
            ['customers', 'customers_firstname', str_repeat('a', 32), 'First Name:'],
            ['address_book', 'entry_street_address', '1234 Main St', 'Street Address:'],
            ['address_book', 'entry_gender', 'm', 'Gender:'],
        ]));
    }

    public function testOverLongValuesAreReportedWithTheirLabelAndLimit(): void
    {
        $this->assertSame(
            [['First Name:', 32], ['Street Address:', 128]],
            \zen_get_field_length_violations([
                ['customers', 'customers_firstname', str_repeat('a', 33), 'First Name:'],
                ['address_book', 'entry_street_address', str_repeat('b', 129), 'Street Address:'],
                ['address_book', 'entry_gender', 'f', 'Gender:'],
            ])
        );
    }

    public function testLengthIsMeasuredInCharactersNotBytes(): void
    {
        // 32 two-byte characters fit a varchar(32); a byte count would wrongly reject them.
        $this->assertSame([], \zen_get_field_length_violations([
            ['customers', 'customers_firstname', str_repeat('é', 32), 'First Name:'],
        ]));
        $this->assertSame([['First Name:', 32]], \zen_get_field_length_violations([
            ['customers', 'customers_firstname', str_repeat('é', 33), 'First Name:'],
        ]));
    }

    public function testEmptyValuesUnknownColumnsAndNonStringColumnsAreSkipped(): void
    {
        $this->assertSame([], \zen_get_field_length_violations([
            ['customers', 'customers_firstname', '', 'First Name:'],
            ['customers', 'customers_firstname', null, 'First Name:'],
            ['customers', 'no_such_column', str_repeat('x', 500), 'Nothing:'],
            ['no_such_table', 'customers_firstname', str_repeat('x', 500), 'Nothing:'],
            // queryFactoryMeta assigns text columns a placeholder width of 8; they must not be enforced.
            ['customers', 'customers_notes', str_repeat('x', 500), 'Notes:'],
            ['address_book', 'entry_country_id', '123456789012', 'Country:'],
        ]));
    }
}
