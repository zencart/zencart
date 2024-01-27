<?php

namespace Database\Seeders\zencart;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductsToCategoriesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        DB::table('products_to_categories')->truncate();

        DB::table('products_to_categories')->insert(array(
            0 =>
                array(
                    'categories_id' => 4,
                    'products_id' => 1,
                ),
            1 =>
                array(
                    'categories_id' => 4,
                    'products_id' => 2,
                ),
            2 =>
                array(
                    'categories_id' => 9,
                    'products_id' => 3,
                ),
            3 =>
                array(
                    'categories_id' => 10,
                    'products_id' => 4,
                ),
            4 =>
                array(
                    'categories_id' => 11,
                    'products_id' => 5,
                ),
            5 =>
                array(
                    'categories_id' => 22,
                    'products_id' => 5,
                ),
            6 =>
                array(
                    'categories_id' => 10,
                    'products_id' => 6,
                ),
            7 =>
                array(
                    'categories_id' => 22,
                    'products_id' => 6,
                ),
            8 =>
                array(
                    'categories_id' => 12,
                    'products_id' => 7,
                ),
            9 =>
                array(
                    'categories_id' => 22,
                    'products_id' => 7,
                ),
            10 =>
                array(
                    'categories_id' => 13,
                    'products_id' => 8,
                ),
            11 =>
                array(
                    'categories_id' => 22,
                    'products_id' => 8,
                ),
            12 =>
                array(
                    'categories_id' => 10,
                    'products_id' => 9,
                ),
            13 =>
                array(
                    'categories_id' => 22,
                    'products_id' => 9,
                ),
            14 =>
                array(
                    'categories_id' => 10,
                    'products_id' => 10,
                ),
            15 =>
                array(
                    'categories_id' => 10,
                    'products_id' => 11,
                ),
            16 =>
                array(
                    'categories_id' => 22,
                    'products_id' => 11,
                ),
            17 =>
                array(
                    'categories_id' => 10,
                    'products_id' => 12,
                ),
            18 =>
                array(
                    'categories_id' => 22,
                    'products_id' => 12,
                ),
            19 =>
                array(
                    'categories_id' => 10,
                    'products_id' => 13,
                ),
            20 =>
                array(
                    'categories_id' => 22,
                    'products_id' => 13,
                ),
            21 =>
                array(
                    'categories_id' => 15,
                    'products_id' => 14,
                ),
            22 =>
                array(
                    'categories_id' => 22,
                    'products_id' => 14,
                ),
            23 =>
                array(
                    'categories_id' => 14,
                    'products_id' => 15,
                ),
            24 =>
                array(
                    'categories_id' => 22,
                    'products_id' => 15,
                ),
            25 =>
                array(
                    'categories_id' => 15,
                    'products_id' => 16,
                ),
            26 =>
                array(
                    'categories_id' => 22,
                    'products_id' => 16,
                ),
            27 =>
                array(
                    'categories_id' => 10,
                    'products_id' => 17,
                ),
            28 =>
                array(
                    'categories_id' => 22,
                    'products_id' => 17,
                ),
            29 =>
                array(
                    'categories_id' => 10,
                    'products_id' => 18,
                ),
            30 =>
                array(
                    'categories_id' => 12,
                    'products_id' => 19,
                ),
            31 =>
                array(
                    'categories_id' => 22,
                    'products_id' => 19,
                ),
            32 =>
                array(
                    'categories_id' => 15,
                    'products_id' => 20,
                ),
            33 =>
                array(
                    'categories_id' => 22,
                    'products_id' => 20,
                ),
            34 =>
                array(
                    'categories_id' => 18,
                    'products_id' => 21,
                ),
            35 =>
                array(
                    'categories_id' => 22,
                    'products_id' => 21,
                ),
            36 =>
                array(
                    'categories_id' => 19,
                    'products_id' => 22,
                ),
            37 =>
                array(
                    'categories_id' => 22,
                    'products_id' => 22,
                ),
            38 =>
                array(
                    'categories_id' => 20,
                    'products_id' => 23,
                ),
            39 =>
                array(
                    'categories_id' => 22,
                    'products_id' => 23,
                ),
            40 =>
                array(
                    'categories_id' => 20,
                    'products_id' => 24,
                ),
            41 =>
                array(
                    'categories_id' => 22,
                    'products_id' => 24,
                ),
            42 =>
                array(
                    'categories_id' => 8,
                    'products_id' => 25,
                ),
            43 =>
                array(
                    'categories_id' => 9,
                    'products_id' => 26,
                ),
            44 =>
                array(
                    'categories_id' => 5,
                    'products_id' => 27,
                ),
            45 =>
                array(
                    'categories_id' => 22,
                    'products_id' => 27,
                ),
            46 =>
                array(
                    'categories_id' => 21,
                    'products_id' => 28,
                ),
            47 =>
                array(
                    'categories_id' => 21,
                    'products_id' => 29,
                ),
            48 =>
                array(
                    'categories_id' => 21,
                    'products_id' => 30,
                ),
            49 =>
                array(
                    'categories_id' => 21,
                    'products_id' => 31,
                ),
            50 =>
                array(
                    'categories_id' => 21,
                    'products_id' => 32,
                ),
            51 =>
                array(
                    'categories_id' => 22,
                    'products_id' => 34,
                ),
            52 =>
                array(
                    'categories_id' => 25,
                    'products_id' => 36,
                ),
            53 =>
                array(
                    'categories_id' => 24,
                    'products_id' => 39,
                ),
            54 =>
                array(
                    'categories_id' => 24,
                    'products_id' => 40,
                ),
            55 =>
                array(
                    'categories_id' => 28,
                    'products_id' => 41,
                ),
            56 =>
                array(
                    'categories_id' => 28,
                    'products_id' => 42,
                ),
            57 =>
                array(
                    'categories_id' => 24,
                    'products_id' => 43,
                ),
            58 =>
                array(
                    'categories_id' => 22,
                    'products_id' => 44,
                ),
            59 =>
                array(
                    'categories_id' => 22,
                    'products_id' => 46,
                ),
            60 =>
                array(
                    'categories_id' => 21,
                    'products_id' => 47,
                ),
            61 =>
                array(
                    'categories_id' => 23,
                    'products_id' => 48,
                ),
            62 =>
                array(
                    'categories_id' => 23,
                    'products_id' => 49,
                ),
            63 =>
                array(
                    'categories_id' => 23,
                    'products_id' => 50,
                ),
            64 =>
                array(
                    'categories_id' => 24,
                    'products_id' => 51,
                ),
            65 =>
                array(
                    'categories_id' => 24,
                    'products_id' => 52,
                ),
            66 =>
                array(
                    'categories_id' => 23,
                    'products_id' => 53,
                ),
            67 =>
                array(
                    'categories_id' => 23,
                    'products_id' => 54,
                ),
            68 =>
                array(
                    'categories_id' => 28,
                    'products_id' => 55,
                ),
            69 =>
                array(
                    'categories_id' => 28,
                    'products_id' => 56,
                ),
            70 =>
                array(
                    'categories_id' => 24,
                    'products_id' => 57,
                ),
            71 =>
                array(
                    'categories_id' => 23,
                    'products_id' => 59,
                ),
            72 =>
                array(
                    'categories_id' => 28,
                    'products_id' => 60,
                ),
            73 =>
                array(
                    'categories_id' => 28,
                    'products_id' => 61,
                ),
            74 =>
                array(
                    'categories_id' => 23,
                    'products_id' => 74,
                ),
            75 =>
                array(
                    'categories_id' => 28,
                    'products_id' => 76,
                ),
            76 =>
                array(
                    'categories_id' => 25,
                    'products_id' => 78,
                ),
            77 =>
                array(
                    'categories_id' => 23,
                    'products_id' => 79,
                ),
            78 =>
                array(
                    'categories_id' => 23,
                    'products_id' => 80,
                ),
            79 =>
                array(
                    'categories_id' => 27,
                    'products_id' => 82,
                ),
            80 =>
                array(
                    'categories_id' => 27,
                    'products_id' => 83,
                ),
            81 =>
                array(
                    'categories_id' => 23,
                    'products_id' => 84,
                ),
            82 =>
                array(
                    'categories_id' => 23,
                    'products_id' => 85,
                ),
            83 =>
                array(
                    'categories_id' => 31,
                    'products_id' => 88,
                ),
            84 =>
                array(
                    'categories_id' => 31,
                    'products_id' => 89,
                ),
            85 =>
                array(
                    'categories_id' => 45,
                    'products_id' => 90,
                ),
            86 =>
                array(
                    'categories_id' => 45,
                    'products_id' => 92,
                ),
            87 =>
                array(
                    'categories_id' => 46,
                    'products_id' => 93,
                ),
            88 =>
                array(
                    'categories_id' => 46,
                    'products_id' => 94,
                ),
            89 =>
                array(
                    'categories_id' => 51,
                    'products_id' => 95,
                ),
            90 =>
                array(
                    'categories_id' => 51,
                    'products_id' => 96,
                ),
            91 =>
                array(
                    'categories_id' => 32,
                    'products_id' => 97,
                ),
            92 =>
                array(
                    'categories_id' => 32,
                    'products_id' => 98,
                ),
            93 =>
                array(
                    'categories_id' => 23,
                    'products_id' => 99,
                ),
            94 =>
                array(
                    'categories_id' => 25,
                    'products_id' => 100,
                ),
            95 =>
                array(
                    'categories_id' => 47,
                    'products_id' => 101,
                ),
            96 =>
                array(
                    'categories_id' => 23,
                    'products_id' => 104,
                ),
            97 =>
                array(
                    'categories_id' => 22,
                    'products_id' => 105,
                ),
            98 =>
                array(
                    'categories_id' => 22,
                    'products_id' => 106,
                ),
            99 =>
                array(
                    'categories_id' => 23,
                    'products_id' => 107,
                ),
            100 =>
                array(
                    'categories_id' => 23,
                    'products_id' => 108,
                ),
            101 =>
                array(
                    'categories_id' => 23,
                    'products_id' => 109,
                ),
            102 =>
                array(
                    'categories_id' => 52,
                    'products_id' => 110,
                ),
            103 =>
                array(
                    'categories_id' => 52,
                    'products_id' => 111,
                ),
            104 =>
                array(
                    'categories_id' => 53,
                    'products_id' => 112,
                ),
            105 =>
                array(
                    'categories_id' => 53,
                    'products_id' => 113,
                ),
            106 =>
                array(
                    'categories_id' => 53,
                    'products_id' => 114,
                ),
            107 =>
                array(
                    'categories_id' => 53,
                    'products_id' => 115,
                ),
            108 =>
                array(
                    'categories_id' => 53,
                    'products_id' => 116,
                ),
            109 =>
                array(
                    'categories_id' => 53,
                    'products_id' => 117,
                ),
            110 =>
                array(
                    'categories_id' => 53,
                    'products_id' => 118,
                ),
            111 =>
                array(
                    'categories_id' => 53,
                    'products_id' => 119,
                ),
            112 =>
                array(
                    'categories_id' => 53,
                    'products_id' => 120,
                ),
            113 =>
                array(
                    'categories_id' => 53,
                    'products_id' => 121,
                ),
            114 =>
                array(
                    'categories_id' => 53,
                    'products_id' => 122,
                ),
            115 =>
                array(
                    'categories_id' => 53,
                    'products_id' => 123,
                ),
            116 =>
                array(
                    'categories_id' => 55,
                    'products_id' => 127,
                ),
            117 =>
                array(
                    'categories_id' => 55,
                    'products_id' => 130,
                ),
            118 =>
                array(
                    'categories_id' => 57,
                    'products_id' => 131,
                ),
            119 =>
                array(
                    'categories_id' => 58,
                    'products_id' => 132,
                ),
            120 =>
                array(
                    'categories_id' => 60,
                    'products_id' => 133,
                ),
            121 =>
                array(
                    'categories_id' => 57,
                    'products_id' => 134,
                ),
            122 =>
                array(
                    'categories_id' => 58,
                    'products_id' => 154,
                ),
            123 =>
                array(
                    'categories_id' => 56,
                    'products_id' => 155,
                ),
            124 =>
                array(
                    'categories_id' => 56,
                    'products_id' => 156,
                ),
            125 =>
                array(
                    'categories_id' => 56,
                    'products_id' => 157,
                ),
            126 =>
                array(
                    'categories_id' => 56,
                    'products_id' => 158,
                ),
            127 =>
                array(
                    'categories_id' => 56,
                    'products_id' => 159,
                ),
            128 =>
                array(
                    'categories_id' => 61,
                    'products_id' => 160,
                ),
            129 =>
                array(
                    'categories_id' => 61,
                    'products_id' => 165,
                ),
            130 =>
                array(
                    'categories_id' => 62,
                    'products_id' => 166,
                ),
            131 =>
                array(
                    'categories_id' => 63,
                    'products_id' => 167,
                ),
            132 =>
                array(
                    'categories_id' => 64,
                    'products_id' => 168,
                ),
            133 =>
                array(
                    'categories_id' => 64,
                    'products_id' => 169,
                ),
            134 =>
                array(
                    'categories_id' => 64,
                    'products_id' => 170,
                ),
            135 =>
                array(
                    'categories_id' => 63,
                    'products_id' => 171,
                ),
            136 =>
                array(
                    'categories_id' => 64,
                    'products_id' => 171,
                ),
            137 =>
                array(
                    'categories_id' => 64,
                    'products_id' => 172,
                ),
            138 =>
                array(
                    'categories_id' => 61,
                    'products_id' => 173,
                ),
            139 =>
                array(
                    'categories_id' => 24,
                    'products_id' => 174,
                ),
            140 =>
                array(
                    'categories_id' => 55,
                    'products_id' => 175,
                ),
            141 =>
                array(
                    'categories_id' => 55,
                    'products_id' => 176,
                ),
            142 =>
                array(
                    'categories_id' => 55,
                    'products_id' => 177,
                ),
            143 =>
                array(
                    'categories_id' => 55,
                    'products_id' => 178,
                ),
            144 =>
                array(
                    'categories_id' => 60,
                    'products_id' => 179,
                ),
            145 =>
                array(
                    'categories_id' => 63,
                    'products_id' => 180,
                ),
        ));


    }
}
