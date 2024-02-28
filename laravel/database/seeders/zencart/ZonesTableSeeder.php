<?php

namespace Database\Seeders\zencart;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ZonesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        DB::table('zones')->truncate();

        DB::table('zones')->insert(array(
            0 =>
                array(
                    'zone_code' => 'AL',
                    'zone_country_id' => 223,
                    'zone_id' => 1,
                    'zone_name' => 'Alabama',
                ),
            1 =>
                array(
                    'zone_code' => 'AK',
                    'zone_country_id' => 223,
                    'zone_id' => 2,
                    'zone_name' => 'Alaska',
                ),
            2 =>
                array(
                    'zone_code' => 'AS',
                    'zone_country_id' => 223,
                    'zone_id' => 3,
                    'zone_name' => 'American Samoa',
                ),
            3 =>
                array(
                    'zone_code' => 'AZ',
                    'zone_country_id' => 223,
                    'zone_id' => 4,
                    'zone_name' => 'Arizona',
                ),
            4 =>
                array(
                    'zone_code' => 'AR',
                    'zone_country_id' => 223,
                    'zone_id' => 5,
                    'zone_name' => 'Arkansas',
                ),
            5 =>
                array(
                    'zone_code' => 'AA',
                    'zone_country_id' => 223,
                    'zone_id' => 7,
                    'zone_name' => 'Armed Forces Americas',
                ),
            6 =>
                array(
                    'zone_code' => 'AE',
                    'zone_country_id' => 223,
                    'zone_id' => 9,
                    'zone_name' => 'Armed Forces Europe',
                ),
            7 =>
                array(
                    'zone_code' => 'AP',
                    'zone_country_id' => 223,
                    'zone_id' => 11,
                    'zone_name' => 'Armed Forces Pacific',
                ),
            8 =>
                array(
                    'zone_code' => 'CA',
                    'zone_country_id' => 223,
                    'zone_id' => 12,
                    'zone_name' => 'California',
                ),
            9 =>
                array(
                    'zone_code' => 'CO',
                    'zone_country_id' => 223,
                    'zone_id' => 13,
                    'zone_name' => 'Colorado',
                ),
            10 =>
                array(
                    'zone_code' => 'CT',
                    'zone_country_id' => 223,
                    'zone_id' => 14,
                    'zone_name' => 'Connecticut',
                ),
            11 =>
                array(
                    'zone_code' => 'DE',
                    'zone_country_id' => 223,
                    'zone_id' => 15,
                    'zone_name' => 'Delaware',
                ),
            12 =>
                array(
                    'zone_code' => 'DC',
                    'zone_country_id' => 223,
                    'zone_id' => 16,
                    'zone_name' => 'District of Columbia',
                ),
            13 =>
                array(
                    'zone_code' => 'FM',
                    'zone_country_id' => 223,
                    'zone_id' => 17,
                    'zone_name' => 'Federated States Of Micronesia',
                ),
            14 =>
                array(
                    'zone_code' => 'FL',
                    'zone_country_id' => 223,
                    'zone_id' => 18,
                    'zone_name' => 'Florida',
                ),
            15 =>
                array(
                    'zone_code' => 'GA',
                    'zone_country_id' => 223,
                    'zone_id' => 19,
                    'zone_name' => 'Georgia',
                ),
            16 =>
                array(
                    'zone_code' => 'GU',
                    'zone_country_id' => 223,
                    'zone_id' => 20,
                    'zone_name' => 'Guam',
                ),
            17 =>
                array(
                    'zone_code' => 'HI',
                    'zone_country_id' => 223,
                    'zone_id' => 21,
                    'zone_name' => 'Hawaii',
                ),
            18 =>
                array(
                    'zone_code' => 'ID',
                    'zone_country_id' => 223,
                    'zone_id' => 22,
                    'zone_name' => 'Idaho',
                ),
            19 =>
                array(
                    'zone_code' => 'IL',
                    'zone_country_id' => 223,
                    'zone_id' => 23,
                    'zone_name' => 'Illinois',
                ),
            20 =>
                array(
                    'zone_code' => 'IN',
                    'zone_country_id' => 223,
                    'zone_id' => 24,
                    'zone_name' => 'Indiana',
                ),
            21 =>
                array(
                    'zone_code' => 'IA',
                    'zone_country_id' => 223,
                    'zone_id' => 25,
                    'zone_name' => 'Iowa',
                ),
            22 =>
                array(
                    'zone_code' => 'KS',
                    'zone_country_id' => 223,
                    'zone_id' => 26,
                    'zone_name' => 'Kansas',
                ),
            23 =>
                array(
                    'zone_code' => 'KY',
                    'zone_country_id' => 223,
                    'zone_id' => 27,
                    'zone_name' => 'Kentucky',
                ),
            24 =>
                array(
                    'zone_code' => 'LA',
                    'zone_country_id' => 223,
                    'zone_id' => 28,
                    'zone_name' => 'Louisiana',
                ),
            25 =>
                array(
                    'zone_code' => 'ME',
                    'zone_country_id' => 223,
                    'zone_id' => 29,
                    'zone_name' => 'Maine',
                ),
            26 =>
                array(
                    'zone_code' => 'MH',
                    'zone_country_id' => 223,
                    'zone_id' => 30,
                    'zone_name' => 'Marshall Islands',
                ),
            27 =>
                array(
                    'zone_code' => 'MD',
                    'zone_country_id' => 223,
                    'zone_id' => 31,
                    'zone_name' => 'Maryland',
                ),
            28 =>
                array(
                    'zone_code' => 'MA',
                    'zone_country_id' => 223,
                    'zone_id' => 32,
                    'zone_name' => 'Massachusetts',
                ),
            29 =>
                array(
                    'zone_code' => 'MI',
                    'zone_country_id' => 223,
                    'zone_id' => 33,
                    'zone_name' => 'Michigan',
                ),
            30 =>
                array(
                    'zone_code' => 'MN',
                    'zone_country_id' => 223,
                    'zone_id' => 34,
                    'zone_name' => 'Minnesota',
                ),
            31 =>
                array(
                    'zone_code' => 'MS',
                    'zone_country_id' => 223,
                    'zone_id' => 35,
                    'zone_name' => 'Mississippi',
                ),
            32 =>
                array(
                    'zone_code' => 'MO',
                    'zone_country_id' => 223,
                    'zone_id' => 36,
                    'zone_name' => 'Missouri',
                ),
            33 =>
                array(
                    'zone_code' => 'MT',
                    'zone_country_id' => 223,
                    'zone_id' => 37,
                    'zone_name' => 'Montana',
                ),
            34 =>
                array(
                    'zone_code' => 'NE',
                    'zone_country_id' => 223,
                    'zone_id' => 38,
                    'zone_name' => 'Nebraska',
                ),
            35 =>
                array(
                    'zone_code' => 'NV',
                    'zone_country_id' => 223,
                    'zone_id' => 39,
                    'zone_name' => 'Nevada',
                ),
            36 =>
                array(
                    'zone_code' => 'NH',
                    'zone_country_id' => 223,
                    'zone_id' => 40,
                    'zone_name' => 'New Hampshire',
                ),
            37 =>
                array(
                    'zone_code' => 'NJ',
                    'zone_country_id' => 223,
                    'zone_id' => 41,
                    'zone_name' => 'New Jersey',
                ),
            38 =>
                array(
                    'zone_code' => 'NM',
                    'zone_country_id' => 223,
                    'zone_id' => 42,
                    'zone_name' => 'New Mexico',
                ),
            39 =>
                array(
                    'zone_code' => 'NY',
                    'zone_country_id' => 223,
                    'zone_id' => 43,
                    'zone_name' => 'New York',
                ),
            40 =>
                array(
                    'zone_code' => 'NC',
                    'zone_country_id' => 223,
                    'zone_id' => 44,
                    'zone_name' => 'North Carolina',
                ),
            41 =>
                array(
                    'zone_code' => 'ND',
                    'zone_country_id' => 223,
                    'zone_id' => 45,
                    'zone_name' => 'North Dakota',
                ),
            42 =>
                array(
                    'zone_code' => 'MP',
                    'zone_country_id' => 223,
                    'zone_id' => 46,
                    'zone_name' => 'Northern Mariana Islands',
                ),
            43 =>
                array(
                    'zone_code' => 'OH',
                    'zone_country_id' => 223,
                    'zone_id' => 47,
                    'zone_name' => 'Ohio',
                ),
            44 =>
                array(
                    'zone_code' => 'OK',
                    'zone_country_id' => 223,
                    'zone_id' => 48,
                    'zone_name' => 'Oklahoma',
                ),
            45 =>
                array(
                    'zone_code' => 'OR',
                    'zone_country_id' => 223,
                    'zone_id' => 49,
                    'zone_name' => 'Oregon',
                ),
            46 =>
                array(
                    'zone_code' => 'PW',
                    'zone_country_id' => 163,
                    'zone_id' => 50,
                    'zone_name' => 'Palau',
                ),
            47 =>
                array(
                    'zone_code' => 'PA',
                    'zone_country_id' => 223,
                    'zone_id' => 51,
                    'zone_name' => 'Pennsylvania',
                ),
            48 =>
                array(
                    'zone_code' => 'PR',
                    'zone_country_id' => 223,
                    'zone_id' => 52,
                    'zone_name' => 'Puerto Rico',
                ),
            49 =>
                array(
                    'zone_code' => 'RI',
                    'zone_country_id' => 223,
                    'zone_id' => 53,
                    'zone_name' => 'Rhode Island',
                ),
            50 =>
                array(
                    'zone_code' => 'SC',
                    'zone_country_id' => 223,
                    'zone_id' => 54,
                    'zone_name' => 'South Carolina',
                ),
            51 =>
                array(
                    'zone_code' => 'SD',
                    'zone_country_id' => 223,
                    'zone_id' => 55,
                    'zone_name' => 'South Dakota',
                ),
            52 =>
                array(
                    'zone_code' => 'TN',
                    'zone_country_id' => 223,
                    'zone_id' => 56,
                    'zone_name' => 'Tennessee',
                ),
            53 =>
                array(
                    'zone_code' => 'TX',
                    'zone_country_id' => 223,
                    'zone_id' => 57,
                    'zone_name' => 'Texas',
                ),
            54 =>
                array(
                    'zone_code' => 'UT',
                    'zone_country_id' => 223,
                    'zone_id' => 58,
                    'zone_name' => 'Utah',
                ),
            55 =>
                array(
                    'zone_code' => 'VT',
                    'zone_country_id' => 223,
                    'zone_id' => 59,
                    'zone_name' => 'Vermont',
                ),
            56 =>
                array(
                    'zone_code' => 'VI',
                    'zone_country_id' => 223,
                    'zone_id' => 60,
                    'zone_name' => 'Virgin Islands',
                ),
            57 =>
                array(
                    'zone_code' => 'VA',
                    'zone_country_id' => 223,
                    'zone_id' => 61,
                    'zone_name' => 'Virginia',
                ),
            58 =>
                array(
                    'zone_code' => 'WA',
                    'zone_country_id' => 223,
                    'zone_id' => 62,
                    'zone_name' => 'Washington',
                ),
            59 =>
                array(
                    'zone_code' => 'WV',
                    'zone_country_id' => 223,
                    'zone_id' => 63,
                    'zone_name' => 'West Virginia',
                ),
            60 =>
                array(
                    'zone_code' => 'WI',
                    'zone_country_id' => 223,
                    'zone_id' => 64,
                    'zone_name' => 'Wisconsin',
                ),
            61 =>
                array(
                    'zone_code' => 'WY',
                    'zone_country_id' => 223,
                    'zone_id' => 65,
                    'zone_name' => 'Wyoming',
                ),
            62 =>
                array(
                    'zone_code' => 'AB',
                    'zone_country_id' => 38,
                    'zone_id' => 66,
                    'zone_name' => 'Alberta',
                ),
            63 =>
                array(
                    'zone_code' => 'BC',
                    'zone_country_id' => 38,
                    'zone_id' => 67,
                    'zone_name' => 'British Columbia',
                ),
            64 =>
                array(
                    'zone_code' => 'MB',
                    'zone_country_id' => 38,
                    'zone_id' => 68,
                    'zone_name' => 'Manitoba',
                ),
            65 =>
                array(
                    'zone_code' => 'NL',
                    'zone_country_id' => 38,
                    'zone_id' => 69,
                    'zone_name' => 'Newfoundland',
                ),
            66 =>
                array(
                    'zone_code' => 'NB',
                    'zone_country_id' => 38,
                    'zone_id' => 70,
                    'zone_name' => 'New Brunswick',
                ),
            67 =>
                array(
                    'zone_code' => 'NS',
                    'zone_country_id' => 38,
                    'zone_id' => 71,
                    'zone_name' => 'Nova Scotia',
                ),
            68 =>
                array(
                    'zone_code' => 'NT',
                    'zone_country_id' => 38,
                    'zone_id' => 72,
                    'zone_name' => 'Northwest Territories',
                ),
            69 =>
                array(
                    'zone_code' => 'NU',
                    'zone_country_id' => 38,
                    'zone_id' => 73,
                    'zone_name' => 'Nunavut',
                ),
            70 =>
                array(
                    'zone_code' => 'ON',
                    'zone_country_id' => 38,
                    'zone_id' => 74,
                    'zone_name' => 'Ontario',
                ),
            71 =>
                array(
                    'zone_code' => 'PE',
                    'zone_country_id' => 38,
                    'zone_id' => 75,
                    'zone_name' => 'Prince Edward Island',
                ),
            72 =>
                array(
                    'zone_code' => 'QC',
                    'zone_country_id' => 38,
                    'zone_id' => 76,
                    'zone_name' => 'Quebec',
                ),
            73 =>
                array(
                    'zone_code' => 'SK',
                    'zone_country_id' => 38,
                    'zone_id' => 77,
                    'zone_name' => 'Saskatchewan',
                ),
            74 =>
                array(
                    'zone_code' => 'YT',
                    'zone_country_id' => 38,
                    'zone_id' => 78,
                    'zone_name' => 'Yukon Territory',
                ),
            75 =>
                array(
                    'zone_code' => 'NDS',
                    'zone_country_id' => 81,
                    'zone_id' => 79,
                    'zone_name' => 'Niedersachsen',
                ),
            76 =>
                array(
                    'zone_code' => 'BAW',
                    'zone_country_id' => 81,
                    'zone_id' => 80,
                    'zone_name' => 'Baden-Württemberg',
                ),
            77 =>
                array(
                    'zone_code' => 'BAY',
                    'zone_country_id' => 81,
                    'zone_id' => 81,
                    'zone_name' => 'Bayern',
                ),
            78 =>
                array(
                    'zone_code' => 'BER',
                    'zone_country_id' => 81,
                    'zone_id' => 82,
                    'zone_name' => 'Berlin',
                ),
            79 =>
                array(
                    'zone_code' => 'BRG',
                    'zone_country_id' => 81,
                    'zone_id' => 83,
                    'zone_name' => 'Brandenburg',
                ),
            80 =>
                array(
                    'zone_code' => 'BRE',
                    'zone_country_id' => 81,
                    'zone_id' => 84,
                    'zone_name' => 'Bremen',
                ),
            81 =>
                array(
                    'zone_code' => 'HAM',
                    'zone_country_id' => 81,
                    'zone_id' => 85,
                    'zone_name' => 'Hamburg',
                ),
            82 =>
                array(
                    'zone_code' => 'HES',
                    'zone_country_id' => 81,
                    'zone_id' => 86,
                    'zone_name' => 'Hessen',
                ),
            83 =>
                array(
                    'zone_code' => 'MEC',
                    'zone_country_id' => 81,
                    'zone_id' => 87,
                    'zone_name' => 'Mecklenburg-Vorpommern',
                ),
            84 =>
                array(
                    'zone_code' => 'NRW',
                    'zone_country_id' => 81,
                    'zone_id' => 88,
                    'zone_name' => 'Nordrhein-Westfalen',
                ),
            85 =>
                array(
                    'zone_code' => 'RHE',
                    'zone_country_id' => 81,
                    'zone_id' => 89,
                    'zone_name' => 'Rheinland-Pfalz',
                ),
            86 =>
                array(
                    'zone_code' => 'SAR',
                    'zone_country_id' => 81,
                    'zone_id' => 90,
                    'zone_name' => 'Saarland',
                ),
            87 =>
                array(
                    'zone_code' => 'SAS',
                    'zone_country_id' => 81,
                    'zone_id' => 91,
                    'zone_name' => 'Sachsen',
                ),
            88 =>
                array(
                    'zone_code' => 'SAC',
                    'zone_country_id' => 81,
                    'zone_id' => 92,
                    'zone_name' => 'Sachsen-Anhalt',
                ),
            89 =>
                array(
                    'zone_code' => 'SCN',
                    'zone_country_id' => 81,
                    'zone_id' => 93,
                    'zone_name' => 'Schleswig-Holstein',
                ),
            90 =>
                array(
                    'zone_code' => 'THE',
                    'zone_country_id' => 81,
                    'zone_id' => 94,
                    'zone_name' => 'Thüringen',
                ),
            91 =>
                array(
                    'zone_code' => 'WI',
                    'zone_country_id' => 14,
                    'zone_id' => 95,
                    'zone_name' => 'Wien',
                ),
            92 =>
                array(
                    'zone_code' => 'NO',
                    'zone_country_id' => 14,
                    'zone_id' => 96,
                    'zone_name' => 'Niederösterreich',
                ),
            93 =>
                array(
                    'zone_code' => 'OO',
                    'zone_country_id' => 14,
                    'zone_id' => 97,
                    'zone_name' => 'Oberösterreich',
                ),
            94 =>
                array(
                    'zone_code' => 'SB',
                    'zone_country_id' => 14,
                    'zone_id' => 98,
                    'zone_name' => 'Salzburg',
                ),
            95 =>
                array(
                    'zone_code' => 'KN',
                    'zone_country_id' => 14,
                    'zone_id' => 99,
                    'zone_name' => 'Kärnten',
                ),
            96 =>
                array(
                    'zone_code' => 'ST',
                    'zone_country_id' => 14,
                    'zone_id' => 100,
                    'zone_name' => 'Steiermark',
                ),
            97 =>
                array(
                    'zone_code' => 'TI',
                    'zone_country_id' => 14,
                    'zone_id' => 101,
                    'zone_name' => 'Tirol',
                ),
            98 =>
                array(
                    'zone_code' => 'BL',
                    'zone_country_id' => 14,
                    'zone_id' => 102,
                    'zone_name' => 'Burgenland',
                ),
            99 =>
                array(
                    'zone_code' => 'VB',
                    'zone_country_id' => 14,
                    'zone_id' => 103,
                    'zone_name' => 'Voralberg',
                ),
            100 =>
                array(
                    'zone_code' => 'AG',
                    'zone_country_id' => 204,
                    'zone_id' => 104,
                    'zone_name' => 'Aargau',
                ),
            101 =>
                array(
                    'zone_code' => 'AI',
                    'zone_country_id' => 204,
                    'zone_id' => 105,
                    'zone_name' => 'Appenzell Innerrhoden',
                ),
            102 =>
                array(
                    'zone_code' => 'AR',
                    'zone_country_id' => 204,
                    'zone_id' => 106,
                    'zone_name' => 'Appenzell Ausserrhoden',
                ),
            103 =>
                array(
                    'zone_code' => 'BE',
                    'zone_country_id' => 204,
                    'zone_id' => 107,
                    'zone_name' => 'Bern',
                ),
            104 =>
                array(
                    'zone_code' => 'BL',
                    'zone_country_id' => 204,
                    'zone_id' => 108,
                    'zone_name' => 'Basel-Landschaft',
                ),
            105 =>
                array(
                    'zone_code' => 'BS',
                    'zone_country_id' => 204,
                    'zone_id' => 109,
                    'zone_name' => 'Basel-Stadt',
                ),
            106 =>
                array(
                    'zone_code' => 'FR',
                    'zone_country_id' => 204,
                    'zone_id' => 110,
                    'zone_name' => 'Freiburg',
                ),
            107 =>
                array(
                    'zone_code' => 'GE',
                    'zone_country_id' => 204,
                    'zone_id' => 111,
                    'zone_name' => 'Genf',
                ),
            108 =>
                array(
                    'zone_code' => 'GL',
                    'zone_country_id' => 204,
                    'zone_id' => 112,
                    'zone_name' => 'Glarus',
                ),
            109 =>
                array(
                    'zone_code' => 'JU',
                    'zone_country_id' => 204,
                    'zone_id' => 113,
                    'zone_name' => 'Graubnden',
                ),
            110 =>
                array(
                    'zone_code' => 'JU',
                    'zone_country_id' => 204,
                    'zone_id' => 114,
                    'zone_name' => 'Jura',
                ),
            111 =>
                array(
                    'zone_code' => 'LU',
                    'zone_country_id' => 204,
                    'zone_id' => 115,
                    'zone_name' => 'Luzern',
                ),
            112 =>
                array(
                    'zone_code' => 'NE',
                    'zone_country_id' => 204,
                    'zone_id' => 116,
                    'zone_name' => 'Neuenburg',
                ),
            113 =>
                array(
                    'zone_code' => 'NW',
                    'zone_country_id' => 204,
                    'zone_id' => 117,
                    'zone_name' => 'Nidwalden',
                ),
            114 =>
                array(
                    'zone_code' => 'OW',
                    'zone_country_id' => 204,
                    'zone_id' => 118,
                    'zone_name' => 'Obwalden',
                ),
            115 =>
                array(
                    'zone_code' => 'SG',
                    'zone_country_id' => 204,
                    'zone_id' => 119,
                    'zone_name' => 'St. Gallen',
                ),
            116 =>
                array(
                    'zone_code' => 'SH',
                    'zone_country_id' => 204,
                    'zone_id' => 120,
                    'zone_name' => 'Schaffhausen',
                ),
            117 =>
                array(
                    'zone_code' => 'SO',
                    'zone_country_id' => 204,
                    'zone_id' => 121,
                    'zone_name' => 'Solothurn',
                ),
            118 =>
                array(
                    'zone_code' => 'SZ',
                    'zone_country_id' => 204,
                    'zone_id' => 122,
                    'zone_name' => 'Schwyz',
                ),
            119 =>
                array(
                    'zone_code' => 'TG',
                    'zone_country_id' => 204,
                    'zone_id' => 123,
                    'zone_name' => 'Thurgau',
                ),
            120 =>
                array(
                    'zone_code' => 'TI',
                    'zone_country_id' => 204,
                    'zone_id' => 124,
                    'zone_name' => 'Tessin',
                ),
            121 =>
                array(
                    'zone_code' => 'UR',
                    'zone_country_id' => 204,
                    'zone_id' => 125,
                    'zone_name' => 'Uri',
                ),
            122 =>
                array(
                    'zone_code' => 'VD',
                    'zone_country_id' => 204,
                    'zone_id' => 126,
                    'zone_name' => 'Waadt',
                ),
            123 =>
                array(
                    'zone_code' => 'VS',
                    'zone_country_id' => 204,
                    'zone_id' => 127,
                    'zone_name' => 'Wallis',
                ),
            124 =>
                array(
                    'zone_code' => 'ZG',
                    'zone_country_id' => 204,
                    'zone_id' => 128,
                    'zone_name' => 'Zug',
                ),
            125 =>
                array(
                    'zone_code' => 'ZH',
                    'zone_country_id' => 204,
                    'zone_id' => 129,
                    'zone_name' => 'Zürich',
                ),
            126 =>
                array(
                    'zone_code' => 'A Coruña',
                    'zone_country_id' => 195,
                    'zone_id' => 130,
                    'zone_name' => 'A Coruña',
                ),
            127 =>
                array(
                    'zone_code' => 'Álava',
                    'zone_country_id' => 195,
                    'zone_id' => 131,
                    'zone_name' => 'Álava',
                ),
            128 =>
                array(
                    'zone_code' => 'Albacete',
                    'zone_country_id' => 195,
                    'zone_id' => 132,
                    'zone_name' => 'Albacete',
                ),
            129 =>
                array(
                    'zone_code' => 'Alicante',
                    'zone_country_id' => 195,
                    'zone_id' => 133,
                    'zone_name' => 'Alicante',
                ),
            130 =>
                array(
                    'zone_code' => 'Almería',
                    'zone_country_id' => 195,
                    'zone_id' => 134,
                    'zone_name' => 'Almería',
                ),
            131 =>
                array(
                    'zone_code' => 'Asturias',
                    'zone_country_id' => 195,
                    'zone_id' => 135,
                    'zone_name' => 'Asturias',
                ),
            132 =>
                array(
                    'zone_code' => 'Ávila',
                    'zone_country_id' => 195,
                    'zone_id' => 136,
                    'zone_name' => 'Ávila',
                ),
            133 =>
                array(
                    'zone_code' => 'Badajoz',
                    'zone_country_id' => 195,
                    'zone_id' => 137,
                    'zone_name' => 'Badajoz',
                ),
            134 =>
                array(
                    'zone_code' => 'Baleares',
                    'zone_country_id' => 195,
                    'zone_id' => 138,
                    'zone_name' => 'Baleares',
                ),
            135 =>
                array(
                    'zone_code' => 'Barcelona',
                    'zone_country_id' => 195,
                    'zone_id' => 139,
                    'zone_name' => 'Barcelona',
                ),
            136 =>
                array(
                    'zone_code' => 'Burgos',
                    'zone_country_id' => 195,
                    'zone_id' => 140,
                    'zone_name' => 'Burgos',
                ),
            137 =>
                array(
                    'zone_code' => 'Cáceres',
                    'zone_country_id' => 195,
                    'zone_id' => 141,
                    'zone_name' => 'Cáceres',
                ),
            138 =>
                array(
                    'zone_code' => 'Cádiz',
                    'zone_country_id' => 195,
                    'zone_id' => 142,
                    'zone_name' => 'Cádiz',
                ),
            139 =>
                array(
                    'zone_code' => 'Cantabria',
                    'zone_country_id' => 195,
                    'zone_id' => 143,
                    'zone_name' => 'Cantabria',
                ),
            140 =>
                array(
                    'zone_code' => 'Castellón',
                    'zone_country_id' => 195,
                    'zone_id' => 144,
                    'zone_name' => 'Castellón',
                ),
            141 =>
                array(
                    'zone_code' => 'Ceuta',
                    'zone_country_id' => 195,
                    'zone_id' => 145,
                    'zone_name' => 'Ceuta',
                ),
            142 =>
                array(
                    'zone_code' => 'Ciudad Real',
                    'zone_country_id' => 195,
                    'zone_id' => 146,
                    'zone_name' => 'Ciudad Real',
                ),
            143 =>
                array(
                    'zone_code' => 'Córdoba',
                    'zone_country_id' => 195,
                    'zone_id' => 147,
                    'zone_name' => 'Córdoba',
                ),
            144 =>
                array(
                    'zone_code' => 'Cuenca',
                    'zone_country_id' => 195,
                    'zone_id' => 148,
                    'zone_name' => 'Cuenca',
                ),
            145 =>
                array(
                    'zone_code' => 'Girona',
                    'zone_country_id' => 195,
                    'zone_id' => 149,
                    'zone_name' => 'Girona',
                ),
            146 =>
                array(
                    'zone_code' => 'Granada',
                    'zone_country_id' => 195,
                    'zone_id' => 150,
                    'zone_name' => 'Granada',
                ),
            147 =>
                array(
                    'zone_code' => 'Guadalajara',
                    'zone_country_id' => 195,
                    'zone_id' => 151,
                    'zone_name' => 'Guadalajara',
                ),
            148 =>
                array(
                    'zone_code' => 'Guipúzcoa',
                    'zone_country_id' => 195,
                    'zone_id' => 152,
                    'zone_name' => 'Guipúzcoa',
                ),
            149 =>
                array(
                    'zone_code' => 'Huelva',
                    'zone_country_id' => 195,
                    'zone_id' => 153,
                    'zone_name' => 'Huelva',
                ),
            150 =>
                array(
                    'zone_code' => 'Huesca',
                    'zone_country_id' => 195,
                    'zone_id' => 154,
                    'zone_name' => 'Huesca',
                ),
            151 =>
                array(
                    'zone_code' => 'Jaén',
                    'zone_country_id' => 195,
                    'zone_id' => 155,
                    'zone_name' => 'Jaén',
                ),
            152 =>
                array(
                    'zone_code' => 'La Rioja',
                    'zone_country_id' => 195,
                    'zone_id' => 156,
                    'zone_name' => 'La Rioja',
                ),
            153 =>
                array(
                    'zone_code' => 'Las Palmas',
                    'zone_country_id' => 195,
                    'zone_id' => 157,
                    'zone_name' => 'Las Palmas',
                ),
            154 =>
                array(
                    'zone_code' => 'León',
                    'zone_country_id' => 195,
                    'zone_id' => 158,
                    'zone_name' => 'León',
                ),
            155 =>
                array(
                    'zone_code' => 'Lérida',
                    'zone_country_id' => 195,
                    'zone_id' => 159,
                    'zone_name' => 'Lérida',
                ),
            156 =>
                array(
                    'zone_code' => 'Lugo',
                    'zone_country_id' => 195,
                    'zone_id' => 160,
                    'zone_name' => 'Lugo',
                ),
            157 =>
                array(
                    'zone_code' => 'Madrid',
                    'zone_country_id' => 195,
                    'zone_id' => 161,
                    'zone_name' => 'Madrid',
                ),
            158 =>
                array(
                    'zone_code' => 'Málaga',
                    'zone_country_id' => 195,
                    'zone_id' => 162,
                    'zone_name' => 'Málaga',
                ),
            159 =>
                array(
                    'zone_code' => 'Melilla',
                    'zone_country_id' => 195,
                    'zone_id' => 163,
                    'zone_name' => 'Melilla',
                ),
            160 =>
                array(
                    'zone_code' => 'Murcia',
                    'zone_country_id' => 195,
                    'zone_id' => 164,
                    'zone_name' => 'Murcia',
                ),
            161 =>
                array(
                    'zone_code' => 'Navarra',
                    'zone_country_id' => 195,
                    'zone_id' => 165,
                    'zone_name' => 'Navarra',
                ),
            162 =>
                array(
                    'zone_code' => 'Ourense',
                    'zone_country_id' => 195,
                    'zone_id' => 166,
                    'zone_name' => 'Ourense',
                ),
            163 =>
                array(
                    'zone_code' => 'Palencia',
                    'zone_country_id' => 195,
                    'zone_id' => 167,
                    'zone_name' => 'Palencia',
                ),
            164 =>
                array(
                    'zone_code' => 'Pontevedra',
                    'zone_country_id' => 195,
                    'zone_id' => 168,
                    'zone_name' => 'Pontevedra',
                ),
            165 =>
                array(
                    'zone_code' => 'Salamanca',
                    'zone_country_id' => 195,
                    'zone_id' => 169,
                    'zone_name' => 'Salamanca',
                ),
            166 =>
                array(
                    'zone_code' => 'Santa Cruz de Tenerife',
                    'zone_country_id' => 195,
                    'zone_id' => 170,
                    'zone_name' => 'Santa Cruz de Tenerife',
                ),
            167 =>
                array(
                    'zone_code' => 'Segovia',
                    'zone_country_id' => 195,
                    'zone_id' => 171,
                    'zone_name' => 'Segovia',
                ),
            168 =>
                array(
                    'zone_code' => 'Sevilla',
                    'zone_country_id' => 195,
                    'zone_id' => 172,
                    'zone_name' => 'Sevilla',
                ),
            169 =>
                array(
                    'zone_code' => 'Soria',
                    'zone_country_id' => 195,
                    'zone_id' => 173,
                    'zone_name' => 'Soria',
                ),
            170 =>
                array(
                    'zone_code' => 'Tarragona',
                    'zone_country_id' => 195,
                    'zone_id' => 174,
                    'zone_name' => 'Tarragona',
                ),
            171 =>
                array(
                    'zone_code' => 'Teruel',
                    'zone_country_id' => 195,
                    'zone_id' => 175,
                    'zone_name' => 'Teruel',
                ),
            172 =>
                array(
                    'zone_code' => 'Toledo',
                    'zone_country_id' => 195,
                    'zone_id' => 176,
                    'zone_name' => 'Toledo',
                ),
            173 =>
                array(
                    'zone_code' => 'Valencia',
                    'zone_country_id' => 195,
                    'zone_id' => 177,
                    'zone_name' => 'Valencia',
                ),
            174 =>
                array(
                    'zone_code' => 'Valladolid',
                    'zone_country_id' => 195,
                    'zone_id' => 178,
                    'zone_name' => 'Valladolid',
                ),
            175 =>
                array(
                    'zone_code' => 'Vizcaya',
                    'zone_country_id' => 195,
                    'zone_id' => 179,
                    'zone_name' => 'Vizcaya',
                ),
            176 =>
                array(
                    'zone_code' => 'Zamora',
                    'zone_country_id' => 195,
                    'zone_id' => 180,
                    'zone_name' => 'Zamora',
                ),
            177 =>
                array(
                    'zone_code' => 'Zaragoza',
                    'zone_country_id' => 195,
                    'zone_id' => 181,
                    'zone_name' => 'Zaragoza',
                ),
            178 =>
                array(
                    'zone_code' => 'ACT',
                    'zone_country_id' => 13,
                    'zone_id' => 182,
                    'zone_name' => 'Australian Capital Territory',
                ),
            179 =>
                array(
                    'zone_code' => 'NSW',
                    'zone_country_id' => 13,
                    'zone_id' => 183,
                    'zone_name' => 'New South Wales',
                ),
            180 =>
                array(
                    'zone_code' => 'NT',
                    'zone_country_id' => 13,
                    'zone_id' => 184,
                    'zone_name' => 'Northern Territory',
                ),
            181 =>
                array(
                    'zone_code' => 'QLD',
                    'zone_country_id' => 13,
                    'zone_id' => 185,
                    'zone_name' => 'Queensland',
                ),
            182 =>
                array(
                    'zone_code' => 'SA',
                    'zone_country_id' => 13,
                    'zone_id' => 186,
                    'zone_name' => 'South Australia',
                ),
            183 =>
                array(
                    'zone_code' => 'TAS',
                    'zone_country_id' => 13,
                    'zone_id' => 187,
                    'zone_name' => 'Tasmania',
                ),
            184 =>
                array(
                    'zone_code' => 'VIC',
                    'zone_country_id' => 13,
                    'zone_id' => 188,
                    'zone_name' => 'Victoria',
                ),
            185 =>
                array(
                    'zone_code' => 'WA',
                    'zone_country_id' => 13,
                    'zone_id' => 189,
                    'zone_name' => 'Western Australia',
                ),
            186 =>
                array(
                    'zone_code' => 'AG',
                    'zone_country_id' => 105,
                    'zone_id' => 190,
                    'zone_name' => 'Agrigento',
                ),
            187 =>
                array(
                    'zone_code' => 'AL',
                    'zone_country_id' => 105,
                    'zone_id' => 191,
                    'zone_name' => 'Alessandria',
                ),
            188 =>
                array(
                    'zone_code' => 'AN',
                    'zone_country_id' => 105,
                    'zone_id' => 192,
                    'zone_name' => 'Ancona',
                ),
            189 =>
                array(
                    'zone_code' => 'AO',
                    'zone_country_id' => 105,
                    'zone_id' => 193,
                    'zone_name' => 'Aosta',
                ),
            190 =>
                array(
                    'zone_code' => 'AR',
                    'zone_country_id' => 105,
                    'zone_id' => 194,
                    'zone_name' => 'Arezzo',
                ),
            191 =>
                array(
                    'zone_code' => 'AP',
                    'zone_country_id' => 105,
                    'zone_id' => 195,
                    'zone_name' => 'Ascoli Piceno',
                ),
            192 =>
                array(
                    'zone_code' => 'AT',
                    'zone_country_id' => 105,
                    'zone_id' => 196,
                    'zone_name' => 'Asti',
                ),
            193 =>
                array(
                    'zone_code' => 'AV',
                    'zone_country_id' => 105,
                    'zone_id' => 197,
                    'zone_name' => 'Avellino',
                ),
            194 =>
                array(
                    'zone_code' => 'BA',
                    'zone_country_id' => 105,
                    'zone_id' => 198,
                    'zone_name' => 'Bari',
                ),
            195 =>
                array(
                    'zone_code' => 'BT',
                    'zone_country_id' => 105,
                    'zone_id' => 199,
                    'zone_name' => 'Barletta Andria Trani',
                ),
            196 =>
                array(
                    'zone_code' => 'BL',
                    'zone_country_id' => 105,
                    'zone_id' => 200,
                    'zone_name' => 'Belluno',
                ),
            197 =>
                array(
                    'zone_code' => 'BN',
                    'zone_country_id' => 105,
                    'zone_id' => 201,
                    'zone_name' => 'Benevento',
                ),
            198 =>
                array(
                    'zone_code' => 'BG',
                    'zone_country_id' => 105,
                    'zone_id' => 202,
                    'zone_name' => 'Bergamo',
                ),
            199 =>
                array(
                    'zone_code' => 'BI',
                    'zone_country_id' => 105,
                    'zone_id' => 203,
                    'zone_name' => 'Biella',
                ),
            200 =>
                array(
                    'zone_code' => 'BO',
                    'zone_country_id' => 105,
                    'zone_id' => 204,
                    'zone_name' => 'Bologna',
                ),
            201 =>
                array(
                    'zone_code' => 'BZ',
                    'zone_country_id' => 105,
                    'zone_id' => 205,
                    'zone_name' => 'Bolzano',
                ),
            202 =>
                array(
                    'zone_code' => 'BS',
                    'zone_country_id' => 105,
                    'zone_id' => 206,
                    'zone_name' => 'Brescia',
                ),
            203 =>
                array(
                    'zone_code' => 'BR',
                    'zone_country_id' => 105,
                    'zone_id' => 207,
                    'zone_name' => 'Brindisi',
                ),
            204 =>
                array(
                    'zone_code' => 'CA',
                    'zone_country_id' => 105,
                    'zone_id' => 208,
                    'zone_name' => 'Cagliari',
                ),
            205 =>
                array(
                    'zone_code' => 'CL',
                    'zone_country_id' => 105,
                    'zone_id' => 209,
                    'zone_name' => 'Caltanissetta',
                ),
            206 =>
                array(
                    'zone_code' => 'CB',
                    'zone_country_id' => 105,
                    'zone_id' => 210,
                    'zone_name' => 'Campobasso',
                ),
            207 =>
                array(
                    'zone_code' => 'CI',
                    'zone_country_id' => 105,
                    'zone_id' => 211,
                    'zone_name' => 'Carbonia-Iglesias',
                ),
            208 =>
                array(
                    'zone_code' => 'CE',
                    'zone_country_id' => 105,
                    'zone_id' => 212,
                    'zone_name' => 'Caserta',
                ),
            209 =>
                array(
                    'zone_code' => 'CT',
                    'zone_country_id' => 105,
                    'zone_id' => 213,
                    'zone_name' => 'Catania',
                ),
            210 =>
                array(
                    'zone_code' => 'CZ',
                    'zone_country_id' => 105,
                    'zone_id' => 214,
                    'zone_name' => 'Catanzaro',
                ),
            211 =>
                array(
                    'zone_code' => 'CH',
                    'zone_country_id' => 105,
                    'zone_id' => 215,
                    'zone_name' => 'Chieti',
                ),
            212 =>
                array(
                    'zone_code' => 'CO',
                    'zone_country_id' => 105,
                    'zone_id' => 216,
                    'zone_name' => 'Como',
                ),
            213 =>
                array(
                    'zone_code' => 'CS',
                    'zone_country_id' => 105,
                    'zone_id' => 217,
                    'zone_name' => 'Cosenza',
                ),
            214 =>
                array(
                    'zone_code' => 'CR',
                    'zone_country_id' => 105,
                    'zone_id' => 218,
                    'zone_name' => 'Cremona',
                ),
            215 =>
                array(
                    'zone_code' => 'KR',
                    'zone_country_id' => 105,
                    'zone_id' => 219,
                    'zone_name' => 'Crotone',
                ),
            216 =>
                array(
                    'zone_code' => 'CN',
                    'zone_country_id' => 105,
                    'zone_id' => 220,
                    'zone_name' => 'Cuneo',
                ),
            217 =>
                array(
                    'zone_code' => 'EN',
                    'zone_country_id' => 105,
                    'zone_id' => 221,
                    'zone_name' => 'Enna',
                ),
            218 =>
                array(
                    'zone_code' => 'FM',
                    'zone_country_id' => 105,
                    'zone_id' => 222,
                    'zone_name' => 'Fermo',
                ),
            219 =>
                array(
                    'zone_code' => 'FE',
                    'zone_country_id' => 105,
                    'zone_id' => 223,
                    'zone_name' => 'Ferrara',
                ),
            220 =>
                array(
                    'zone_code' => 'FI',
                    'zone_country_id' => 105,
                    'zone_id' => 224,
                    'zone_name' => 'Firenze',
                ),
            221 =>
                array(
                    'zone_code' => 'FG',
                    'zone_country_id' => 105,
                    'zone_id' => 225,
                    'zone_name' => 'Foggia',
                ),
            222 =>
                array(
                    'zone_code' => 'FC',
                    'zone_country_id' => 105,
                    'zone_id' => 226,
                    'zone_name' => 'Forlì Cesena',
                ),
            223 =>
                array(
                    'zone_code' => 'FR',
                    'zone_country_id' => 105,
                    'zone_id' => 227,
                    'zone_name' => 'Frosinone',
                ),
            224 =>
                array(
                    'zone_code' => 'GE',
                    'zone_country_id' => 105,
                    'zone_id' => 228,
                    'zone_name' => 'Genova',
                ),
            225 =>
                array(
                    'zone_code' => 'GO',
                    'zone_country_id' => 105,
                    'zone_id' => 229,
                    'zone_name' => 'Gorizia',
                ),
            226 =>
                array(
                    'zone_code' => 'GR',
                    'zone_country_id' => 105,
                    'zone_id' => 230,
                    'zone_name' => 'Grosseto',
                ),
            227 =>
                array(
                    'zone_code' => 'IM',
                    'zone_country_id' => 105,
                    'zone_id' => 231,
                    'zone_name' => 'Imperia',
                ),
            228 =>
                array(
                    'zone_code' => 'IS',
                    'zone_country_id' => 105,
                    'zone_id' => 232,
                    'zone_name' => 'Isernia',
                ),
            229 =>
                array(
                    'zone_code' => 'AQ',
                    'zone_country_id' => 105,
                    'zone_id' => 233,
                    'zone_name' => 'Aquila',
                ),
            230 =>
                array(
                    'zone_code' => 'SP',
                    'zone_country_id' => 105,
                    'zone_id' => 234,
                    'zone_name' => 'La Spezia',
                ),
            231 =>
                array(
                    'zone_code' => 'LT',
                    'zone_country_id' => 105,
                    'zone_id' => 235,
                    'zone_name' => 'Latina',
                ),
            232 =>
                array(
                    'zone_code' => 'LE',
                    'zone_country_id' => 105,
                    'zone_id' => 236,
                    'zone_name' => 'Lecce',
                ),
            233 =>
                array(
                    'zone_code' => 'LC',
                    'zone_country_id' => 105,
                    'zone_id' => 237,
                    'zone_name' => 'Lecco',
                ),
            234 =>
                array(
                    'zone_code' => 'LI',
                    'zone_country_id' => 105,
                    'zone_id' => 238,
                    'zone_name' => 'Livorno',
                ),
            235 =>
                array(
                    'zone_code' => 'LO',
                    'zone_country_id' => 105,
                    'zone_id' => 239,
                    'zone_name' => 'Lodi',
                ),
            236 =>
                array(
                    'zone_code' => 'LU',
                    'zone_country_id' => 105,
                    'zone_id' => 240,
                    'zone_name' => 'Lucca',
                ),
            237 =>
                array(
                    'zone_code' => 'MC',
                    'zone_country_id' => 105,
                    'zone_id' => 241,
                    'zone_name' => 'Macerata',
                ),
            238 =>
                array(
                    'zone_code' => 'MN',
                    'zone_country_id' => 105,
                    'zone_id' => 242,
                    'zone_name' => 'Mantova',
                ),
            239 =>
                array(
                    'zone_code' => 'MS',
                    'zone_country_id' => 105,
                    'zone_id' => 243,
                    'zone_name' => 'Massa Carrara',
                ),
            240 =>
                array(
                    'zone_code' => 'MT',
                    'zone_country_id' => 105,
                    'zone_id' => 244,
                    'zone_name' => 'Matera',
                ),
            241 =>
                array(
                    'zone_code' => 'VS',
                    'zone_country_id' => 105,
                    'zone_id' => 245,
                    'zone_name' => 'Medio Campidano',
                ),
            242 =>
                array(
                    'zone_code' => 'ME',
                    'zone_country_id' => 105,
                    'zone_id' => 246,
                    'zone_name' => 'Messina',
                ),
            243 =>
                array(
                    'zone_code' => 'MI',
                    'zone_country_id' => 105,
                    'zone_id' => 247,
                    'zone_name' => 'Milano',
                ),
            244 =>
                array(
                    'zone_code' => 'MO',
                    'zone_country_id' => 105,
                    'zone_id' => 248,
                    'zone_name' => 'Modena',
                ),
            245 =>
                array(
                    'zone_code' => 'MB',
                    'zone_country_id' => 105,
                    'zone_id' => 249,
                    'zone_name' => 'Monza e Brianza',
                ),
            246 =>
                array(
                    'zone_code' => 'NA',
                    'zone_country_id' => 105,
                    'zone_id' => 250,
                    'zone_name' => 'Napoli',
                ),
            247 =>
                array(
                    'zone_code' => 'NO',
                    'zone_country_id' => 105,
                    'zone_id' => 251,
                    'zone_name' => 'Novara',
                ),
            248 =>
                array(
                    'zone_code' => 'NU',
                    'zone_country_id' => 105,
                    'zone_id' => 252,
                    'zone_name' => 'Nuoro',
                ),
            249 =>
                array(
                    'zone_code' => 'OG',
                    'zone_country_id' => 105,
                    'zone_id' => 253,
                    'zone_name' => 'Ogliastra',
                ),
            250 =>
                array(
                    'zone_code' => 'OT',
                    'zone_country_id' => 105,
                    'zone_id' => 254,
                    'zone_name' => 'Olbia-Tempio',
                ),
            251 =>
                array(
                    'zone_code' => 'OR',
                    'zone_country_id' => 105,
                    'zone_id' => 255,
                    'zone_name' => 'Oristano',
                ),
            252 =>
                array(
                    'zone_code' => 'PD',
                    'zone_country_id' => 105,
                    'zone_id' => 256,
                    'zone_name' => 'Padova',
                ),
            253 =>
                array(
                    'zone_code' => 'PA',
                    'zone_country_id' => 105,
                    'zone_id' => 257,
                    'zone_name' => 'Palermo',
                ),
            254 =>
                array(
                    'zone_code' => 'PR',
                    'zone_country_id' => 105,
                    'zone_id' => 258,
                    'zone_name' => 'Parma',
                ),
            255 =>
                array(
                    'zone_code' => 'PG',
                    'zone_country_id' => 105,
                    'zone_id' => 259,
                    'zone_name' => 'Perugia',
                ),
            256 =>
                array(
                    'zone_code' => 'PV',
                    'zone_country_id' => 105,
                    'zone_id' => 260,
                    'zone_name' => 'Pavia',
                ),
            257 =>
                array(
                    'zone_code' => 'PU',
                    'zone_country_id' => 105,
                    'zone_id' => 261,
                    'zone_name' => 'Pesaro Urbino',
                ),
            258 =>
                array(
                    'zone_code' => 'PE',
                    'zone_country_id' => 105,
                    'zone_id' => 262,
                    'zone_name' => 'Pescara',
                ),
            259 =>
                array(
                    'zone_code' => 'PC',
                    'zone_country_id' => 105,
                    'zone_id' => 263,
                    'zone_name' => 'Piacenza',
                ),
            260 =>
                array(
                    'zone_code' => 'PI',
                    'zone_country_id' => 105,
                    'zone_id' => 264,
                    'zone_name' => 'Pisa',
                ),
            261 =>
                array(
                    'zone_code' => 'PT',
                    'zone_country_id' => 105,
                    'zone_id' => 265,
                    'zone_name' => 'Pistoia',
                ),
            262 =>
                array(
                    'zone_code' => 'PN',
                    'zone_country_id' => 105,
                    'zone_id' => 266,
                    'zone_name' => 'Pordenone',
                ),
            263 =>
                array(
                    'zone_code' => 'PZ',
                    'zone_country_id' => 105,
                    'zone_id' => 267,
                    'zone_name' => 'Potenza',
                ),
            264 =>
                array(
                    'zone_code' => 'PO',
                    'zone_country_id' => 105,
                    'zone_id' => 268,
                    'zone_name' => 'Prato',
                ),
            265 =>
                array(
                    'zone_code' => 'RG',
                    'zone_country_id' => 105,
                    'zone_id' => 269,
                    'zone_name' => 'Ragusa',
                ),
            266 =>
                array(
                    'zone_code' => 'RA',
                    'zone_country_id' => 105,
                    'zone_id' => 270,
                    'zone_name' => 'Ravenna',
                ),
            267 =>
                array(
                    'zone_code' => 'RC',
                    'zone_country_id' => 105,
                    'zone_id' => 271,
                    'zone_name' => 'Reggio Calabria',
                ),
            268 =>
                array(
                    'zone_code' => 'RE',
                    'zone_country_id' => 105,
                    'zone_id' => 272,
                    'zone_name' => 'Reggio Emilia',
                ),
            269 =>
                array(
                    'zone_code' => 'RI',
                    'zone_country_id' => 105,
                    'zone_id' => 273,
                    'zone_name' => 'Rieti',
                ),
            270 =>
                array(
                    'zone_code' => 'RN',
                    'zone_country_id' => 105,
                    'zone_id' => 274,
                    'zone_name' => 'Rimini',
                ),
            271 =>
                array(
                    'zone_code' => 'RM',
                    'zone_country_id' => 105,
                    'zone_id' => 275,
                    'zone_name' => 'Roma',
                ),
            272 =>
                array(
                    'zone_code' => 'RO',
                    'zone_country_id' => 105,
                    'zone_id' => 276,
                    'zone_name' => 'Rovigo',
                ),
            273 =>
                array(
                    'zone_code' => 'SA',
                    'zone_country_id' => 105,
                    'zone_id' => 277,
                    'zone_name' => 'Salerno',
                ),
            274 =>
                array(
                    'zone_code' => 'SS',
                    'zone_country_id' => 105,
                    'zone_id' => 278,
                    'zone_name' => 'Sassari',
                ),
            275 =>
                array(
                    'zone_code' => 'SV',
                    'zone_country_id' => 105,
                    'zone_id' => 279,
                    'zone_name' => 'Savona',
                ),
            276 =>
                array(
                    'zone_code' => 'SI',
                    'zone_country_id' => 105,
                    'zone_id' => 280,
                    'zone_name' => 'Siena',
                ),
            277 =>
                array(
                    'zone_code' => 'SR',
                    'zone_country_id' => 105,
                    'zone_id' => 281,
                    'zone_name' => 'Siracusa',
                ),
            278 =>
                array(
                    'zone_code' => 'SO',
                    'zone_country_id' => 105,
                    'zone_id' => 282,
                    'zone_name' => 'Sondrio',
                ),
            279 =>
                array(
                    'zone_code' => 'TA',
                    'zone_country_id' => 105,
                    'zone_id' => 283,
                    'zone_name' => 'Taranto',
                ),
            280 =>
                array(
                    'zone_code' => 'TE',
                    'zone_country_id' => 105,
                    'zone_id' => 284,
                    'zone_name' => 'Teramo',
                ),
            281 =>
                array(
                    'zone_code' => 'TR',
                    'zone_country_id' => 105,
                    'zone_id' => 285,
                    'zone_name' => 'Terni',
                ),
            282 =>
                array(
                    'zone_code' => 'TO',
                    'zone_country_id' => 105,
                    'zone_id' => 286,
                    'zone_name' => 'Torino',
                ),
            283 =>
                array(
                    'zone_code' => 'TP',
                    'zone_country_id' => 105,
                    'zone_id' => 287,
                    'zone_name' => 'Trapani',
                ),
            284 =>
                array(
                    'zone_code' => 'TN',
                    'zone_country_id' => 105,
                    'zone_id' => 288,
                    'zone_name' => 'Trento',
                ),
            285 =>
                array(
                    'zone_code' => 'TV',
                    'zone_country_id' => 105,
                    'zone_id' => 289,
                    'zone_name' => 'Treviso',
                ),
            286 =>
                array(
                    'zone_code' => 'TS',
                    'zone_country_id' => 105,
                    'zone_id' => 290,
                    'zone_name' => 'Trieste',
                ),
            287 =>
                array(
                    'zone_code' => 'UD',
                    'zone_country_id' => 105,
                    'zone_id' => 291,
                    'zone_name' => 'Udine',
                ),
            288 =>
                array(
                    'zone_code' => 'VA',
                    'zone_country_id' => 105,
                    'zone_id' => 292,
                    'zone_name' => 'Varese',
                ),
            289 =>
                array(
                    'zone_code' => 'VE',
                    'zone_country_id' => 105,
                    'zone_id' => 293,
                    'zone_name' => 'Venezia',
                ),
            290 =>
                array(
                    'zone_code' => 'VB',
                    'zone_country_id' => 105,
                    'zone_id' => 294,
                    'zone_name' => 'Verbania',
                ),
            291 =>
                array(
                    'zone_code' => 'VC',
                    'zone_country_id' => 105,
                    'zone_id' => 295,
                    'zone_name' => 'Vercelli',
                ),
            292 =>
                array(
                    'zone_code' => 'VR',
                    'zone_country_id' => 105,
                    'zone_id' => 296,
                    'zone_name' => 'Verona',
                ),
            293 =>
                array(
                    'zone_code' => 'VV',
                    'zone_country_id' => 105,
                    'zone_id' => 297,
                    'zone_name' => 'Vibo Valentia',
                ),
            294 =>
                array(
                    'zone_code' => 'VI',
                    'zone_country_id' => 105,
                    'zone_id' => 298,
                    'zone_name' => 'Vicenza',
                ),
            295 =>
                array(
                    'zone_code' => 'VT',
                    'zone_country_id' => 105,
                    'zone_id' => 299,
                    'zone_name' => 'Viterbo',
                ),
        ));


    }
}
