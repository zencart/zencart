<?php

/**
 * Process.php
 *
 * @since       2015-02-21
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2010-2024 Nicola Asuni - Tecnick.com LTD
 * @license     http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 *
 * This file is part of tc-lib-barcode software library.
 */

namespace Com\Tecnick\Barcode\Type\Linear\CodeOneTwoEight;

use Com\Tecnick\Barcode\Exception as BarcodeException;

/**
 * Com\Tecnick\Barcode\Type\Linear\CodeOneTwoEight\Process;
 *
 * Process methods for CodeOneTwoEight Barcode type class
 * CODE 128
 *
 * @since       2015-02-21
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2010-2024 Nicola Asuni - Tecnick.com LTD
 * @license     http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
abstract class Process extends \Com\Tecnick\Barcode\Type\Linear
{
    /**
     * Barcode format
     *
     * @var string
     */
    protected const FORMAT = 'C128';

    /**
     * Map characters to barcodes
     *
     * @var array<string>
     */
    protected const CHBAR = [
        '212222', // 00
        '222122', // 01
        '222221', // 02
        '121223', // 03
        '121322', // 04
        '131222', // 05
        '122213', // 06
        '122312', // 07
        '132212', // 08
        '221213', // 09
        '221312', // 10
        '231212', // 11
        '112232', // 12
        '122132', // 13
        '122231', // 14
        '113222', // 15
        '123122', // 16
        '123221', // 17
        '223211', // 18
        '221132', // 19
        '221231', // 20
        '213212', // 21
        '223112', // 22
        '312131', // 23
        '311222', // 24
        '321122', // 25
        '321221', // 26
        '312212', // 27
        '322112', // 28
        '322211', // 29
        '212123', // 30
        '212321', // 31
        '232121', // 32
        '111323', // 33
        '131123', // 34
        '131321', // 35
        '112313', // 36
        '132113', // 37
        '132311', // 38
        '211313', // 39
        '231113', // 40
        '231311', // 41
        '112133', // 42
        '112331', // 43
        '132131', // 44
        '113123', // 45
        '113321', // 46
        '133121', // 47
        '313121', // 48
        '211331', // 49
        '231131', // 50
        '213113', // 51
        '213311', // 52
        '213131', // 53
        '311123', // 54
        '311321', // 55
        '331121', // 56
        '312113', // 57
        '312311', // 58
        '332111', // 59
        '314111', // 60
        '221411', // 61
        '431111', // 62
        '111224', // 63
        '111422', // 64
        '121124', // 65
        '121421', // 66
        '141122', // 67
        '141221', // 68
        '112214', // 69
        '112412', // 70
        '122114', // 71
        '122411', // 72
        '142112', // 73
        '142211', // 74
        '241211', // 75
        '221114', // 76
        '413111', // 77
        '241112', // 78
        '134111', // 79
        '111242', // 80
        '121142', // 81
        '121241', // 82
        '114212', // 83
        '124112', // 84
        '124211', // 85
        '411212', // 86
        '421112', // 87
        '421211', // 88
        '212141', // 89
        '214121', // 90
        '412121', // 91
        '111143', // 92
        '111341', // 93
        '131141', // 94
        '114113', // 95
        '114311', // 96
        '411113', // 97
        '411311', // 98
        '113141', // 99
        '114131', // 100
        '311141', // 101
        '411131', // 102
        '211412', // 103 START A
        '211214', // 104 START B
        '211232', // 105 START C
        '233111', // STOP
        '200000', // END
    ];

    /**
     * Map ASCII characters for code A (ASCII 00 - 95)
     * // 128A (Code Set A) - ASCII characters 00 to 95 (0-9, A-Z and control codes), special characters
     *
     * @var string
     */
    protected const KEYS_A = ' !"#$%&\'()*+,-./'
        . '0123456789'
        . ':;<=>?@'
        . 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
        . '[\\]^_'
        . "\x00\x01\x02\x03\x04\x05\x06\x07\x08\x09\x0A\x0B\x0C\x0D\x0E\x0F"
        . "\x10\x11\x12\x13\x14\x15\x16\x17\x18\x19\x1A\x1B\x1C\x1D\x1E\x1F";

    /**
     * Map ASCII characters for code B (ASCII 32 - 127)
     * // 128B (Code Set B) - ASCII characters 32 to 127 (0-9, A-Z, a-z), special characters
     *
     * @var string
     */
    protected const KEYS_B = ' !"#$%&\'()*+,-./'
        . '0123456789'
        . ':;<=>?@'
        . 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
        . '[\\]^_`'
        . 'abcdefghijklmnopqrstuvwxyz'
        . '{|}~'
        . "\x7F";

    /**
     * Map special FNC codes for Code Set A (FNC 1-4)
     *
     * @var array<int, int>
     */
    protected const FNC_A = [
        241 => 102,
        242 => 97,
        243 => 96,
        244 => 101,
    ];

    /**
     * Map special FNC codes for Code Set B (FNC 1-4)
     *
     * @var array<int, int>
     */
    protected const FNC_B = [
        241 => 102,
        242 => 97,
        243 => 96,
        244 => 100,
    ];

    /**
     * Get the numeric sequence (if any)
     *
     * @param string $code Code to parse
     *
     * @return array<int, array{string, string, int}>
     *
     * @throws BarcodeException in case of error
     */
    protected function getNumericSequence(string $code): array
    {
        $sequence = [];
        $len = strlen($code);
        // get numeric sequences (if any)
        $numseq = [];
        preg_match_all('/(\d{4,})/', $code, $numseq, PREG_OFFSET_CAPTURE);
        if (! empty($numseq[1])) {
            $end_offset = 0;
            foreach ($numseq[1] as $val) {
                // offset to the start of numeric substr
                $offset = $val[1];

                // numeric sequence
                $slen = strlen($val[0]);
                if ($slen % 2 != 0) {
                    // the length must be even
                    --$slen;
                    // add 1 to start of offset so numbers are c type encoded "from the end"
                    ++$offset;
                }

                if ($offset > $end_offset) {
                    // non numeric sequence
                    $sequence = array_merge(
                        $sequence,
                        $this->get128ABsequence(substr($code, $end_offset, ($offset - $end_offset)))
                    );
                }

                $sequence[] = ['C', substr($code, $offset, $slen), $slen];
                $end_offset = $offset + $slen;
            }

            if ($end_offset < $len) {
                $sequence = array_merge($sequence, $this->get128ABsequence(substr($code, $end_offset)));
            }
        } else {
            // text code (non C mode)
            $sequence = array_merge($sequence, $this->get128ABsequence($code));
        }

        return $sequence;
    }

    /**
     * Split text code in A/B sequence for 128 code
     *
     * @param string $code Code to split
     *
     * @return array<int, array{string, string, int}>
     */
    protected function get128ABsequence(string $code): array
    {
        $len = strlen($code);
        $sequence = [];
        // get A sequences (if any)
        $aseq = [];
        preg_match_all('/([\x00-\x1f])/', $code, $aseq, PREG_OFFSET_CAPTURE);
        if (! empty($aseq[1])) {
            // get the entire A sequence (excluding FNC1-FNC4)
            preg_match_all('/([\x00-\x5f]+)/', $code, $aseq, PREG_OFFSET_CAPTURE);
            $end_offset = 0;
            foreach ($aseq[1] as $val) {
                $offset = $val[1];
                if ($offset > $end_offset) {
                    // B sequence
                    $sequence[] = ['B', substr($code, $end_offset, ($offset - $end_offset)), ($offset - $end_offset)];
                }

                // A sequence
                $slen = strlen($val[0]);
                $sequence[] = ['A', substr($code, $offset, $slen), $slen];
                $end_offset = $offset + $slen;
            }

            if ($end_offset < $len) {
                $sequence[] = ['B', substr($code, $end_offset), ($len - $end_offset)];
            }
        } else {
            // only B sequence
            $sequence[] = ['B', $code, $len];
        }

        return $sequence;
    }

    /**
     * Get the A code point array
     *
     * @param array<int, int>  $code_data  Array of codepoints to alter
     * @param string $code       Code to process
     * @param int    $len        Number of characters to process
     *
     * @throws BarcodeException in case of error
     */
    protected function getCodeDataA(
        array &$code_data,
        string $code,
        int $len
    ): void {
        for ($pos = 0; $pos < $len; ++$pos) {
            $char = $code[$pos];
            $char_id = ord($char);
            if (($char_id >= 241) && ($char_id <= 244)) {
                $code_data[] = $this::FNC_A[$char_id];
            } elseif ($char_id <= 95) {
                $code_data[] = strpos($this::KEYS_A, $char);
            } else {
                throw new BarcodeException('Invalid character sequence');
            }
        }
    }

    /**
     * Get the B code point array
     *
     * @param array<int, int>  $code_data  Array of codepoints to alter
     * @param string $code       Code to process
     * @param int    $len        Number of characters to process
     *
     * @throws BarcodeException in case of error
     */
    protected function getCodeDataB(
        array &$code_data,
        string $code,
        int $len
    ): void {
        for ($pos = 0; $pos < $len; ++$pos) {
            $char = $code[$pos];
            $char_id = ord($char);
            if (($char_id >= 241) && ($char_id <= 244)) {
                $code_data[] = $this::FNC_B[$char_id];
            } elseif (($char_id >= 32) && ($char_id <= 127)) {
                $code_data[] = strpos($this::KEYS_B, $char);
            } else {
                throw new BarcodeException('Invalid character sequence: ' . $char_id);
            }
        }
    }

    /**
     * Get the C code point array
     *
     * @param array<int, int>  $code_data  Array of codepoints to alter
     * @param string $code       Code to process
     *
     * @throws BarcodeException in case of error
     */
    protected function getCodeDataC(
        array &$code_data,
        string $code
    ): void {
        // code blocks separated by FNC1 (chr 241)
        $blocks = explode(chr(241), $code);

        foreach ($blocks as $block) {
            $len = strlen($block);

            if ($len % 2 != 0) {
                throw new BarcodeException('The length of each FNC1-separated code block must be even');
            }

            for ($pos = 0; $pos < $len; $pos += 2) {
                $chrnum = $block[$pos] . $block[($pos + 1)];
                if (preg_match('/(\d{2})/', $chrnum) > 0) {
                    $code_data[] = (int) $chrnum;
                } else {
                    throw new BarcodeException('Invalid character sequence');
                }
            }

            $code_data[] = 102;
        }

        // remove last 102 code
        array_pop($code_data);
    }

    /**
     * Finalize code data
     *
     * @param array<int, int>  $code_data  Array of codepoints to alter
     * @param int    $startid    Start ID code
     *
     * @return array<int, int> Array of codepoints
     */
    protected function finalizeCodeData(
        array $code_data,
        int $startid
    ): array {
        // calculate check character
        $sum = $startid;
        foreach ($code_data as $key => $val) {
            $sum += ($val * ($key + 1));
        }

        // add check character
        $code_data[] = ($sum % 103);

        // add stop sequence
        $code_data[] = 106;
        $code_data[] = 107;
        // add start code at the beginning
        array_unshift($code_data, $startid);

        return $code_data;
    }
}
