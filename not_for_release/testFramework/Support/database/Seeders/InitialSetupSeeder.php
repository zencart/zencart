<?php

namespace Seeders;

use App\Models\Currency;
use App\Models\TaxClass;
use App\Models\TaxRate;
use App\Models\TaxRatesDescription;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class InitialSetupSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run($mainConfigs)
    {
        // set a admin login that won't trigger an expired password
        Capsule::table('admin')->truncate();
        Capsule::table('admin')->insert(array (
            0 =>
                array (
                    'admin_id' => 1,
                    'admin_name' => 'Admin',
                    'admin_email' => 'admin@localhost',
                    'admin_profile' => 1,
                    'admin_pass' => password_hash('password', PASSWORD_DEFAULT),
                    'prev_pass1' => '',
                    'prev_pass2' => '',
                    'prev_pass3' => '',
                    'reset_token' => '',
                    'last_modified' => Carbon::now()->format('Y-m-d H:i:s'),
                    'last_login_date' => Carbon::now()->format('Y-m-d H:i:s'),
                    'pwd_last_change_date' => Carbon::now()->format('Y-m-d H:i:s'),
                    'last_login_ip' => '',
                    'failed_logins' => 0,
                    'lockout_expires' => 0,
                    'last_failed_attempt' => '0001-01-01 00:00:00',
                    'last_failed_ip' => '',
                ),
        ));
        // disable sending emails by  default
        $email = \App\Models\Configuration::where('configuration_key', 'SEND_EMAILS')->first();
        $email->configuration_value = 'false';
        $email->save();
        // set a valid email from address
        $lof = \App\Models\Configuration::where('configuration_key', 'MODULE_ORDER_TOTAL_LOWORDERFEE_STATUS')->first();
        $lof->configuration_value = 'false';
        $lof->save();
        // set SSLPWSTATUSCHECK to avoid redirect on admin login
        $statusCheck = \App\Models\Configuration::where('configuration_key', 'SSLPWSTATUSCHECK')->first();
        $e = (str_starts_with(HTTP_SERVER, 'https')) ? '1' : '0';
        $statusCheck->configuration_value = "$e:$e";
        $statusCheck->save();

        // disable low order fee by default
        // @TODO

        // create a currency with 4 digits
        $currency = new Currency([
            'title' => 'Swedish Kroner',
            'code' => 'SEK',
            'symbol_left' => 'SEK',
            'symbol_right' => 'SEK',
            'decimal_point' => ',',
            'thousands_point' => '.',
            'decimal_places' => '4',
            'value' => '0.175',
        ]);
        $currency->save();
        // create a custom tax class/rate for shipping tax
        $taxClass = new TaxClass();
        $taxClass->tax_class_title = 'Taxable Shipping';
        $taxClass->save();
        $taxRate = new TaxRate();
        $taxRate->tax_zone_id = 1;
        $taxRate->tax_class_id = $taxClass->tax_class_id;
        $taxRate->tax_priority = 1;
        $taxRate->tax_rate = 10;
        $taxRate->save();
        $taxRateDesc = new TaxRatesDescription();
        $taxRateDesc->tax_rates_id = $taxRate->tax_rates_id;
        $taxRateDesc->language_id = 1;
        $taxRateDesc->tax_description = 'SHIPPING TAX 10%';
        $taxRateDesc->save();
        // see if we need to set a custom smtp server - e.g. for mailpit
        if (isset($mainConfigs['use-server']) && $mainConfigs['use-mailserver'] ) {
            $email = \App\Models\Configuration::where('configuration_key', 'SEND_EMAILS')->first();
            $email->configuration_value = 'true';
            $email->save();
            $email = \App\Models\Configuration::where('configuration_key', 'EMAIL_TRANSPORT')->first();
            $email->configuration_value = 'smtp';
            $email->save();
            $email = \App\Models\Configuration::where('configuration_key', 'EMAIL_SMTPAUTH_MAIL_SERVER')->first();
            $email->configuration_value = $mainConfigs['mailserver-host'] ?? 'localhost';
            $email->save();
            $email = \App\Models\Configuration::where('configuration_key', 'EMAIL_SMTPAUTH_MAIL_SERVER_PORT')->first();
            $email->configuration_value = $mainConfigs['mailserver-port'] ?? '8025';
            $email->save();
            $email = \App\Models\Configuration::where('configuration_key', 'EMAIL_SMTPAUTH_MAILBOX')->first();
            $email->configuration_value = $mainConfigs['mailserver-user'] ?? 'ddev';
            $email->save();
            $email = \App\Models\Configuration::where('configuration_key', 'EMAIL_SMTPAUTH_PASSWORD')->first();
            $email->configuration_value = $mainConfigs['mailserver-password'] ?? 'mailpit';
            $email->save();
        }
    }
}

if (!function_exists('logDetails')) {
    function logDetails(string $details, string $location = "General"): void
    {
        if (!isset($_SESSION['logfilename']) || $_SESSION['logfilename'] === '') {
            $_SESSION['logfilename'] = date('m-d-Y_h-i-s-') . zen_create_random_value(6);
        }
        if ($fp = @fopen(DEBUG_LOG_FOLDER . '/zcInstallLog_' . $_SESSION['logfilename'] . '.log', 'a')) {
            fwrite($fp, '---------------' . "\n" . date('M d Y G:i') . ' -- ' . $location . "\n" . $details . "\n\n");
            fclose($fp);
        }
    }
}
