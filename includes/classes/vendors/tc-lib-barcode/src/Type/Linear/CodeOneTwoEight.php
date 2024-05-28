<?php

/**
 * CodeOneTwoEight.php
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

namespace Com\Tecnick\Barcode\Type\Linear;

use Com\Tecnick\Barcode\Exception as BarcodeException;

/**
 * Com\Tecnick\Barcode\Type\Linear\CodeOneTwoEight;
 *
 * CodeOneTwoEight Barcode type class
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
class CodeOneTwoEight extends \Com\Tecnick\Barcode\Type\Linear\CodeOneTwoEight\Process
{
    /**
     * Get the coe point array
     *
     * @return array<int, int>
     *
     * @throws BarcodeException in case of error
     */
    protected function getCodeData(): array
    {
        $code = $this->code;
        // array of symbols
        $code_data = [];
        // split code into sequences
        $sequence = $this->getNumericSequence($code);
        // process the sequence
        $startid = 0;
        foreach ($sequence as $key => $seq) {
            $processMethod = 'processSequence' . $seq[0];
            $this->$processMethod($sequence, $code_data, $startid, $key, $seq);
        }

        return $this->finalizeCodeData($code_data, $startid);
    }

    /**
     * Process the A sequence
     *
     * @param array<int, array<int, string>>  $sequence   Sequence to process
     * @param array<int, int>  $code_data  Array of codepoints to alter
     * @param int    $startid    Start ID
     * @param int    $key        Sequence current key
     * @param array{string, string, int} $seq        Sequence current value
     *
     * @throws BarcodeException in case of error
     */
    protected function processSequenceA(
        array &$sequence,
        array &$code_data,
        int &$startid,
        int $key,
        array $seq
    ): void {
        if ($key == 0) {
            $startid = 103;
        } elseif ($sequence[($key - 1)][0] != 'A') {
            if (
                ($seq[2] == 1)
                && ($key > 0)
                && ($sequence[($key - 1)][0] == 'B')
                && (! isset($sequence[($key - 1)][3]))
            ) {
                // single character shift
                $code_data[] = 98;
                // mark shift
                $sequence[$key][3] = true;
            } elseif (! isset($sequence[($key - 1)][3])) {
                $code_data[] = 101;
            }
        }

        $this->getCodeDataA($code_data, $seq[1], (int) $seq[2]);
    }

    /**
     * Process the B sequence
     *
     * @param array<int, array<int, string>>  $sequence   Sequence to process
     * @param array<int, int>  $code_data  Array of codepoints to alter
     * @param int    $startid    Start ID
     * @param int    $key        Sequence current key
     * @param array{string, string, int} $seq        Sequence current value
     *
     * @throws BarcodeException in case of error
     */
    protected function processSequenceB(
        array &$sequence,
        array &$code_data,
        int &$startid,
        int $key,
        array $seq
    ): void {
        if ($key == 0) {
            $this->processSequenceBA($sequence, $code_data, $startid, $key, $seq);
        } elseif ($sequence[($key - 1)][0] != 'B') {
            $this->processSequenceBB($sequence, $code_data, $key, $seq);
        }

        $this->getCodeDataB($code_data, $seq[1], (int) $seq[2]);
    }

    /**
     * Process the B-A sequence
     *
     * @param array<int, array<int, string>>  $sequence   Sequence to process
     * @param array<int, int>  $code_data  Array of codepoints to alter
     * @param int    $startid    Start ID
     * @param int    $key        Sequence current key
     * @param array{string, string, int} $seq        Sequence current value
     *
     * @throws BarcodeException in case of error
     */
    protected function processSequenceBA(
        array &$sequence,
        array &$code_data,
        int &$startid,
        int $key,
        array $seq
    ): void {
        $tmpchr = ord($seq[1][0]);
        if (
            ($seq[2] == 1)
            && ($tmpchr >= 241)
            && ($tmpchr <= 244)
            && isset($sequence[($key + 1)])
            && ($sequence[($key + 1)][0] != 'B')
        ) {
            switch ($sequence[($key + 1)][0]) {
                case 'A':
                    $startid = 103;
                    $sequence[$key][0] = 'A';
                    $code_data[] = $this::FNC_A[$tmpchr];
                    break;
                case 'C':
                    $startid = 105;
                    $sequence[$key][0] = 'C';
                    $code_data[] = $this::FNC_A[$tmpchr];
                    break;
            }
        } else {
            $startid = 104;
        }
    }

    /**
     * Process the B-B sequence
     *
     * @param array<int, array<int, string>>  $sequence   Sequence to process
     * @param array<int, int>  $code_data  Array of codepoints to alter
     * @param int    $key        Sequence current key
     * @param array{string, string, int} $seq        Sequence current value
     *
     * @throws BarcodeException in case of error
     */
    protected function processSequenceBB(
        array &$sequence,
        array &$code_data,
        int $key,
        array $seq
    ): void {
        if (
            ($seq[2] == 1)
            && ($key > 0)
            && ($sequence[($key - 1)][0] == 'A')
            && (! isset($sequence[($key - 1)][3]))
        ) {
            // single character shift
            $code_data[] = 98;
            // mark shift
            $sequence[$key][3] = true;
        } elseif (! isset($sequence[($key - 1)][3])) {
            $code_data[] = 100;
        }
    }

    /**
     * Process the C sequence
     *
     * @param array<int, array<int, string>>  $sequence   Sequence to process
     * @param array<int, int>  $code_data  Array of codepoints to alter
     * @param int    $startid    Start ID
     * @param int    $key        Sequence current key
     * @param array{string, string, int} $seq        Sequence current value
     *
     * @throws BarcodeException in case of error
     */
    protected function processSequenceC(
        array &$sequence,
        array &$code_data,
        int &$startid,
        int $key,
        array $seq
    ): void {
        if ($key == 0) {
            $startid = 105;
        } elseif ($sequence[($key - 1)][0] != 'C') {
            $code_data[] = 99;
        }

        $this->getCodeDataC($code_data, $seq[1]);
    }

    /**
     * Set the bars array.
     *
     * @throws BarcodeException in case of error
     */
    protected function setBars(): void
    {
        $code_data = $this->getCodeData();
        $this->ncols = 0;
        $this->nrows = 1;
        $this->bars = [];
        foreach ($code_data as $val) {
            $seq = $this::CHBAR[$val];
            for ($pos = 0; $pos < 6; ++$pos) {
                $bar_width = (int) $seq[$pos];
                if ((($pos % 2) == 0) && ($bar_width > 0)) {
                    $this->bars[] = [$this->ncols, 0, $bar_width, 1];
                }

                $this->ncols += $bar_width;
            }
        }
    }
}
