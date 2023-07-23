<?php

namespace Database\Seeders\zencart;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountriesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        DB::table('countries')->truncate();

        DB::table('countries')->insert(array(
            0 =>
                array(
                    'address_format_id' => 6,
                    'countries_id' => 1,
                    'countries_iso_code_2' => 'AF',
                    'countries_iso_code_3' => 'AFG',
                    'countries_name' => 'Afghanistan',
                    'status' => 1,
                ),
            1 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 2,
                    'countries_iso_code_2' => 'AL',
                    'countries_iso_code_3' => 'ALB',
                    'countries_name' => 'Albania',
                    'status' => 1,
                ),
            2 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 3,
                    'countries_iso_code_2' => 'DZ',
                    'countries_iso_code_3' => 'DZA',
                    'countries_name' => 'Algeria',
                    'status' => 1,
                ),
            3 =>
                array(
                    'address_format_id' => 7,
                    'countries_id' => 4,
                    'countries_iso_code_2' => 'AS',
                    'countries_iso_code_3' => 'ASM',
                    'countries_name' => 'American Samoa',
                    'status' => 1,
                ),
            4 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 5,
                    'countries_iso_code_2' => 'AD',
                    'countries_iso_code_3' => 'AND',
                    'countries_name' => 'Andorra',
                    'status' => 1,
                ),
            5 =>
                array(
                    'address_format_id' => 8,
                    'countries_id' => 6,
                    'countries_iso_code_2' => 'AO',
                    'countries_iso_code_3' => 'AGO',
                    'countries_name' => 'Angola',
                    'status' => 1,
                ),
            6 =>
                array(
                    'address_format_id' => 10,
                    'countries_id' => 7,
                    'countries_iso_code_2' => 'AI',
                    'countries_iso_code_3' => 'AIA',
                    'countries_name' => 'Anguilla',
                    'status' => 1,
                ),
            7 =>
                array(
                    'address_format_id' => 10,
                    'countries_id' => 8,
                    'countries_iso_code_2' => 'AQ',
                    'countries_iso_code_3' => 'ATA',
                    'countries_name' => 'Antarctica',
                    'status' => 1,
                ),
            8 =>
                array(
                    'address_format_id' => 8,
                    'countries_id' => 9,
                    'countries_iso_code_2' => 'AG',
                    'countries_iso_code_3' => 'ATG',
                    'countries_name' => 'Antigua and Barbuda',
                    'status' => 1,
                ),
            9 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 10,
                    'countries_iso_code_2' => 'AR',
                    'countries_iso_code_3' => 'ARG',
                    'countries_name' => 'Argentina',
                    'status' => 1,
                ),
            10 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 11,
                    'countries_iso_code_2' => 'AM',
                    'countries_iso_code_3' => 'ARM',
                    'countries_name' => 'Armenia',
                    'status' => 1,
                ),
            11 =>
                array(
                    'address_format_id' => 8,
                    'countries_id' => 12,
                    'countries_iso_code_2' => 'AW',
                    'countries_iso_code_3' => 'ABW',
                    'countries_name' => 'Aruba',
                    'status' => 1,
                ),
            12 =>
                array(
                    'address_format_id' => 7,
                    'countries_id' => 13,
                    'countries_iso_code_2' => 'AU',
                    'countries_iso_code_3' => 'AUS',
                    'countries_name' => 'Australia',
                    'status' => 1,
                ),
            13 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 14,
                    'countries_iso_code_2' => 'AT',
                    'countries_iso_code_3' => 'AUT',
                    'countries_name' => 'Austria',
                    'status' => 1,
                ),
            14 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 15,
                    'countries_iso_code_2' => 'AZ',
                    'countries_iso_code_3' => 'AZE',
                    'countries_name' => 'Azerbaijan',
                    'status' => 1,
                ),
            15 =>
                array(
                    'address_format_id' => 10,
                    'countries_id' => 16,
                    'countries_iso_code_2' => 'BS',
                    'countries_iso_code_3' => 'BHS',
                    'countries_name' => 'Bahamas',
                    'status' => 1,
                ),
            16 =>
                array(
                    'address_format_id' => 10,
                    'countries_id' => 17,
                    'countries_iso_code_2' => 'BH',
                    'countries_iso_code_3' => 'BHR',
                    'countries_name' => 'Bahrain',
                    'status' => 1,
                ),
            17 =>
                array(
                    'address_format_id' => 10,
                    'countries_id' => 18,
                    'countries_iso_code_2' => 'BD',
                    'countries_iso_code_3' => 'BGD',
                    'countries_name' => 'Bangladesh',
                    'status' => 1,
                ),
            18 =>
                array(
                    'address_format_id' => 8,
                    'countries_id' => 19,
                    'countries_iso_code_2' => 'BB',
                    'countries_iso_code_3' => 'BRB',
                    'countries_name' => 'Barbados',
                    'status' => 1,
                ),
            19 =>
                array(
                    'address_format_id' => 14,
                    'countries_id' => 20,
                    'countries_iso_code_2' => 'BY',
                    'countries_iso_code_3' => 'BLR',
                    'countries_name' => 'Belarus',
                    'status' => 1,
                ),
            20 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 21,
                    'countries_iso_code_2' => 'BE',
                    'countries_iso_code_3' => 'BEL',
                    'countries_name' => 'Belgium',
                    'status' => 1,
                ),
            21 =>
                array(
                    'address_format_id' => 10,
                    'countries_id' => 22,
                    'countries_iso_code_2' => 'BZ',
                    'countries_iso_code_3' => 'BLZ',
                    'countries_name' => 'Belize',
                    'status' => 1,
                ),
            22 =>
                array(
                    'address_format_id' => 8,
                    'countries_id' => 23,
                    'countries_iso_code_2' => 'BJ',
                    'countries_iso_code_3' => 'BEN',
                    'countries_name' => 'Benin',
                    'status' => 1,
                ),
            23 =>
                array(
                    'address_format_id' => 10,
                    'countries_id' => 24,
                    'countries_iso_code_2' => 'BM',
                    'countries_iso_code_3' => 'BMU',
                    'countries_name' => 'Bermuda',
                    'status' => 1,
                ),
            24 =>
                array(
                    'address_format_id' => 10,
                    'countries_id' => 25,
                    'countries_iso_code_2' => 'BT',
                    'countries_iso_code_3' => 'BTN',
                    'countries_name' => 'Bhutan',
                    'status' => 1,
                ),
            25 =>
                array(
                    'address_format_id' => 8,
                    'countries_id' => 26,
                    'countries_iso_code_2' => 'BO',
                    'countries_iso_code_3' => 'BOL',
                    'countries_name' => 'Bolivia',
                    'status' => 1,
                ),
            26 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 27,
                    'countries_iso_code_2' => 'BA',
                    'countries_iso_code_3' => 'BIH',
                    'countries_name' => 'Bosnia and Herzegowina',
                    'status' => 1,
                ),
            27 =>
                array(
                    'address_format_id' => 8,
                    'countries_id' => 28,
                    'countries_iso_code_2' => 'BW',
                    'countries_iso_code_3' => 'BWA',
                    'countries_name' => 'Botswana',
                    'status' => 1,
                ),
            28 =>
                array(
                    'address_format_id' => 8,
                    'countries_id' => 29,
                    'countries_iso_code_2' => 'BV',
                    'countries_iso_code_3' => 'BVT',
                    'countries_name' => 'Bouvet Island',
                    'status' => 1,
                ),
            29 =>
                array(
                    'address_format_id' => 11,
                    'countries_id' => 30,
                    'countries_iso_code_2' => 'BR',
                    'countries_iso_code_3' => 'BRA',
                    'countries_name' => 'Brazil',
                    'status' => 1,
                ),
            30 =>
                array(
                    'address_format_id' => 6,
                    'countries_id' => 31,
                    'countries_iso_code_2' => 'IO',
                    'countries_iso_code_3' => 'IOT',
                    'countries_name' => 'British Indian Ocean Territory',
                    'status' => 1,
                ),
            31 =>
                array(
                    'address_format_id' => 18,
                    'countries_id' => 32,
                    'countries_iso_code_2' => 'BN',
                    'countries_iso_code_3' => 'BRN',
                    'countries_name' => 'Brunei Darussalam',
                    'status' => 1,
                ),
            32 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 33,
                    'countries_iso_code_2' => 'BG',
                    'countries_iso_code_3' => 'BGR',
                    'countries_name' => 'Bulgaria',
                    'status' => 1,
                ),
            33 =>
                array(
                    'address_format_id' => 10,
                    'countries_id' => 34,
                    'countries_iso_code_2' => 'BF',
                    'countries_iso_code_3' => 'BFA',
                    'countries_name' => 'Burkina Faso',
                    'status' => 1,
                ),
            34 =>
                array(
                    'address_format_id' => 8,
                    'countries_id' => 35,
                    'countries_iso_code_2' => 'BI',
                    'countries_iso_code_3' => 'BDI',
                    'countries_name' => 'Burundi',
                    'status' => 1,
                ),
            35 =>
                array(
                    'address_format_id' => 7,
                    'countries_id' => 36,
                    'countries_iso_code_2' => 'KH',
                    'countries_iso_code_3' => 'KHM',
                    'countries_name' => 'Cambodia',
                    'status' => 1,
                ),
            36 =>
                array(
                    'address_format_id' => 8,
                    'countries_id' => 37,
                    'countries_iso_code_2' => 'CM',
                    'countries_iso_code_3' => 'CMR',
                    'countries_name' => 'Cameroon',
                    'status' => 1,
                ),
            37 =>
                array(
                    'address_format_id' => 7,
                    'countries_id' => 38,
                    'countries_iso_code_2' => 'CA',
                    'countries_iso_code_3' => 'CAN',
                    'countries_name' => 'Canada',
                    'status' => 1,
                ),
            38 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 39,
                    'countries_iso_code_2' => 'CV',
                    'countries_iso_code_3' => 'CPV',
                    'countries_name' => 'Cape Verde',
                    'status' => 1,
                ),
            39 =>
                array(
                    'address_format_id' => 7,
                    'countries_id' => 40,
                    'countries_iso_code_2' => 'KY',
                    'countries_iso_code_3' => 'CYM',
                    'countries_name' => 'Cayman Islands',
                    'status' => 1,
                ),
            40 =>
                array(
                    'address_format_id' => 8,
                    'countries_id' => 41,
                    'countries_iso_code_2' => 'CF',
                    'countries_iso_code_3' => 'CAF',
                    'countries_name' => 'Central African Republic',
                    'status' => 1,
                ),
            41 =>
                array(
                    'address_format_id' => 8,
                    'countries_id' => 42,
                    'countries_iso_code_2' => 'TD',
                    'countries_iso_code_3' => 'TCD',
                    'countries_name' => 'Chad',
                    'status' => 1,
                ),
            42 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 43,
                    'countries_iso_code_2' => 'CL',
                    'countries_iso_code_3' => 'CHL',
                    'countries_name' => 'Chile',
                    'status' => 1,
                ),
            43 =>
                array(
                    'address_format_id' => 7,
                    'countries_id' => 44,
                    'countries_iso_code_2' => 'CN',
                    'countries_iso_code_3' => 'CHN',
                    'countries_name' => 'China',
                    'status' => 1,
                ),
            44 =>
                array(
                    'address_format_id' => 7,
                    'countries_id' => 45,
                    'countries_iso_code_2' => 'CX',
                    'countries_iso_code_3' => 'CXR',
                    'countries_name' => 'Christmas Island',
                    'status' => 1,
                ),
            45 =>
                array(
                    'address_format_id' => 7,
                    'countries_id' => 46,
                    'countries_iso_code_2' => 'CC',
                    'countries_iso_code_3' => 'CCK',
                    'countries_name' => 'Cocos (Keeling) Islands',
                    'status' => 1,
                ),
            46 =>
                array(
                    'address_format_id' => 7,
                    'countries_id' => 47,
                    'countries_iso_code_2' => 'CO',
                    'countries_iso_code_3' => 'COL',
                    'countries_name' => 'Colombia',
                    'status' => 1,
                ),
            47 =>
                array(
                    'address_format_id' => 8,
                    'countries_id' => 48,
                    'countries_iso_code_2' => 'KM',
                    'countries_iso_code_3' => 'COM',
                    'countries_name' => 'Comoros',
                    'status' => 1,
                ),
            48 =>
                array(
                    'address_format_id' => 8,
                    'countries_id' => 49,
                    'countries_iso_code_2' => 'CG',
                    'countries_iso_code_3' => 'COG',
                    'countries_name' => 'Congo',
                    'status' => 1,
                ),
            49 =>
                array(
                    'address_format_id' => 10,
                    'countries_id' => 50,
                    'countries_iso_code_2' => 'CK',
                    'countries_iso_code_3' => 'COK',
                    'countries_name' => 'Cook Islands',
                    'status' => 1,
                ),
            50 =>
                array(
                    'address_format_id' => 11,
                    'countries_id' => 51,
                    'countries_iso_code_2' => 'CR',
                    'countries_iso_code_3' => 'CRI',
                    'countries_name' => 'Costa Rica',
                    'status' => 1,
                ),
            51 =>
                array(
                    'address_format_id' => 8,
                    'countries_id' => 52,
                    'countries_iso_code_2' => 'CI',
                    'countries_iso_code_3' => 'CIV',
                    'countries_name' => 'Côte d\'Ivoire',
                    'status' => 1,
                ),
            52 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 53,
                    'countries_iso_code_2' => 'HR',
                    'countries_iso_code_3' => 'HRV',
                    'countries_name' => 'Croatia',
                    'status' => 1,
                ),
            53 =>
                array(
                    'address_format_id' => 9,
                    'countries_id' => 54,
                    'countries_iso_code_2' => 'CU',
                    'countries_iso_code_3' => 'CUB',
                    'countries_name' => 'Cuba',
                    'status' => 1,
                ),
            54 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 55,
                    'countries_iso_code_2' => 'CY',
                    'countries_iso_code_3' => 'CYP',
                    'countries_name' => 'Cyprus',
                    'status' => 1,
                ),
            55 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 56,
                    'countries_iso_code_2' => 'CZ',
                    'countries_iso_code_3' => 'CZE',
                    'countries_name' => 'Czech Republic',
                    'status' => 1,
                ),
            56 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 57,
                    'countries_iso_code_2' => 'DK',
                    'countries_iso_code_3' => 'DNK',
                    'countries_name' => 'Denmark',
                    'status' => 1,
                ),
            57 =>
                array(
                    'address_format_id' => 8,
                    'countries_id' => 58,
                    'countries_iso_code_2' => 'DJ',
                    'countries_iso_code_3' => 'DJI',
                    'countries_name' => 'Djibouti',
                    'status' => 1,
                ),
            58 =>
                array(
                    'address_format_id' => 8,
                    'countries_id' => 59,
                    'countries_iso_code_2' => 'DM',
                    'countries_iso_code_3' => 'DMA',
                    'countries_name' => 'Dominica',
                    'status' => 1,
                ),
            59 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 60,
                    'countries_iso_code_2' => 'DO',
                    'countries_iso_code_3' => 'DOM',
                    'countries_name' => 'Dominican Republic',
                    'status' => 1,
                ),
            60 =>
                array(
                    'address_format_id' => 10,
                    'countries_id' => 61,
                    'countries_iso_code_2' => 'TL',
                    'countries_iso_code_3' => 'TLS',
                    'countries_name' => 'Timor-Leste',
                    'status' => 1,
                ),
            61 =>
                array(
                    'address_format_id' => 12,
                    'countries_id' => 62,
                    'countries_iso_code_2' => 'EC',
                    'countries_iso_code_3' => 'ECU',
                    'countries_name' => 'Ecuador',
                    'status' => 1,
                ),
            62 =>
                array(
                    'address_format_id' => 6,
                    'countries_id' => 63,
                    'countries_iso_code_2' => 'EG',
                    'countries_iso_code_3' => 'EGY',
                    'countries_name' => 'Egypt',
                    'status' => 1,
                ),
            63 =>
                array(
                    'address_format_id' => 14,
                    'countries_id' => 64,
                    'countries_iso_code_2' => 'SV',
                    'countries_iso_code_3' => 'SLV',
                    'countries_name' => 'El Salvador',
                    'status' => 1,
                ),
            64 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 65,
                    'countries_iso_code_2' => 'GQ',
                    'countries_iso_code_3' => 'GNQ',
                    'countries_name' => 'Equatorial Guinea',
                    'status' => 1,
                ),
            65 =>
                array(
                    'address_format_id' => 8,
                    'countries_id' => 66,
                    'countries_iso_code_2' => 'ER',
                    'countries_iso_code_3' => 'ERI',
                    'countries_name' => 'Eritrea',
                    'status' => 1,
                ),
            66 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 67,
                    'countries_iso_code_2' => 'EE',
                    'countries_iso_code_3' => 'EST',
                    'countries_name' => 'Estonia',
                    'status' => 1,
                ),
            67 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 68,
                    'countries_iso_code_2' => 'ET',
                    'countries_iso_code_3' => 'ETH',
                    'countries_name' => 'Ethiopia',
                    'status' => 1,
                ),
            68 =>
                array(
                    'address_format_id' => 6,
                    'countries_id' => 69,
                    'countries_iso_code_2' => 'FK',
                    'countries_iso_code_3' => 'FLK',
                    'countries_name' => 'Falkland Islands (Malvinas)',
                    'status' => 1,
                ),
            69 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 70,
                    'countries_iso_code_2' => 'FO',
                    'countries_iso_code_3' => 'FRO',
                    'countries_name' => 'Faroe Islands',
                    'status' => 1,
                ),
            70 =>
                array(
                    'address_format_id' => 8,
                    'countries_id' => 71,
                    'countries_iso_code_2' => 'FJ',
                    'countries_iso_code_3' => 'FJI',
                    'countries_name' => 'Fiji',
                    'status' => 1,
                ),
            71 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 72,
                    'countries_iso_code_2' => 'FI',
                    'countries_iso_code_3' => 'FIN',
                    'countries_name' => 'Finland',
                    'status' => 1,
                ),
            72 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 73,
                    'countries_iso_code_2' => 'FR',
                    'countries_iso_code_3' => 'FRA',
                    'countries_name' => 'France',
                    'status' => 1,
                ),
            73 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 75,
                    'countries_iso_code_2' => 'GF',
                    'countries_iso_code_3' => 'GUF',
                    'countries_name' => 'French Guiana',
                    'status' => 1,
                ),
            74 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 76,
                    'countries_iso_code_2' => 'PF',
                    'countries_iso_code_3' => 'PYF',
                    'countries_name' => 'French Polynesia',
                    'status' => 1,
                ),
            75 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 77,
                    'countries_iso_code_2' => 'TF',
                    'countries_iso_code_3' => 'ATF',
                    'countries_name' => 'French Southern Territories',
                    'status' => 1,
                ),
            76 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 78,
                    'countries_iso_code_2' => 'GA',
                    'countries_iso_code_3' => 'GAB',
                    'countries_name' => 'Gabon',
                    'status' => 1,
                ),
            77 =>
                array(
                    'address_format_id' => 8,
                    'countries_id' => 79,
                    'countries_iso_code_2' => 'GM',
                    'countries_iso_code_3' => 'GMB',
                    'countries_name' => 'Gambia',
                    'status' => 1,
                ),
            78 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 80,
                    'countries_iso_code_2' => 'GE',
                    'countries_iso_code_3' => 'GEO',
                    'countries_name' => 'Georgia',
                    'status' => 1,
                ),
            79 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 81,
                    'countries_iso_code_2' => 'DE',
                    'countries_iso_code_3' => 'DEU',
                    'countries_name' => 'Germany',
                    'status' => 1,
                ),
            80 =>
                array(
                    'address_format_id' => 11,
                    'countries_id' => 82,
                    'countries_iso_code_2' => 'GH',
                    'countries_iso_code_3' => 'GHA',
                    'countries_name' => 'Ghana',
                    'status' => 1,
                ),
            81 =>
                array(
                    'address_format_id' => 6,
                    'countries_id' => 83,
                    'countries_iso_code_2' => 'GI',
                    'countries_iso_code_3' => 'GIB',
                    'countries_name' => 'Gibraltar',
                    'status' => 1,
                ),
            82 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 84,
                    'countries_iso_code_2' => 'GR',
                    'countries_iso_code_3' => 'GRC',
                    'countries_name' => 'Greece',
                    'status' => 1,
                ),
            83 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 85,
                    'countries_iso_code_2' => 'GL',
                    'countries_iso_code_3' => 'GRL',
                    'countries_name' => 'Greenland',
                    'status' => 1,
                ),
            84 =>
                array(
                    'address_format_id' => 8,
                    'countries_id' => 86,
                    'countries_iso_code_2' => 'GD',
                    'countries_iso_code_3' => 'GRD',
                    'countries_name' => 'Grenada',
                    'status' => 1,
                ),
            85 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 87,
                    'countries_iso_code_2' => 'GP',
                    'countries_iso_code_3' => 'GLP',
                    'countries_name' => 'Guadeloupe',
                    'status' => 1,
                ),
            86 =>
                array(
                    'address_format_id' => 7,
                    'countries_id' => 88,
                    'countries_iso_code_2' => 'GU',
                    'countries_iso_code_3' => 'GUM',
                    'countries_name' => 'Guam',
                    'status' => 1,
                ),
            87 =>
                array(
                    'address_format_id' => 14,
                    'countries_id' => 89,
                    'countries_iso_code_2' => 'GT',
                    'countries_iso_code_3' => 'GTM',
                    'countries_name' => 'Guatemala',
                    'status' => 1,
                ),
            88 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 90,
                    'countries_iso_code_2' => 'GN',
                    'countries_iso_code_3' => 'GIN',
                    'countries_name' => 'Guinea',
                    'status' => 1,
                ),
            89 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 91,
                    'countries_iso_code_2' => 'GW',
                    'countries_iso_code_3' => 'GNB',
                    'countries_name' => 'Guinea-bissau',
                    'status' => 1,
                ),
            90 =>
                array(
                    'address_format_id' => 7,
                    'countries_id' => 92,
                    'countries_iso_code_2' => 'GY',
                    'countries_iso_code_3' => 'GUY',
                    'countries_name' => 'Guyana',
                    'status' => 1,
                ),
            91 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 93,
                    'countries_iso_code_2' => 'HT',
                    'countries_iso_code_3' => 'HTI',
                    'countries_name' => 'Haiti',
                    'status' => 1,
                ),
            92 =>
                array(
                    'address_format_id' => 7,
                    'countries_id' => 94,
                    'countries_iso_code_2' => 'HM',
                    'countries_iso_code_3' => 'HMD',
                    'countries_name' => 'Heard and Mc Donald Islands',
                    'status' => 1,
                ),
            93 =>
                array(
                    'address_format_id' => 9,
                    'countries_id' => 95,
                    'countries_iso_code_2' => 'HN',
                    'countries_iso_code_3' => 'HND',
                    'countries_name' => 'Honduras',
                    'status' => 1,
                ),
            94 =>
                array(
                    'address_format_id' => 8,
                    'countries_id' => 96,
                    'countries_iso_code_2' => 'HK',
                    'countries_iso_code_3' => 'HKG',
                    'countries_name' => 'Hong Kong',
                    'status' => 1,
                ),
            95 =>
                array(
                    'address_format_id' => 19,
                    'countries_id' => 97,
                    'countries_iso_code_2' => 'HU',
                    'countries_iso_code_3' => 'HUN',
                    'countries_name' => 'Hungary',
                    'status' => 1,
                ),
            96 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 98,
                    'countries_iso_code_2' => 'IS',
                    'countries_iso_code_3' => 'ISL',
                    'countries_name' => 'Iceland',
                    'status' => 1,
                ),
            97 =>
                array(
                    'address_format_id' => 6,
                    'countries_id' => 99,
                    'countries_iso_code_2' => 'IN',
                    'countries_iso_code_3' => 'IND',
                    'countries_name' => 'India',
                    'status' => 1,
                ),
            98 =>
                array(
                    'address_format_id' => 10,
                    'countries_id' => 100,
                    'countries_iso_code_2' => 'ID',
                    'countries_iso_code_3' => 'IDN',
                    'countries_name' => 'Indonesia',
                    'status' => 1,
                ),
            99 =>
                array(
                    'address_format_id' => 6,
                    'countries_id' => 101,
                    'countries_iso_code_2' => 'IR',
                    'countries_iso_code_3' => 'IRN',
                    'countries_name' => 'Iran (Islamic Republic of)',
                    'status' => 1,
                ),
            100 =>
                array(
                    'address_format_id' => 11,
                    'countries_id' => 102,
                    'countries_iso_code_2' => 'IQ',
                    'countries_iso_code_3' => 'IRQ',
                    'countries_name' => 'Iraq',
                    'status' => 1,
                ),
            101 =>
                array(
                    'address_format_id' => 6,
                    'countries_id' => 103,
                    'countries_iso_code_2' => 'IE',
                    'countries_iso_code_3' => 'IRL',
                    'countries_name' => 'Ireland',
                    'status' => 1,
                ),
            102 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 104,
                    'countries_iso_code_2' => 'IL',
                    'countries_iso_code_3' => 'ISR',
                    'countries_name' => 'Israel',
                    'status' => 1,
                ),
            103 =>
                array(
                    'address_format_id' => 9,
                    'countries_id' => 105,
                    'countries_iso_code_2' => 'IT',
                    'countries_iso_code_3' => 'ITA',
                    'countries_name' => 'Italy',
                    'status' => 1,
                ),
            104 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 106,
                    'countries_iso_code_2' => 'JM',
                    'countries_iso_code_3' => 'JAM',
                    'countries_name' => 'Jamaica',
                    'status' => 1,
                ),
            105 =>
                array(
                    'address_format_id' => 7,
                    'countries_id' => 107,
                    'countries_iso_code_2' => 'JP',
                    'countries_iso_code_3' => 'JPN',
                    'countries_name' => 'Japan',
                    'status' => 1,
                ),
            106 =>
                array(
                    'address_format_id' => 10,
                    'countries_id' => 108,
                    'countries_iso_code_2' => 'JO',
                    'countries_iso_code_3' => 'JOR',
                    'countries_name' => 'Jordan',
                    'status' => 1,
                ),
            107 =>
                array(
                    'address_format_id' => 6,
                    'countries_id' => 109,
                    'countries_iso_code_2' => 'KZ',
                    'countries_iso_code_3' => 'KAZ',
                    'countries_name' => 'Kazakhstan',
                    'status' => 1,
                ),
            108 =>
                array(
                    'address_format_id' => 6,
                    'countries_id' => 110,
                    'countries_iso_code_2' => 'KE',
                    'countries_iso_code_3' => 'KEN',
                    'countries_name' => 'Kenya',
                    'status' => 1,
                ),
            109 =>
                array(
                    'address_format_id' => 6,
                    'countries_id' => 111,
                    'countries_iso_code_2' => 'KI',
                    'countries_iso_code_3' => 'KIR',
                    'countries_name' => 'Kiribati',
                    'status' => 1,
                ),
            110 =>
                array(
                    'address_format_id' => 10,
                    'countries_id' => 112,
                    'countries_iso_code_2' => 'KP',
                    'countries_iso_code_3' => 'PRK',
                    'countries_name' => 'Korea, Democratic People\'s Republic of',
                    'status' => 1,
                ),
            111 =>
                array(
                    'address_format_id' => 7,
                    'countries_id' => 113,
                    'countries_iso_code_2' => 'KR',
                    'countries_iso_code_3' => 'KOR',
                    'countries_name' => 'Korea,  Republic of',
                    'status' => 1,
                ),
            112 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 114,
                    'countries_iso_code_2' => 'KW',
                    'countries_iso_code_3' => 'KWT',
                    'countries_name' => 'Kuwait',
                    'status' => 1,
                ),
            113 =>
                array(
                    'address_format_id' => 14,
                    'countries_id' => 115,
                    'countries_iso_code_2' => 'KG',
                    'countries_iso_code_3' => 'KGZ',
                    'countries_name' => 'Kyrgyzstan',
                    'status' => 1,
                ),
            114 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 116,
                    'countries_iso_code_2' => 'LA',
                    'countries_iso_code_3' => 'LAO',
                    'countries_name' => 'Lao People\'s Democratic Republic',
                    'status' => 1,
                ),
            115 =>
                array(
                    'address_format_id' => 2,
                    'countries_id' => 117,
                    'countries_iso_code_2' => 'LV',
                    'countries_iso_code_3' => 'LVA',
                    'countries_name' => 'Latvia',
                    'status' => 1,
                ),
            116 =>
                array(
                    'address_format_id' => 10,
                    'countries_id' => 118,
                    'countries_iso_code_2' => 'LB',
                    'countries_iso_code_3' => 'LBN',
                    'countries_name' => 'Lebanon',
                    'status' => 1,
                ),
            117 =>
                array(
                    'address_format_id' => 10,
                    'countries_id' => 119,
                    'countries_iso_code_2' => 'LS',
                    'countries_iso_code_3' => 'LSO',
                    'countries_name' => 'Lesotho',
                    'status' => 1,
                ),
            118 =>
                array(
                    'address_format_id' => 9,
                    'countries_id' => 120,
                    'countries_iso_code_2' => 'LR',
                    'countries_iso_code_3' => 'LBR',
                    'countries_name' => 'Liberia',
                    'status' => 1,
                ),
            119 =>
                array(
                    'address_format_id' => 8,
                    'countries_id' => 121,
                    'countries_iso_code_2' => 'LY',
                    'countries_iso_code_3' => 'LBY',
                    'countries_name' => 'Libya',
                    'status' => 1,
                ),
            120 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 122,
                    'countries_iso_code_2' => 'LI',
                    'countries_iso_code_3' => 'LIE',
                    'countries_name' => 'Liechtenstein',
                    'status' => 1,
                ),
            121 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 123,
                    'countries_iso_code_2' => 'LT',
                    'countries_iso_code_3' => 'LTU',
                    'countries_name' => 'Lithuania',
                    'status' => 1,
                ),
            122 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 124,
                    'countries_iso_code_2' => 'LU',
                    'countries_iso_code_3' => 'LUX',
                    'countries_name' => 'Luxembourg',
                    'status' => 1,
                ),
            123 =>
                array(
                    'address_format_id' => 8,
                    'countries_id' => 125,
                    'countries_iso_code_2' => 'MO',
                    'countries_iso_code_3' => 'MAC',
                    'countries_name' => 'Macao',
                    'status' => 1,
                ),
            124 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 126,
                    'countries_iso_code_2' => 'MK',
                    'countries_iso_code_3' => 'MKD',
                    'countries_name' => 'Macedonia, The Former Yugoslav Republic of',
                    'status' => 1,
                ),
            125 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 127,
                    'countries_iso_code_2' => 'MG',
                    'countries_iso_code_3' => 'MDG',
                    'countries_name' => 'Madagascar',
                    'status' => 1,
                ),
            126 =>
                array(
                    'address_format_id' => 8,
                    'countries_id' => 128,
                    'countries_iso_code_2' => 'MW',
                    'countries_iso_code_3' => 'MWI',
                    'countries_name' => 'Malawi',
                    'status' => 1,
                ),
            127 =>
                array(
                    'address_format_id' => 14,
                    'countries_id' => 129,
                    'countries_iso_code_2' => 'MY',
                    'countries_iso_code_3' => 'MYS',
                    'countries_name' => 'Malaysia',
                    'status' => 1,
                ),
            128 =>
                array(
                    'address_format_id' => 10,
                    'countries_id' => 130,
                    'countries_iso_code_2' => 'MV',
                    'countries_iso_code_3' => 'MDV',
                    'countries_name' => 'Maldives',
                    'status' => 1,
                ),
            129 =>
                array(
                    'address_format_id' => 8,
                    'countries_id' => 131,
                    'countries_iso_code_2' => 'ML',
                    'countries_iso_code_3' => 'MLI',
                    'countries_name' => 'Mali',
                    'status' => 1,
                ),
            130 =>
                array(
                    'address_format_id' => 6,
                    'countries_id' => 132,
                    'countries_iso_code_2' => 'MT',
                    'countries_iso_code_3' => 'MLT',
                    'countries_name' => 'Malta',
                    'status' => 1,
                ),
            131 =>
                array(
                    'address_format_id' => 7,
                    'countries_id' => 133,
                    'countries_iso_code_2' => 'MH',
                    'countries_iso_code_3' => 'MHL',
                    'countries_name' => 'Marshall Islands',
                    'status' => 1,
                ),
            132 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 134,
                    'countries_iso_code_2' => 'MQ',
                    'countries_iso_code_3' => 'MTQ',
                    'countries_name' => 'Martinique',
                    'status' => 1,
                ),
            133 =>
                array(
                    'address_format_id' => 8,
                    'countries_id' => 135,
                    'countries_iso_code_2' => 'MR',
                    'countries_iso_code_3' => 'MRT',
                    'countries_name' => 'Mauritania',
                    'status' => 1,
                ),
            134 =>
                array(
                    'address_format_id' => 8,
                    'countries_id' => 136,
                    'countries_iso_code_2' => 'MU',
                    'countries_iso_code_3' => 'MUS',
                    'countries_name' => 'Mauritius',
                    'status' => 1,
                ),
            135 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 137,
                    'countries_iso_code_2' => 'YT',
                    'countries_iso_code_3' => 'MYT',
                    'countries_name' => 'Mayotte',
                    'status' => 1,
                ),
            136 =>
                array(
                    'address_format_id' => 9,
                    'countries_id' => 138,
                    'countries_iso_code_2' => 'MX',
                    'countries_iso_code_3' => 'MEX',
                    'countries_name' => 'Mexico',
                    'status' => 1,
                ),
            137 =>
                array(
                    'address_format_id' => 7,
                    'countries_id' => 139,
                    'countries_iso_code_2' => 'FM',
                    'countries_iso_code_3' => 'FSM',
                    'countries_name' => 'Micronesia, Federated States of',
                    'status' => 1,
                ),
            138 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 140,
                    'countries_iso_code_2' => 'MD',
                    'countries_iso_code_3' => 'MDA',
                    'countries_name' => 'Moldova',
                    'status' => 1,
                ),
            139 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 141,
                    'countries_iso_code_2' => 'MC',
                    'countries_iso_code_3' => 'MCO',
                    'countries_name' => 'Monaco',
                    'status' => 1,
                ),
            140 =>
                array(
                    'address_format_id' => 10,
                    'countries_id' => 142,
                    'countries_iso_code_2' => 'MN',
                    'countries_iso_code_3' => 'MNG',
                    'countries_name' => 'Mongolia',
                    'status' => 1,
                ),
            141 =>
                array(
                    'address_format_id' => 6,
                    'countries_id' => 143,
                    'countries_iso_code_2' => 'MS',
                    'countries_iso_code_3' => 'MSR',
                    'countries_name' => 'Montserrat',
                    'status' => 1,
                ),
            142 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 144,
                    'countries_iso_code_2' => 'MA',
                    'countries_iso_code_3' => 'MAR',
                    'countries_name' => 'Morocco',
                    'status' => 1,
                ),
            143 =>
                array(
                    'address_format_id' => 14,
                    'countries_id' => 145,
                    'countries_iso_code_2' => 'MZ',
                    'countries_iso_code_3' => 'MOZ',
                    'countries_name' => 'Mozambique',
                    'status' => 1,
                ),
            144 =>
                array(
                    'address_format_id' => 2,
                    'countries_id' => 146,
                    'countries_iso_code_2' => 'MM',
                    'countries_iso_code_3' => 'MMR',
                    'countries_name' => 'Myanmar',
                    'status' => 1,
                ),
            145 =>
                array(
                    'address_format_id' => 8,
                    'countries_id' => 147,
                    'countries_iso_code_2' => 'NA',
                    'countries_iso_code_3' => 'NAM',
                    'countries_name' => 'Namibia',
                    'status' => 1,
                ),
            146 =>
                array(
                    'address_format_id' => 10,
                    'countries_id' => 148,
                    'countries_iso_code_2' => 'NR',
                    'countries_iso_code_3' => 'NRU',
                    'countries_name' => 'Nauru',
                    'status' => 1,
                ),
            147 =>
                array(
                    'address_format_id' => 10,
                    'countries_id' => 149,
                    'countries_iso_code_2' => 'NP',
                    'countries_iso_code_3' => 'NPL',
                    'countries_name' => 'Nepal',
                    'status' => 1,
                ),
            148 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 150,
                    'countries_iso_code_2' => 'NL',
                    'countries_iso_code_3' => 'NLD',
                    'countries_name' => 'Netherlands',
                    'status' => 1,
                ),
            149 =>
                array(
                    'address_format_id' => 10,
                    'countries_id' => 151,
                    'countries_iso_code_2' => 'BQ',
                    'countries_iso_code_3' => 'BES',
                    'countries_name' => 'Bonaire, Sint Eustatius and Saba',
                    'status' => 1,
                ),
            150 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 152,
                    'countries_iso_code_2' => 'NC',
                    'countries_iso_code_3' => 'NCL',
                    'countries_name' => 'New Caledonia',
                    'status' => 1,
                ),
            151 =>
                array(
                    'address_format_id' => 10,
                    'countries_id' => 153,
                    'countries_iso_code_2' => 'NZ',
                    'countries_iso_code_3' => 'NZL',
                    'countries_name' => 'New Zealand',
                    'status' => 1,
                ),
            152 =>
                array(
                    'address_format_id' => 12,
                    'countries_id' => 154,
                    'countries_iso_code_2' => 'NI',
                    'countries_iso_code_3' => 'NIC',
                    'countries_name' => 'Nicaragua',
                    'status' => 1,
                ),
            153 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 155,
                    'countries_iso_code_2' => 'NE',
                    'countries_iso_code_3' => 'NER',
                    'countries_name' => 'Niger',
                    'status' => 1,
                ),
            154 =>
                array(
                    'address_format_id' => 13,
                    'countries_id' => 156,
                    'countries_iso_code_2' => 'NG',
                    'countries_iso_code_3' => 'NGA',
                    'countries_name' => 'Nigeria',
                    'status' => 1,
                ),
            155 =>
                array(
                    'address_format_id' => 10,
                    'countries_id' => 157,
                    'countries_iso_code_2' => 'NU',
                    'countries_iso_code_3' => 'NIU',
                    'countries_name' => 'Niue',
                    'status' => 1,
                ),
            156 =>
                array(
                    'address_format_id' => 7,
                    'countries_id' => 158,
                    'countries_iso_code_2' => 'NF',
                    'countries_iso_code_3' => 'NFK',
                    'countries_name' => 'Norfolk Island',
                    'status' => 1,
                ),
            157 =>
                array(
                    'address_format_id' => 7,
                    'countries_id' => 159,
                    'countries_iso_code_2' => 'MP',
                    'countries_iso_code_3' => 'MNP',
                    'countries_name' => 'Northern Mariana Islands',
                    'status' => 1,
                ),
            158 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 160,
                    'countries_iso_code_2' => 'NO',
                    'countries_iso_code_3' => 'NOR',
                    'countries_name' => 'Norway',
                    'status' => 1,
                ),
            159 =>
                array(
                    'address_format_id' => 15,
                    'countries_id' => 161,
                    'countries_iso_code_2' => 'OM',
                    'countries_iso_code_3' => 'OMN',
                    'countries_name' => 'Oman',
                    'status' => 1,
                ),
            160 =>
                array(
                    'address_format_id' => 7,
                    'countries_id' => 162,
                    'countries_iso_code_2' => 'PK',
                    'countries_iso_code_3' => 'PAK',
                    'countries_name' => 'Pakistan',
                    'status' => 1,
                ),
            161 =>
                array(
                    'address_format_id' => 7,
                    'countries_id' => 163,
                    'countries_iso_code_2' => 'PW',
                    'countries_iso_code_3' => 'PLW',
                    'countries_name' => 'Palau',
                    'status' => 1,
                ),
            162 =>
                array(
                    'address_format_id' => 14,
                    'countries_id' => 164,
                    'countries_iso_code_2' => 'PA',
                    'countries_iso_code_3' => 'PAN',
                    'countries_name' => 'Panama',
                    'status' => 1,
                ),
            163 =>
                array(
                    'address_format_id' => 16,
                    'countries_id' => 165,
                    'countries_iso_code_2' => 'PG',
                    'countries_iso_code_3' => 'PNG',
                    'countries_name' => 'Papua New Guinea',
                    'status' => 1,
                ),
            164 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 166,
                    'countries_iso_code_2' => 'PY',
                    'countries_iso_code_3' => 'PRY',
                    'countries_name' => 'Paraguay',
                    'status' => 1,
                ),
            165 =>
                array(
                    'address_format_id' => 12,
                    'countries_id' => 167,
                    'countries_iso_code_2' => 'PE',
                    'countries_iso_code_3' => 'PER',
                    'countries_name' => 'Peru',
                    'status' => 1,
                ),
            166 =>
                array(
                    'address_format_id' => 17,
                    'countries_id' => 168,
                    'countries_iso_code_2' => 'PH',
                    'countries_iso_code_3' => 'PHL',
                    'countries_name' => 'Philippines',
                    'status' => 1,
                ),
            167 =>
                array(
                    'address_format_id' => 6,
                    'countries_id' => 169,
                    'countries_iso_code_2' => 'PN',
                    'countries_iso_code_3' => 'PCN',
                    'countries_name' => 'Pitcairn',
                    'status' => 1,
                ),
            168 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 170,
                    'countries_iso_code_2' => 'PL',
                    'countries_iso_code_3' => 'POL',
                    'countries_name' => 'Poland',
                    'status' => 1,
                ),
            169 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 171,
                    'countries_iso_code_2' => 'PT',
                    'countries_iso_code_3' => 'PRT',
                    'countries_name' => 'Portugal',
                    'status' => 1,
                ),
            170 =>
                array(
                    'address_format_id' => 7,
                    'countries_id' => 172,
                    'countries_iso_code_2' => 'PR',
                    'countries_iso_code_3' => 'PRI',
                    'countries_name' => 'Puerto Rico',
                    'status' => 1,
                ),
            171 =>
                array(
                    'address_format_id' => 8,
                    'countries_id' => 173,
                    'countries_iso_code_2' => 'QA',
                    'countries_iso_code_3' => 'QAT',
                    'countries_name' => 'Qatar',
                    'status' => 1,
                ),
            172 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 174,
                    'countries_iso_code_2' => 'RE',
                    'countries_iso_code_3' => 'REU',
                    'countries_name' => 'Réunion',
                    'status' => 1,
                ),
            173 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 175,
                    'countries_iso_code_2' => 'RO',
                    'countries_iso_code_3' => 'ROU',
                    'countries_name' => 'Romania',
                    'status' => 1,
                ),
            174 =>
                array(
                    'address_format_id' => 6,
                    'countries_id' => 176,
                    'countries_iso_code_2' => 'RU',
                    'countries_iso_code_3' => 'RUS',
                    'countries_name' => 'Russian Federation',
                    'status' => 1,
                ),
            175 =>
                array(
                    'address_format_id' => 8,
                    'countries_id' => 177,
                    'countries_iso_code_2' => 'RW',
                    'countries_iso_code_3' => 'RWA',
                    'countries_name' => 'Rwanda',
                    'status' => 1,
                ),
            176 =>
                array(
                    'address_format_id' => 2,
                    'countries_id' => 178,
                    'countries_iso_code_2' => 'KN',
                    'countries_iso_code_3' => 'KNA',
                    'countries_name' => 'Saint Kitts and Nevis',
                    'status' => 1,
                ),
            177 =>
                array(
                    'address_format_id' => 8,
                    'countries_id' => 179,
                    'countries_iso_code_2' => 'LC',
                    'countries_iso_code_3' => 'LCA',
                    'countries_name' => 'Saint Lucia',
                    'status' => 1,
                ),
            178 =>
                array(
                    'address_format_id' => 10,
                    'countries_id' => 180,
                    'countries_iso_code_2' => 'VC',
                    'countries_iso_code_3' => 'VCT',
                    'countries_name' => 'Saint Vincent and the Grenadines',
                    'status' => 1,
                ),
            179 =>
                array(
                    'address_format_id' => 8,
                    'countries_id' => 181,
                    'countries_iso_code_2' => 'WS',
                    'countries_iso_code_3' => 'WSM',
                    'countries_name' => 'Samoa',
                    'status' => 1,
                ),
            180 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 182,
                    'countries_iso_code_2' => 'SM',
                    'countries_iso_code_3' => 'SMR',
                    'countries_name' => 'San Marino',
                    'status' => 1,
                ),
            181 =>
                array(
                    'address_format_id' => 8,
                    'countries_id' => 183,
                    'countries_iso_code_2' => 'ST',
                    'countries_iso_code_3' => 'STP',
                    'countries_name' => 'Sao Tome and Principe',
                    'status' => 1,
                ),
            182 =>
                array(
                    'address_format_id' => 10,
                    'countries_id' => 184,
                    'countries_iso_code_2' => 'SA',
                    'countries_iso_code_3' => 'SAU',
                    'countries_name' => 'Saudi Arabia',
                    'status' => 1,
                ),
            183 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 185,
                    'countries_iso_code_2' => 'SN',
                    'countries_iso_code_3' => 'SEN',
                    'countries_name' => 'Senegal',
                    'status' => 1,
                ),
            184 =>
                array(
                    'address_format_id' => 6,
                    'countries_id' => 186,
                    'countries_iso_code_2' => 'SC',
                    'countries_iso_code_3' => 'SYC',
                    'countries_name' => 'Seychelles',
                    'status' => 1,
                ),
            185 =>
                array(
                    'address_format_id' => 8,
                    'countries_id' => 187,
                    'countries_iso_code_2' => 'SL',
                    'countries_iso_code_3' => 'SLE',
                    'countries_name' => 'Sierra Leone',
                    'status' => 1,
                ),
            186 =>
                array(
                    'address_format_id' => 10,
                    'countries_id' => 188,
                    'countries_iso_code_2' => 'SG',
                    'countries_iso_code_3' => 'SGP',
                    'countries_name' => 'Singapore',
                    'status' => 1,
                ),
            187 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 189,
                    'countries_iso_code_2' => 'SK',
                    'countries_iso_code_3' => 'SVK',
                    'countries_name' => 'Slovakia (Slovak Republic)',
                    'status' => 1,
                ),
            188 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 190,
                    'countries_iso_code_2' => 'SI',
                    'countries_iso_code_3' => 'SVN',
                    'countries_name' => 'Slovenia',
                    'status' => 1,
                ),
            189 =>
                array(
                    'address_format_id' => 6,
                    'countries_id' => 191,
                    'countries_iso_code_2' => 'SB',
                    'countries_iso_code_3' => 'SLB',
                    'countries_name' => 'Solomon Islands',
                    'status' => 1,
                ),
            190 =>
                array(
                    'address_format_id' => 2,
                    'countries_id' => 192,
                    'countries_iso_code_2' => 'SO',
                    'countries_iso_code_3' => 'SOM',
                    'countries_name' => 'Somalia',
                    'status' => 1,
                ),
            191 =>
                array(
                    'address_format_id' => 6,
                    'countries_id' => 193,
                    'countries_iso_code_2' => 'ZA',
                    'countries_iso_code_3' => 'ZAF',
                    'countries_name' => 'South Africa',
                    'status' => 1,
                ),
            192 =>
                array(
                    'address_format_id' => 6,
                    'countries_id' => 194,
                    'countries_iso_code_2' => 'GS',
                    'countries_iso_code_3' => 'SGS',
                    'countries_name' => 'South Georgia and the South Sandwich Islands',
                    'status' => 1,
                ),
            193 =>
                array(
                    'address_format_id' => 20,
                    'countries_id' => 195,
                    'countries_iso_code_2' => 'ES',
                    'countries_iso_code_3' => 'ESP',
                    'countries_name' => 'Spain',
                    'status' => 1,
                ),
            194 =>
                array(
                    'address_format_id' => 6,
                    'countries_id' => 196,
                    'countries_iso_code_2' => 'LK',
                    'countries_iso_code_3' => 'LKA',
                    'countries_name' => 'Sri Lanka',
                    'status' => 1,
                ),
            195 =>
                array(
                    'address_format_id' => 6,
                    'countries_id' => 197,
                    'countries_iso_code_2' => 'SH',
                    'countries_iso_code_3' => 'SHN',
                    'countries_name' => 'St. Helena',
                    'status' => 1,
                ),
            196 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 198,
                    'countries_iso_code_2' => 'PM',
                    'countries_iso_code_3' => 'SPM',
                    'countries_name' => 'St. Pierre and Miquelon',
                    'status' => 1,
                ),
            197 =>
                array(
                    'address_format_id' => 12,
                    'countries_id' => 199,
                    'countries_iso_code_2' => 'SD',
                    'countries_iso_code_3' => 'SDN',
                    'countries_name' => 'Sudan',
                    'status' => 1,
                ),
            198 =>
                array(
                    'address_format_id' => 8,
                    'countries_id' => 200,
                    'countries_iso_code_2' => 'SR',
                    'countries_iso_code_3' => 'SUR',
                    'countries_name' => 'Suriname',
                    'status' => 1,
                ),
            199 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 201,
                    'countries_iso_code_2' => 'SJ',
                    'countries_iso_code_3' => 'SJM',
                    'countries_name' => 'Svalbard and Jan Mayen Islands',
                    'status' => 1,
                ),
            200 =>
                array(
                    'address_format_id' => 6,
                    'countries_id' => 202,
                    'countries_iso_code_2' => 'SZ',
                    'countries_iso_code_3' => 'SWZ',
                    'countries_name' => 'Swaziland',
                    'status' => 1,
                ),
            201 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 203,
                    'countries_iso_code_2' => 'SE',
                    'countries_iso_code_3' => 'SWE',
                    'countries_name' => 'Sweden',
                    'status' => 1,
                ),
            202 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 204,
                    'countries_iso_code_2' => 'CH',
                    'countries_iso_code_3' => 'CHE',
                    'countries_name' => 'Switzerland',
                    'status' => 1,
                ),
            203 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 205,
                    'countries_iso_code_2' => 'SY',
                    'countries_iso_code_3' => 'SYR',
                    'countries_name' => 'Syrian Arab Republic',
                    'status' => 1,
                ),
            204 =>
                array(
                    'address_format_id' => 10,
                    'countries_id' => 206,
                    'countries_iso_code_2' => 'TW',
                    'countries_iso_code_3' => 'TWN',
                    'countries_name' => 'Taiwan',
                    'status' => 1,
                ),
            205 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 207,
                    'countries_iso_code_2' => 'TJ',
                    'countries_iso_code_3' => 'TJK',
                    'countries_name' => 'Tajikistan',
                    'status' => 1,
                ),
            206 =>
                array(
                    'address_format_id' => 14,
                    'countries_id' => 208,
                    'countries_iso_code_2' => 'TZ',
                    'countries_iso_code_3' => 'TZA',
                    'countries_name' => 'Tanzania, United Republic of',
                    'status' => 1,
                ),
            207 =>
                array(
                    'address_format_id' => 11,
                    'countries_id' => 209,
                    'countries_iso_code_2' => 'TH',
                    'countries_iso_code_3' => 'THA',
                    'countries_name' => 'Thailand',
                    'status' => 1,
                ),
            208 =>
                array(
                    'address_format_id' => 6,
                    'countries_id' => 210,
                    'countries_iso_code_2' => 'TG',
                    'countries_iso_code_3' => 'TGO',
                    'countries_name' => 'Togo',
                    'status' => 1,
                ),
            209 =>
                array(
                    'address_format_id' => 10,
                    'countries_id' => 211,
                    'countries_iso_code_2' => 'TK',
                    'countries_iso_code_3' => 'TKL',
                    'countries_name' => 'Tokelau',
                    'status' => 1,
                ),
            210 =>
                array(
                    'address_format_id' => 8,
                    'countries_id' => 212,
                    'countries_iso_code_2' => 'TO',
                    'countries_iso_code_3' => 'TON',
                    'countries_name' => 'Tonga',
                    'status' => 1,
                ),
            211 =>
                array(
                    'address_format_id' => 2,
                    'countries_id' => 213,
                    'countries_iso_code_2' => 'TT',
                    'countries_iso_code_3' => 'TTO',
                    'countries_name' => 'Trinidad and Tobago',
                    'status' => 1,
                ),
            212 =>
                array(
                    'address_format_id' => 9,
                    'countries_id' => 214,
                    'countries_iso_code_2' => 'TN',
                    'countries_iso_code_3' => 'TUN',
                    'countries_name' => 'Tunisia',
                    'status' => 1,
                ),
            213 =>
                array(
                    'address_format_id' => 9,
                    'countries_id' => 215,
                    'countries_iso_code_2' => 'TR',
                    'countries_iso_code_3' => 'TUR',
                    'countries_name' => 'Turkey',
                    'status' => 1,
                ),
            214 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 216,
                    'countries_iso_code_2' => 'TM',
                    'countries_iso_code_3' => 'TKM',
                    'countries_name' => 'Turkmenistan',
                    'status' => 1,
                ),
            215 =>
                array(
                    'address_format_id' => 6,
                    'countries_id' => 217,
                    'countries_iso_code_2' => 'TC',
                    'countries_iso_code_3' => 'TCA',
                    'countries_name' => 'Turks and Caicos Islands',
                    'status' => 1,
                ),
            216 =>
                array(
                    'address_format_id' => 6,
                    'countries_id' => 218,
                    'countries_iso_code_2' => 'TV',
                    'countries_iso_code_3' => 'TUV',
                    'countries_name' => 'Tuvalu',
                    'status' => 1,
                ),
            217 =>
                array(
                    'address_format_id' => 8,
                    'countries_id' => 219,
                    'countries_iso_code_2' => 'UG',
                    'countries_iso_code_3' => 'UGA',
                    'countries_name' => 'Uganda',
                    'status' => 1,
                ),
            218 =>
                array(
                    'address_format_id' => 6,
                    'countries_id' => 220,
                    'countries_iso_code_2' => 'UA',
                    'countries_iso_code_3' => 'UKR',
                    'countries_name' => 'Ukraine',
                    'status' => 1,
                ),
            219 =>
                array(
                    'address_format_id' => 6,
                    'countries_id' => 221,
                    'countries_iso_code_2' => 'AE',
                    'countries_iso_code_3' => 'ARE',
                    'countries_name' => 'United Arab Emirates',
                    'status' => 1,
                ),
            220 =>
                array(
                    'address_format_id' => 6,
                    'countries_id' => 222,
                    'countries_iso_code_2' => 'GB',
                    'countries_iso_code_3' => 'GBR',
                    'countries_name' => 'United Kingdom',
                    'status' => 1,
                ),
            221 =>
                array(
                    'address_format_id' => 7,
                    'countries_id' => 223,
                    'countries_iso_code_2' => 'US',
                    'countries_iso_code_3' => 'USA',
                    'countries_name' => 'United States',
                    'status' => 1,
                ),
            222 =>
                array(
                    'address_format_id' => 7,
                    'countries_id' => 224,
                    'countries_iso_code_2' => 'UM',
                    'countries_iso_code_3' => 'UMI',
                    'countries_name' => 'United States Minor Outlying Islands',
                    'status' => 1,
                ),
            223 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 225,
                    'countries_iso_code_2' => 'UY',
                    'countries_iso_code_3' => 'URY',
                    'countries_name' => 'Uruguay',
                    'status' => 1,
                ),
            224 =>
                array(
                    'address_format_id' => 6,
                    'countries_id' => 226,
                    'countries_iso_code_2' => 'UZ',
                    'countries_iso_code_3' => 'UZB',
                    'countries_name' => 'Uzbekistan',
                    'status' => 1,
                ),
            225 =>
                array(
                    'address_format_id' => 8,
                    'countries_id' => 227,
                    'countries_iso_code_2' => 'VU',
                    'countries_iso_code_3' => 'VUT',
                    'countries_name' => 'Vanuatu',
                    'status' => 1,
                ),
            226 =>
                array(
                    'address_format_id' => 9,
                    'countries_id' => 228,
                    'countries_iso_code_2' => 'VA',
                    'countries_iso_code_3' => 'VAT',
                    'countries_name' => 'Vatican City State (Holy See)',
                    'status' => 1,
                ),
            227 =>
                array(
                    'address_format_id' => 16,
                    'countries_id' => 229,
                    'countries_iso_code_2' => 'VE',
                    'countries_iso_code_3' => 'VEN',
                    'countries_name' => 'Venezuela',
                    'status' => 1,
                ),
            228 =>
                array(
                    'address_format_id' => 18,
                    'countries_id' => 230,
                    'countries_iso_code_2' => 'VN',
                    'countries_iso_code_3' => 'VNM',
                    'countries_name' => 'Viet Nam',
                    'status' => 1,
                ),
            229 =>
                array(
                    'address_format_id' => 10,
                    'countries_id' => 231,
                    'countries_iso_code_2' => 'VG',
                    'countries_iso_code_3' => 'VGB',
                    'countries_name' => 'Virgin Islands (British)',
                    'status' => 1,
                ),
            230 =>
                array(
                    'address_format_id' => 7,
                    'countries_id' => 232,
                    'countries_iso_code_2' => 'VI',
                    'countries_iso_code_3' => 'VIR',
                    'countries_name' => 'Virgin Islands (U.S.)',
                    'status' => 1,
                ),
            231 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 233,
                    'countries_iso_code_2' => 'WF',
                    'countries_iso_code_3' => 'WLF',
                    'countries_name' => 'Wallis and Futuna Islands',
                    'status' => 1,
                ),
            232 =>
                array(
                    'address_format_id' => 8,
                    'countries_id' => 234,
                    'countries_iso_code_2' => 'EH',
                    'countries_iso_code_3' => 'ESH',
                    'countries_name' => 'Western Sahara',
                    'status' => 1,
                ),
            233 =>
                array(
                    'address_format_id' => 8,
                    'countries_id' => 235,
                    'countries_iso_code_2' => 'YE',
                    'countries_iso_code_3' => 'YEM',
                    'countries_name' => 'Yemen',
                    'status' => 1,
                ),
            234 =>
                array(
                    'address_format_id' => 6,
                    'countries_id' => 236,
                    'countries_iso_code_2' => 'RS',
                    'countries_iso_code_3' => 'SRB',
                    'countries_name' => 'Serbia',
                    'status' => 1,
                ),
            235 =>
                array(
                    'address_format_id' => 10,
                    'countries_id' => 238,
                    'countries_iso_code_2' => 'ZM',
                    'countries_iso_code_3' => 'ZMB',
                    'countries_name' => 'Zambia',
                    'status' => 1,
                ),
            236 =>
                array(
                    'address_format_id' => 6,
                    'countries_id' => 239,
                    'countries_iso_code_2' => 'ZW',
                    'countries_iso_code_3' => 'ZWE',
                    'countries_name' => 'Zimbabwe',
                    'status' => 1,
                ),
            237 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 240,
                    'countries_iso_code_2' => 'AX',
                    'countries_iso_code_3' => 'ALA',
                    'countries_name' => 'Åland Islands',
                    'status' => 1,
                ),
            238 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 241,
                    'countries_iso_code_2' => 'PS',
                    'countries_iso_code_3' => 'PSE',
                    'countries_name' => 'Palestine,  State of',
                    'status' => 1,
                ),
            239 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 242,
                    'countries_iso_code_2' => 'ME',
                    'countries_iso_code_3' => 'MNE',
                    'countries_name' => 'Montenegro',
                    'status' => 1,
                ),
            240 =>
                array(
                    'address_format_id' => 6,
                    'countries_id' => 243,
                    'countries_iso_code_2' => 'GG',
                    'countries_iso_code_3' => 'GGY',
                    'countries_name' => 'Guernsey',
                    'status' => 1,
                ),
            241 =>
                array(
                    'address_format_id' => 6,
                    'countries_id' => 244,
                    'countries_iso_code_2' => 'IM',
                    'countries_iso_code_3' => 'IMN',
                    'countries_name' => 'Isle of Man',
                    'status' => 1,
                ),
            242 =>
                array(
                    'address_format_id' => 6,
                    'countries_id' => 245,
                    'countries_iso_code_2' => 'JE',
                    'countries_iso_code_3' => 'JEY',
                    'countries_name' => 'Jersey',
                    'status' => 1,
                ),
            243 =>
                array(
                    'address_format_id' => 5,
                    'countries_id' => 246,
                    'countries_iso_code_2' => 'SS',
                    'countries_iso_code_3' => 'SSD',
                    'countries_name' => 'South Sudan',
                    'status' => 1,
                ),
            244 =>
                array(
                    'address_format_id' => 7,
                    'countries_id' => 247,
                    'countries_iso_code_2' => 'CW',
                    'countries_iso_code_3' => 'CUW',
                    'countries_name' => 'Curaçao',
                    'status' => 1,
                ),
            245 =>
                array(
                    'address_format_id' => 7,
                    'countries_id' => 248,
                    'countries_iso_code_2' => 'SX',
                    'countries_iso_code_3' => 'SXM',
                    'countries_name' => 'Sint Maarten (Dutch part)',
                    'status' => 1,
                ),
        ));


    }
}
