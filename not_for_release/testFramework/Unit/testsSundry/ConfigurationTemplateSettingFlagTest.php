<?php

declare(strict_types=1);

namespace Tests\Unit\testsSundry;

use Tests\Support\zcUnitTestCase;

/**
 * The `configuration.is_template_setting` flag decides which settings the admin's
 * configuration tool can store per-template, so a new store (install SQL) and an
 * upgraded store (upgrade SQL) must end up flagging exactly the same keys - and
 * both must agree with the audit that the upgrade SQL cites as its source.
 *
 * These are file-level checks; they need no database.
 */
class ConfigurationTemplateSettingFlagTest extends zcUnitTestCase
{
    private const INSTALL_SQL = 'zc_install/sql/install/mysql_zencart.sql';
    private const UPGRADE_SQL = 'zc_install/sql/updates/mysql_upgrade_zencart_300.sql';
    private const AUDIT_DOC = 'not_for_release/dev_tools/tplSetting-conversion-audit.md';

    public function testInstallSqlDefinesTheIsTemplateSettingColumn(): void
    {
        $this->assertMatchesRegularExpression(
            '/^\s*is_template_setting\s+tinyint\s+NOT NULL\s+DEFAULT\s+0\s*,\s*$/mi',
            $this->readRepoFile(self::INSTALL_SQL),
            'The `configuration` table definition must carry the is_template_setting column.'
        );
    }

    public function testUpgradeSqlAddsTheIsTemplateSettingColumn(): void
    {
        $this->assertMatchesRegularExpression(
            '/^\s*ALTER TABLE configuration ADD is_template_setting TINYINT NOT NULL DEFAULT 0;\s*$/mi',
            $this->readRepoFile(self::UPGRADE_SQL),
            'Upgrading stores need the same column the install SQL creates.'
        );
    }

    public function testInstallAndUpgradeSqlFlagTheSameConfigurationKeys(): void
    {
        $installKeys = $this->installFlaggedKeys();
        $upgradeKeys = $this->upgradeFlaggedKeys();

        $this->assertNotEmpty($installKeys, 'Expected the install SQL to flag some template settings.');
        $this->assertSame(
            [],
            array_values(array_diff($installKeys, $upgradeKeys)),
            'Keys flagged on a fresh install but not by the 3.0.0 upgrade SQL.'
        );
        $this->assertSame(
            [],
            array_values(array_diff($upgradeKeys, $installKeys)),
            'Keys flagged by the 3.0.0 upgrade SQL but not on a fresh install.'
        );
    }

    public function testFlaggedKeysMatchTheTplSettingConversionAudit(): void
    {
        $auditKeys = $this->auditKeys();

        $this->assertNotEmpty($auditKeys, 'Expected the audit document to list some keys.');
        $this->assertSame(
            [],
            array_values(array_diff($auditKeys, $this->installFlaggedKeys())),
            'Keys documented as $tplSetting-> settings but not flagged in the install SQL.'
        );
        $this->assertSame(
            [],
            array_values(array_diff($this->installFlaggedKeys(), $auditKeys)),
            'Keys flagged in the install SQL but not documented in the audit.'
        );
    }

    public function testEveryFlaggedInstallRowSetsTheFlagToOne(): void
    {
        foreach ($this->installConfigurationInserts() as $lineNumber => $insert) {
            if (!in_array('is_template_setting', $insert['columns'], true)) {
                continue;
            }
            $position = array_search('is_template_setting', $insert['columns'], true);
            $this->assertSame(
                '1',
                trim($insert['values'][$position] ?? ''),
                'Naming is_template_setting in an INSERT only makes sense when setting it; line ' . $lineNumber
            );
        }
    }

    /**
     * @return list<string> sorted configuration_key values flagged by the install SQL
     */
    private function installFlaggedKeys(): array
    {
        $keys = [];
        foreach ($this->installConfigurationInserts() as $insert) {
            $flagPosition = array_search('is_template_setting', $insert['columns'], true);
            if ($flagPosition === false || trim($insert['values'][$flagPosition] ?? '') !== '1') {
                continue;
            }
            $keyPosition = array_search('configuration_key', $insert['columns'], true);
            $this->assertNotFalse($keyPosition, 'Every configuration INSERT names a configuration_key column.');
            $keys[] = trim($insert['values'][$keyPosition], "'");
        }

        sort($keys);
        return $keys;
    }

    /**
     * @return list<string> sorted configuration_key values flagged by the 3.0.0 upgrade SQL
     */
    private function upgradeFlaggedKeys(): array
    {
        $matched = preg_match(
            '/UPDATE configuration SET is_template_setting = 1 WHERE configuration_key IN \((.*?)\);/is',
            $this->readRepoFile(self::UPGRADE_SQL),
            $matches
        );
        $this->assertSame(1, $matched, 'The 3.0.0 upgrade SQL must flag the template settings.');

        preg_match_all("/'([^']+)'/", $matches[1], $keyMatches);
        $keys = $keyMatches[1];
        sort($keys);

        return $keys;
    }

    /**
     * @return list<string> sorted configuration_key values listed in the audit document's tables
     */
    private function auditKeys(): array
    {
        preg_match_all('/^\| `([A-Z0-9_]+)` \|/m', $this->readRepoFile(self::AUDIT_DOC), $matches);
        $keys = array_values(array_unique($matches[1]));
        sort($keys);

        return $keys;
    }

    /**
     * @return array<int, array{columns: list<string>, values: list<string>}> keyed by 1-based line number
     */
    private function installConfigurationInserts(): array
    {
        static $inserts = null;
        if ($inserts !== null) {
            return $inserts;
        }

        $inserts = [];
        foreach (file(DIR_FS_CATALOG . self::INSTALL_SQL) as $index => $line) {
            if (!preg_match('/^INSERT INTO configuration \((.*?)\)\s*VALUES\s*\((.*)\);\s*$/i', $line, $matches)) {
                continue;
            }

            $columns = array_map('trim', explode(',', $matches[1]));
            $values = $this->splitSqlValues($matches[2]);
            $this->assertCount(
                count($columns),
                $values,
                'Column/value count mismatch on line ' . ($index + 1) . ' of ' . self::INSTALL_SQL
            );

            $inserts[$index + 1] = ['columns' => $columns, 'values' => $values];
        }

        $this->assertNotEmpty($inserts, 'Expected to parse configuration INSERTs from the install SQL.');

        return $inserts;
    }

    /**
     * Splits a SQL VALUES list on its top-level commas, honouring single-quoted
     * strings and their backslash escapes.
     *
     * @return list<string>
     */
    private function splitSqlValues(string $valueList): array
    {
        $values = [];
        $current = '';
        $inQuotes = false;
        $length = strlen($valueList);

        for ($i = 0; $i < $length; $i++) {
            $character = $valueList[$i];

            if ($inQuotes && $character === '\\' && $i + 1 < $length) {
                $current .= $character . $valueList[$i + 1];
                $i++;
                continue;
            }
            if ($character === "'") {
                $inQuotes = !$inQuotes;
                $current .= $character;
                continue;
            }
            if ($character === ',' && !$inQuotes) {
                $values[] = trim($current);
                $current = '';
                continue;
            }
            $current .= $character;
        }
        $values[] = trim($current);

        return $values;
    }

    private function readRepoFile(string $relativePath): string
    {
        $contents = file_get_contents(DIR_FS_CATALOG . $relativePath);
        $this->assertIsString($contents, 'Could not read ' . $relativePath);

        return $contents;
    }
}
