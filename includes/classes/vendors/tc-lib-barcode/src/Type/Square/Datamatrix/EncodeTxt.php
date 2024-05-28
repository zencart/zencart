<?php

/**
 * EncodeTxt.php
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

namespace Com\Tecnick\Barcode\Type\Square\Datamatrix;

use Com\Tecnick\Barcode\Exception as BarcodeException;

/**
 * Com\Tecnick\Barcode\Type\Square\Datamatrix\Encodetxt
 *
 * Datamatrix Barcode type class
 * DATAMATRIX (ISO/IEC 16022)
 *
 * @since       2015-02-21
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2010-2024 Nicola Asuni - Tecnick.com LTD
 * @license     http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
class EncodeTxt extends \Com\Tecnick\Barcode\Type\Square\Datamatrix\Steps
{
    /**
     * Encode TXTC40 shift
     *
     * @param int   $chr       Character code
     * @param int   $enc       Current encoding
     * @param array<int, int> $temp_cw   Temporary codewords array
     * @param int   $ptr       Pointer
     */
    public function encodeTXTC40shift(
        int &$chr,
        int &$enc,
        array &$temp_cw,
        int &$ptr
    ): void {
        if (isset(Data::CHSET['SH1'][$chr])) {
            $temp_cw[] = 0; // shift 1
            $shiftset = Data::CHSET['SH1'];
        } elseif (isset(Data::CHSET['SH2'][$chr])) {
            $temp_cw[] = 1; // shift 2
            $shiftset = Data::CHSET['SH2'];
        } elseif (($enc == Data::ENC_C40) && isset(Data::CHSET['S3C'][$chr])) {
            $temp_cw[] = 2; // shift 3
            $shiftset = Data::CHSET['S3C'];
        } elseif (($enc == Data::ENC_TXT) && isset(Data::CHSET['S3T'][$chr])) {
            $temp_cw[] = 2; // shift 3
            $shiftset = Data::CHSET['S3T'];
        } else {
            throw new BarcodeException('Error');
        }

        $temp_cw[] = $shiftset[$chr];
        $ptr += 2;
    }

    /**
     * Encode TXTC40
     *
     * @param string $data      Data string
     * @param int    $enc       Current encoding
     * @param array<int, int> $temp_cw   Temporary codewords array
     * @param int    $ptr       Pointer
     * @param int    $epos      End position
     * @param array<int, int> $charset   Charset array
     *
     * @return int   Curent character code
     */
    public function encodeTXTC40(
        string &$data,
        int &$enc,
        array &$temp_cw,
        int &$ptr,
        int &$epos,
        array &$charset
    ): int {
        // 2. process the next character in C40 encodation.
        $chr = ord($data[$epos]);
        ++$epos;
        // check for extended character
        if (($chr & 0x80) !== 0) {
            if ($enc == Data::ENC_X12) {
                throw new BarcodeException('TXTC40 Error');
            }

            $chr &= 0x7f;
            $temp_cw[] = 1; // shift 2
            $temp_cw[] = 30; // upper shift
            $ptr += 2;
        }

        if (isset($charset[$chr])) {
            $temp_cw[] = $charset[$chr];
            ++$ptr;
        } else {
            $this->encodeTXTC40shift($chr, $enc, $temp_cw, $ptr);
        }

        return $chr;
    }

    /**
     * Encode TXTC40 last
     * The following rules apply when only one or two symbol characters remain in the symbol
     * before the start of the error correction codewords.
     *
     * @param int   $chr       Character code
     * @param array<int, int> $cdw       Codewords array
     * @param int   $cdw_num   Codewords number
     * @param int   $enc       Current encoding
     * @param array<int, int> $temp_cw   Temporary codewords array
     * @param int   $ptr       Pointer
     * @param int   $epos      End position
     */
    public function encodeTXTC40last(
        int $chr,
        array &$cdw,
        int &$cdw_num,
        int &$enc,
        array &$temp_cw,
        int &$ptr,
        int &$epos
    ): void {
        // get remaining number of data symbols
        $cdwr = ($this->getMaxDataCodewords($cdw_num + $ptr) - $cdw_num);
        if (($cdwr == 1) && ($ptr == 1)) {
            // d. If one symbol character remains and one
            // C40 value (data character) remains to be encoded
            $cdw[] = ($chr + 1);
            ++$cdw_num;
            $enc = Data::ENC_ASCII;
            $this->last_enc = $enc;
        } elseif (($cdwr == 2) && ($ptr == 1)) {
            // c. If two symbol characters remain and only one
            // C40 value (data character) remains to be encoded
            $cdw[] = 254;
            $cdw[] = ($chr + 1);
            $cdw_num += 2;
            $enc = Data::ENC_ASCII;
            $this->last_enc = $enc;
        } elseif (($cdwr == 2) && ($ptr == 2)) {
            // b. If two symbol characters remain and two C40 values remain to be encoded
            $ch1 = array_shift($temp_cw);
            $ch2 = array_shift($temp_cw);
            $ptr -= 2;
            $tmp = ((1600 * $ch1) + (40 * $ch2) + 1);
            $cdw[] = ($tmp >> 8);
            $cdw[] = ($tmp % 256);
            $cdw_num += 2;
            $enc = Data::ENC_ASCII;
            $this->last_enc = $enc;
        } elseif ($enc != Data::ENC_ASCII) {
            // switch to ASCII encoding
            $enc = Data::ENC_ASCII;
            $this->last_enc = $enc;
            $cdw[] = $this->getSwitchEncodingCodeword($enc);
            ++$cdw_num;
            $epos -= $ptr;
        }
    }

    /**
     * Encode TXT
     *
     * @param array<int, int> $cdw         Codewords array
     * @param int    $cdw_num     Codewords number
     * @param int    $pos         Current position
     * @param int    $data_length Data length
     * @param string $data        Data string
     * @param int    $enc         Current encoding
     */
    public function encodeTXT(
        array &$cdw,
        int &$cdw_num,
        int &$pos,
        int &$data_length,
        string &$data,
        int &$enc
    ): void {
        $temp_cw = [];
        $ptr = 0;
        $epos = $pos;
        // get charset ID
        $set_id = Data::CHSET_ID[$enc];
        // get basic charset for current encoding
        $charset = Data::CHSET[$set_id];
        do {
            $chr = $this->encodeTXTC40($data, $enc, $temp_cw, $ptr, $epos, $charset);
            if ($ptr >= 3) {
                $ch1 = array_shift($temp_cw);
                $ch2 = array_shift($temp_cw);
                $ch3 = array_shift($temp_cw);
                $ptr -= 3;
                $tmp = ((1600 * $ch1) + (40 * $ch2) + $ch3 + 1);
                $cdw[] = ($tmp >> 8);
                $cdw[] = ($tmp % 256);
                $cdw_num += 2;
                $pos = $epos;
                // 1. If the C40 encoding is at the point of starting a new double symbol character and
                // if the look-ahead test (starting at step J) indicates another mode, switch to that mode.
                $newenc = $this->lookAheadTest($data, $pos, $enc);
                if ($newenc != $enc) {
                    // switch to new encoding
                    $enc = $newenc;
                    if ($enc != Data::ENC_ASCII) {
                        // set unlatch character
                        $cdw[] = $this->getSwitchEncodingCodeword(Data::ENC_ASCII);
                        ++$cdw_num;
                    }

                    $cdw[] = $this->getSwitchEncodingCodeword($enc);
                    ++$cdw_num;
                    $pos -= $ptr;
                    $ptr = 0;
                    break;
                }
            }
        } while (($ptr > 0) && ($epos < $data_length));

        // process last data (if any)
        if ($ptr > 0) {
            $this->encodeTXTC40last($chr, $cdw, $cdw_num, $enc, $temp_cw, $ptr, $epos);
            $pos = $epos;
        }
    }
}
