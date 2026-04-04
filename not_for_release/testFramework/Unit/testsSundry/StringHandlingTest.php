<?php
/**
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */

namespace Tests\Unit\testsSundry;

use Tests\Support\zcUnitTestCase;

class StringHandlingTest extends zcUnitTestCase
{
    public function setup(): void
    {
        parent::setup();

        require_once DIR_FS_CATALOG . 'includes/functions/functions_strings.php';
    }

    /**
     * @dataProvider zenTruncStringProvider
     */
    public function testZenTruncString(?string $str, int|string $len, string $more, string $expected): void
    {
        $this->assertEquals($expected, \zen_trunc_string($str, $len, $more));
    }

    public function zenTruncStringProvider(): array
    {
        return [
            [null, 10, '...', ''],
            ['', 10, '...', ''],
            ['Short', 10, '...', 'Short'],
            ['This is a test string', 0, '...', ''],
            ['This is a test string', -5, '...', ''],

            ['This is a test string', 10, ' (more)', 'This is a (more)'],
            ['This is a test string', 10, '...', 'This is a...'],
            ['This is a test string', 10, 'true', 'This is a...'],
            ['This is a test string', 10, 'false', 'This is a'],
            ['This is a test string', 10, '', 'This is a'],
            ['This is a test string', 18, 'true', 'This is a test...'],
            ['This is a test string', 18, '...', 'This is a test...'],
            ['This is a test string', 18, 'false', 'This is a test'],
            ['This is a test string', 18, '', 'This is a test'],
            ['This is a test string', 20, '...', 'This is a test...'],
            ['This is a test string', 21, '...', 'This is a test string'], // exact length
            ['This is a test string', 22, '...', 'This is a test string'], // over length

            ['ExactLength', 11, '...', 'ExactLength'],
            ['NoSpacesHere', 10, 'true', 'NoSpacesHe...'],
            ['NoSpacesHere', 10, '...', 'NoSpacesHe...'],
            ['NoSpacesHere', 10, 'false', 'NoSpacesHe'],
            ['NoSpacesHere', 10, '', 'NoSpacesHe'],

            // diacritics, and at end of string
            ['Thîs îs a tést strîng ç', 20, '...', 'Thîs îs a tést...'],
            ['Thîs îs a tést strîng ç', 22, '...', 'Thîs îs a tést strîng...'], // exact length
            ['Thîs îs a tést strîng ç', 23, '...', 'Thîs îs a tést strîng ç'], // exact length
            ['Thîs îs a tést strîng ç', 23, '...', 'Thîs îs a tést strîng ç'], // over length

            ['when was the runner on site', 19, 'true', 'when was the...'],
            ['Ceci est une chaîne de test', 19, 'true', 'Ceci est une...'],
            ['Ceci est une chaîne de test', 19, '...', 'Ceci est une...'],
            ['Ceci est une chaîne de test', 19, 'false', 'Ceci est une'],
            ['Ceci est une chaîne de test', 19, '', 'Ceci est une'],
            ['Ceci est une chaîne de test', 20, '...', 'Ceci est une chaîne...'],
            ['Ceci est une chaîne de test', 21, '...', 'Ceci est une chaîne...'],
            ['Ceci est une chaîne de test', 22, '...', 'Ceci est une chaîne...'],
            ['Ceci est une chaîne de test', 27, '...', 'Ceci est une chaîne de test'],

            ['申し訳ありませんが、そのご要望には対応できません。', 18, 'true', '申し訳ありませんが、そのご要望には対...'],
            ['申し訳ありませんが、そのご要望には対応できません。', 18, '...', '申し訳ありませんが、そのご要望には対...'],
            ['申し訳ありませんが、そのご要望には対応できません。', 18, 'false', '申し訳ありませんが、そのご要望には対'],
            ['申し訳ありませんが、そのご要望には対応できません。', 18, '', '申し訳ありませんが、そのご要望には対'],
            ['申し訳ありませんが、そのご要望には対応できません。', 20, '...', '申し訳ありませんが、そのご要望には対応で...'],
            ['申し訳ありませんが、そのご要望には対応できません。', 21, '...', '申し訳ありませんが、そのご要望には対応でき...'],
            ['申し訳ありませんが、そのご要望には対応できません。', 22, '。。。', '申し訳ありませんが、そのご要望には対応できま。。。'],
        ];
    }

}
