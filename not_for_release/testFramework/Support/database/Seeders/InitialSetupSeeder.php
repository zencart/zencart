<?php

namespace Seeders;

use Tests\Support\Database\TestDb;

class InitialSetupSeeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run($mainConfigs)
    {
        $now = date('Y-m-d H:i:s');

        // set a admin login that won't trigger an expired password
        TestDb::truncate('admin');
        TestDb::insert('admin', [
            'admin_id' => 1,
            'admin_name' => 'Admin',
            'admin_email' => 'admin@localhost',
            'admin_profile' => 1,
            'admin_pass' => password_hash('password', PASSWORD_DEFAULT),
            'prev_pass1' => '',
            'prev_pass2' => '',
            'prev_pass3' => '',
            'reset_token' => '',
            'last_modified' => $now,
            'last_login_date' => $now,
            'pwd_last_change_date' => $now,
            'last_login_ip' => '',
            'failed_logins' => 0,
            'lockout_expires' => 0,
            'last_failed_attempt' => '0001-01-01 00:00:00',
            'last_failed_ip' => '',
        ]);
        // disable sending emails by  default
        self::setConfiguration('SEND_EMAILS', 'false');
        // set a valid email from address
        self::setConfiguration('MODULE_ORDER_TOTAL_LOWORDERFEE_STATUS', 'false');
        // set SSLPWSTATUSCHECK to avoid redirect on admin login
        $e = (str_starts_with(HTTP_SERVER, 'https')) ? '1' : '0';
        self::setConfiguration('SSLPWSTATUSCHECK', "$e:$e");

        // disable low order fee by default
        // @TODO

        // create a currency with 4 digits
        TestDb::insert('currencies', [
            'title' => 'Swedish Kroner',
            'code' => 'SEK',
            'symbol_left' => 'SEK',
            'symbol_right' => 'SEK',
            'decimal_point' => ',',
            'thousands_point' => '.',
            'decimal_places' => '4',
            'value' => '0.175',
        ]);

        // create a custom tax class/rate for shipping tax
        $taxClassId = TestDb::insert('tax_class', [
            'tax_class_title' => 'Taxable Shipping',
            'tax_class_description' => 'Shipping Taxable Class',
            'last_modified' => $now,
            'date_added' => $now,
        ]);

        $taxRateId = TestDb::insert('tax_rates', [
            'tax_zone_id' => 1,
            'tax_class_id' => $taxClassId,
            'tax_priority' => 1,
            'tax_rate' => 10,
            'last_modified' => $now,
            'date_added' => $now,
        ]);

        TestDb::insert('tax_rates_description', [
            'tax_rates_id' => $taxRateId,
            'language_id' => 1,
            'tax_description' => 'SHIPPING TAX 10%',
        ]);

        // see if we need to set a custom smtp server - e.g. for mailpit
        if (isset($mainConfigs['use-server']) && $mainConfigs['use-mailserver'] ) {
            self::setConfiguration('SEND_EMAILS', 'true');
            self::setConfiguration('EMAIL_TRANSPORT', 'smtp');
            self::setConfiguration('EMAIL_SMTPAUTH_MAIL_SERVER', $mainConfigs['mailserver-host'] ?? 'localhost');
            self::setConfiguration('EMAIL_SMTPAUTH_MAIL_SERVER_PORT', $mainConfigs['mailserver-port'] ?? '8025');
            self::setConfiguration('EMAIL_SMTPAUTH_MAILBOX', $mainConfigs['mailserver-user'] ?? 'ddev');
            self::setConfiguration('EMAIL_SMTPAUTH_PASSWORD', $mainConfigs['mailserver-password'] ?? 'mailpit');
        }
    }

    private static function setConfiguration(string $configKey, string $configValue): void
    {
        TestDb::update(
            'configuration',
            ['configuration_value' => $configValue],
            'configuration_key = :config_key',
            [':config_key' => $configKey]
        );
    }
}
