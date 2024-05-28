<?php

/**
 * Modes.php
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

/**
 * Com\Tecnick\Barcode\Type\Square\Datamatrix\Modes
 *
 * Modes for Datamatrix Barcode type class
 *
 * @since       2015-02-21
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2010-2024 Nicola Asuni - Tecnick.com LTD
 * @license     http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
abstract class Modes extends \Com\Tecnick\Barcode\Type\Square\Datamatrix\Placement
{
    /**
     * Store last used encoding for data codewords.
     */
    public int $last_enc;

    /**
     * Datamatrix shape key (S=square, R=rectangular)
     */
    public string $shape;

    /**
     * Return the 253-state codeword
     *
     * @param int $cdwpad Pad codeword.
     * @param int $cdwpos Number of data codewords from the beginning of encoded data.
     */
    public function get253StateCodeword(int $cdwpad, int $cdwpos): int
    {
        $pad = ($cdwpad + (((149 * $cdwpos) % 253) + 1));
        if ($pad > 254) {
            $pad -= 254;
        }

        return $pad;
    }

    /**
     * Return the 255-state codeword
     *
     * @param int $cdwpad Pad codeword.
     * @param int $cdwpos Number of data codewords from the beginning of encoded data.
     *
     * @return int pad codeword
     */
    protected function get255StateCodeword(int $cdwpad, int $cdwpos): int
    {
        $pad = ($cdwpad + (((149 * $cdwpos) % 255) + 1));
        if ($pad > 255) {
            $pad -= 256;
        }

        return $pad;
    }

    /**
     * Returns true if the char belongs to the selected mode
     *
     * @param int $chr  Character (byte) to check.
     * @param int $mode Current encoding mode.
     *
     * @return bool true if the char is of the selected mode.
     */
    protected function isCharMode(int $chr, int $mode): bool
    {
        $ret = match ($mode) {
            //Data::ENC_ASCII     => 'isASCIIMode',
            Data::ENC_C40 => $this->isC40Mode($chr),
            Data::ENC_TXT => $this->isTXTMode($chr),
            Data::ENC_X12 => $this->isX12Mode($chr),
            Data::ENC_EDF => $this->isEDFMode($chr),
            Data::ENC_BASE256 => $this->isBASE256Mode($chr),
            Data::ENC_ASCII_EXT => $this->isASCIIEXTMode($chr),
            Data::ENC_ASCII_NUM => $this->isASCIINUMMode($chr),
            default => false,
        };

        return $ret;
    }

    ///**
    // * Tell if char is ASCII character 0 to 127
    // *
    // * @param int $chr  Character (byte) to check.
    // *
    // * @return bool
    // */
    //protected function isASCIIMode(int $chr): bool
    //{
    //    return (($chr >= 0) && ($chr <= 127));
    //}
    /**
     * Tell if char is Upper-case alphanumeric
     *
     * @param int $chr  Character (byte) to check.
     */
    protected function isC40Mode(int $chr): bool
    {
        return (($chr == 32) || (($chr >= 48) && ($chr <= 57)) || (($chr >= 65) && ($chr <= 90)));
    }

    /**
     * Tell if char is Lower-case alphanumeric
     *
     * @param int $chr  Character (byte) to check.
     */
    protected function isTXTMode(int $chr): bool
    {
        return (($chr == 32) || (($chr >= 48) && ($chr <= 57)) || (($chr >= 97) && ($chr <= 122)));
    }

    /**
     * Tell if char is ANSI X12
     *
     * @param int $chr  Character (byte) to check.
     */
    protected function isX12Mode(int $chr): bool
    {
        return (($chr == 13) || ($chr == 42) || ($chr == 62));
    }

    /**
     * Tell if char is ASCII character 32 to 94
     *
     * @param int $chr  Character (byte) to check.
     */
    protected function isEDFMode(int $chr): bool
    {
        return (($chr >= 32) && ($chr <= 94));
    }

    /**
     * Tell if char is Function character (FNC1, Structured Append, Reader Program, or Code Page)
     *
     * @param int $chr  Character (byte) to check.
     */
    protected function isBASE256Mode(int $chr): bool
    {
        return (($chr == 232) || ($chr == 233) || ($chr == 234) || ($chr == 241));
    }

    /**
     * Tell if char is ASCII character 128 to 255
     *
     * @param int $chr  Character (byte) to check.
     */
    protected function isASCIIEXTMode(int $chr): bool
    {
        return (($chr >= 128) && ($chr <= 255));
    }

    /**
     * Tell if char is ASCII digits
     *
     * @param int $chr  Character (byte) to check.
     */
    protected function isASCIINUMMode(int $chr): bool
    {
        return (($chr >= 48) && ($chr <= 57));
    }

    /**
     * Choose the minimum matrix size and return the max number of data codewords.
     *
     * @param int $numcw Number of current codewords.
     *
     * @return int number of data codewords in matrix
     */
    protected function getMaxDataCodewords(int $numcw): int
    {
        $mdc = 0;
        foreach (Data::SYMBATTR[$this->shape] as $matrix) {
            if ($matrix[11] >= $numcw) {
                $mdc = $matrix[11];
                break;
            }
        }

        return $mdc;
    }

    /**
     * Get the switching codeword to a new encoding mode (latch codeword)
     * @param int $mode New encoding mode.
     * @return int Switch codeword.
     * @protected
     */
    protected function getSwitchEncodingCodeword(int $mode): int
    {
        $cdw = Data::SWITCHCDW[$mode];
        if ($cdw != 254) {
            return $cdw;
        }

        if ($this->last_enc != Data::ENC_EDF) {
            return $cdw;
        }

        return 124;
    }
}
