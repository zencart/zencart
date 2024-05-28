<?php

/**
 * Steps.php
 *
 * @since       2015-02-21
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2024 Nicola Asuni - Tecnick.com LTD
 * @license     http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 *
 * This file is part of tc-lib-barcode software library.
 */

namespace Com\Tecnick\Barcode\Type\Square\Datamatrix;

/**
 * Com\Tecnick\Barcode\Type\Square\Datamatrix\Steps
 *
 * Steps methods for Datamatrix Barcode type class
 *
 * @since       2015-02-21
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2024 Nicola Asuni - Tecnick.com LTD
 * @license     http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
abstract class Steps extends \Com\Tecnick\Barcode\Type\Square\Datamatrix\Modes
{
    /**
     * The look-ahead test scans the data to be encoded to find the best mode (Annex P - steps from J to S).
     *
     * @param string $data Data to encode
     * @param int    $pos  Current position
     * @param int    $mode Current encoding mode
     *
     * @return int encoding mode
     */
    public function lookAheadTest(string $data, int $pos, int $mode): int
    {
        $data_length = strlen($data);
        if ($pos >= $data_length) {
            return $mode;
        }

        $charscount = 0; // count processed chars
        // STEP J
        if ($mode == Data::ENC_ASCII) {
            $numch = [0, 1, 1, 1, 1, 1.25];
        } else {
            $numch = [1, 2, 2, 2, 2, 2.25];
            $numch[$mode] = 0;
        }

        while (true) {
            if (($pos + $charscount) == $data_length) {
                return $this->stepK($numch);
            }

            $chr = ord($data[$pos + $charscount]);
            ++$charscount;
            $this->stepL($chr, $numch);
            $this->stepM($chr, $numch);
            $this->stepN($chr, $numch);
            $this->stepO($chr, $numch);
            $this->stepP($chr, $numch);
            $this->stepQ($chr, $numch);
            if ($charscount >= 4) {
                $ret = $this->stepR($numch, $pos, $data_length, $charscount, $data);
                if ($ret >= 0) {
                    return $ret;
                }
            }
        }
    }

    /**
     * Step K
     *
     * @param array<int, int> $numch   Number of characters
     *
     * @return int encoding mode
     */
    protected function stepK(array $numch): int
    {
        if (
            $numch[Data::ENC_ASCII] <= ceil(min(
                $numch[Data::ENC_C40],
                $numch[Data::ENC_TXT],
                $numch[Data::ENC_X12],
                $numch[Data::ENC_EDF],
                $numch[Data::ENC_BASE256]
            ))
        ) {
            return Data::ENC_ASCII;
        }

        if (
            $numch[Data::ENC_BASE256] < ceil(min(
                $numch[Data::ENC_ASCII],
                $numch[Data::ENC_C40],
                $numch[Data::ENC_TXT],
                $numch[Data::ENC_X12],
                $numch[Data::ENC_EDF]
            ))
        ) {
            return Data::ENC_BASE256;
        }

        if (
            $numch[Data::ENC_EDF] < ceil(min(
                $numch[Data::ENC_ASCII],
                $numch[Data::ENC_C40],
                $numch[Data::ENC_TXT],
                $numch[Data::ENC_X12],
                $numch[Data::ENC_BASE256]
            ))
        ) {
            return Data::ENC_EDF;
        }

        if (
            $numch[Data::ENC_TXT] < ceil(min(
                $numch[Data::ENC_ASCII],
                $numch[Data::ENC_C40],
                $numch[Data::ENC_X12],
                $numch[Data::ENC_EDF],
                $numch[Data::ENC_BASE256]
            ))
        ) {
            return Data::ENC_TXT;
        }

        if (
            $numch[Data::ENC_X12] < ceil(min(
                $numch[Data::ENC_ASCII],
                $numch[Data::ENC_C40],
                $numch[Data::ENC_TXT],
                $numch[Data::ENC_EDF],
                $numch[Data::ENC_BASE256]
            ))
        ) {
            return Data::ENC_X12;
        }

        return Data::ENC_C40;
    }

    /**
     * Step L
     *
     * @param int $chr    Character code
     * @param array<int, int> $numch   Number of characters
     */
    protected function stepL(int $chr, array &$numch): void
    {
        if ($this->isCharMode($chr, Data::ENC_ASCII_NUM)) {
            $numch[Data::ENC_ASCII] += (1 / 2);
        } elseif ($this->isCharMode($chr, Data::ENC_ASCII_EXT)) {
            $numch[Data::ENC_ASCII] = ceil($numch[Data::ENC_ASCII]);
            $numch[Data::ENC_ASCII] += 2;
        } else {
            $numch[Data::ENC_ASCII] = ceil($numch[Data::ENC_ASCII]);
            ++$numch[Data::ENC_ASCII];
        }
    }

    /**
     * Step M
     *
     * @param int $chr    Character code
     * @param array<int, int> $numch   Number of characters
     */
    protected function stepM(int $chr, array &$numch): void
    {
        if ($this->isCharMode($chr, Data::ENC_C40)) {
            $numch[Data::ENC_C40] += (2 / 3);
        } elseif ($this->isCharMode($chr, Data::ENC_ASCII_EXT)) {
            $numch[Data::ENC_C40] += (8 / 3);
        } else {
            $numch[Data::ENC_C40] += (4 / 3);
        }
    }

    /**
     * Step N
     *
     * @param int $chr    Character code
     * @param array<int, int> $numch   Number of characters
     */
    protected function stepN(int $chr, array &$numch): void
    {
        if ($this->isCharMode($chr, Data::ENC_TXT)) {
            $numch[Data::ENC_TXT] += (2 / 3);
        } elseif ($this->isCharMode($chr, Data::ENC_ASCII_EXT)) {
            $numch[Data::ENC_TXT] += (8 / 3);
        } else {
            $numch[Data::ENC_TXT] += (4 / 3);
        }
    }

    /**
     * Step O
     *
     * @param int $chr    Character code
     * @param array<int, int> $numch   Number of characters
     */
    protected function stepO(int $chr, array &$numch): void
    {
        if ($this->isCharMode($chr, Data::ENC_X12) || $this->isCharMode($chr, Data::ENC_C40)) {
            $numch[Data::ENC_X12] += (2 / 3);
        } elseif ($this->isCharMode($chr, Data::ENC_ASCII_EXT)) {
            $numch[Data::ENC_X12] += (13 / 3);
        } else {
            $numch[Data::ENC_X12] += (10 / 3);
        }
    }

    /**
     * Step P
     *
     * @param int $chr    Character code
     * @param array<int, int> $numch   Number of characters
     */
    protected function stepP(int $chr, array &$numch): void
    {
        if ($this->isCharMode($chr, Data::ENC_EDF)) {
            $numch[Data::ENC_EDF] += (3 / 4);
        } elseif ($this->isCharMode($chr, Data::ENC_ASCII_EXT)) {
            $numch[Data::ENC_EDF] += (17 / 4);
        } else {
            $numch[Data::ENC_EDF] += (13 / 4);
        }
    }

    /**
     * Step Q
     *
     * @param int $chr    Character code
     * @param array<int, int> $numch   Number of characters
     */
    protected function stepQ(int $chr, array &$numch): void
    {
        if ($this->isCharMode($chr, Data::ENC_BASE256)) {
            $numch[Data::ENC_BASE256] += 4;
        } else {
            ++$numch[Data::ENC_BASE256];
        }
    }

    /**
     * Step R-f
     *
     * @param array<int, int> $numch   Number of characters
     * @param int    $pos  Current position
     * @param int    $data_length  Data length
     * @param int    $charscount   Number of processed characters
     * @param string $data Data to encode
     *
     * @return int   Encoding mode
     */
    protected function stepRf(
        array $numch,
        int $pos,
        int $data_length,
        int $charscount,
        string $data
    ): int {
        if (
            ($numch[Data::ENC_C40] + 1) < min(
                $numch[Data::ENC_ASCII],
                $numch[Data::ENC_TXT],
                $numch[Data::ENC_EDF],
                $numch[Data::ENC_BASE256]
            )
        ) {
            if ($numch[Data::ENC_C40] < $numch[Data::ENC_X12]) {
                return Data::ENC_C40;
            }

            if ($numch[Data::ENC_C40] == $numch[Data::ENC_X12]) {
                $ker = ($pos + $charscount + 1);
                while ($ker < $data_length) {
                    $tmpchr = ord($data[$ker]);
                    if ($this->isCharMode($tmpchr, Data::ENC_X12)) {
                        return Data::ENC_X12;
                    }

                    if ($this->isCharMode($tmpchr, Data::ENC_C40)) {
                        break;
                    }

                    ++$ker;
                }

                return Data::ENC_C40;
            }
        }

        return -1;
    }

    /**
     * Step R
     *
     * @param array<int, int> $numch   Number of characters
     * @param int    $pos  Current position
     * @param int    $data_length  Data length
     * @param int    $charscount   Number of processed characters
     * @param string $data Data to encode
     *
     * @return int   Encoding mode
     */
    protected function stepR(
        array $numch,
        int $pos,
        int $data_length,
        int $charscount,
        string $data
    ): int {
        if (
            ($numch[Data::ENC_ASCII] + 1) <= min(
                $numch[Data::ENC_C40],
                $numch[Data::ENC_TXT],
                $numch[Data::ENC_X12],
                $numch[Data::ENC_EDF],
                $numch[Data::ENC_BASE256]
            )
        ) {
            return Data::ENC_ASCII;
        }

        if (
            (($numch[Data::ENC_BASE256] + 1) <= $numch[Data::ENC_ASCII])
            || (($numch[Data::ENC_BASE256] + 1) < min(
                $numch[Data::ENC_C40],
                $numch[Data::ENC_TXT],
                $numch[Data::ENC_X12],
                $numch[Data::ENC_EDF]
            ))
        ) {
            return Data::ENC_BASE256;
        }

        if (
            ($numch[Data::ENC_EDF] + 1) < min(
                $numch[Data::ENC_ASCII],
                $numch[Data::ENC_C40],
                $numch[Data::ENC_TXT],
                $numch[Data::ENC_X12],
                $numch[Data::ENC_BASE256]
            )
        ) {
            return Data::ENC_EDF;
        }

        if (
            ($numch[Data::ENC_TXT] + 1) < min(
                $numch[Data::ENC_ASCII],
                $numch[Data::ENC_C40],
                $numch[Data::ENC_X12],
                $numch[Data::ENC_EDF],
                $numch[Data::ENC_BASE256]
            )
        ) {
            return Data::ENC_TXT;
        }

        if (
            ($numch[Data::ENC_X12] + 1) < min(
                $numch[Data::ENC_ASCII],
                $numch[Data::ENC_C40],
                $numch[Data::ENC_TXT],
                $numch[Data::ENC_EDF],
                $numch[Data::ENC_BASE256]
            )
        ) {
            return Data::ENC_X12;
        }

        return $this->stepRf($numch, $pos, $data_length, $charscount, $data);
    }
}
