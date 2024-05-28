<?php

/**
 * Split.php
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

namespace Com\Tecnick\Barcode\Type\Square\QrCode;

use Com\Tecnick\Barcode\Exception as BarcodeException;

/**
 * Com\Tecnick\Barcode\Type\Square\QrCode\Split
 *
 * @since       2015-02-21
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2010-2024 Nicola Asuni - Tecnick.com LTD
 * @license     http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 *
 * @phpstan-import-type Item from \Com\Tecnick\Barcode\Type\Square\QrCode\Estimate
 */
class Split
{
    /**
     * Input items
     *
     * @var array<int, Item>
     */
    protected array $items = [];

    /**
     * Initialize
     *
     * @param ByteStream $encodingMode ByteStream Class object
     * @param int          $hint    Encoding mode
     * @param int          $version Code version
     */
    public function __construct(
        /**
         * EncodingMode class object
         */
        protected EncodingMode $encodingMode,
        /**
         * Encoding mode
         */
        protected int $hint,
        /**
         * QR code version.
         * The Size of QRcode is defined as version. Version is an integer value from 1 to 40.
         * Version 1 is 21*21 matrix. And 4 modules increases whenever 1 version increases.
         * So version 40 is 177*177 matrix.
         */
        protected int $version
    ) {
    }

    /**
     * Split the input string
     *
     * @param string $data Data
     *
     * @return array<int, Item> items
     */
    public function getSplittedString(string $data): array
    {
        while (strlen($data) > 0) {
            $mode = $this->encodingMode->getEncodingMode($data, 0);
            switch ($mode) {
                case Data::ENC_MODES['NM']:
                    $length = $this->eatNum($data);
                    break;
                case Data::ENC_MODES['AN']:
                    $length = $this->eatAn($data);
                    break;
                case Data::ENC_MODES['KJ']:
                    if ($this->hint == Data::ENC_MODES['KJ']) {
                        $length = $this->eatKanji($data);
                    } else {
                        $length = $this->eat8($data);
                    }

                    break;
                default:
                    $length = $this->eat8($data);
                    break;
            }

            if ($length == 0) {
                break;
            }

            if ($length < 0) {
                throw new BarcodeException('Error while splitting the input data');
            }

            $data = substr($data, $length);
        }

        return $this->items;
    }

    /**
     * eatNum
     *
     * @param string $data Data
     *
     * @return int run
     */
    protected function eatNum(string $data): int
    {
        $lng = $this->encodingMode->getLengthIndicator(Data::ENC_MODES['NM'], $this->version);
        $pos = 0;
        while ($this->encodingMode->isDigitAt($data, $pos)) {
            ++$pos;
        }

        $mode = $this->encodingMode->getEncodingMode($data, $pos);
        if ($mode == Data::ENC_MODES['8B']) {
            $dif = $this->encodingMode->estimateBitsModeNum($pos) + 4 + $lng
                + $this->encodingMode->estimateBitsMode8(1)         // + 4 + l8
                - $this->encodingMode->estimateBitsMode8($pos + 1); // - 4 - l8
            if ($dif > 0) {
                return $this->eat8($data);
            }
        }

        if ($mode == Data::ENC_MODES['AN']) {
            $dif = $this->encodingMode->estimateBitsModeNum($pos) + 4 + $lng
                + $this->encodingMode->estimateBitsModeAn(1)        // + 4 + la
                - $this->encodingMode->estimateBitsModeAn($pos + 1); // - 4 - la
            if ($dif > 0) {
                return $this->eatAn($data);
            }
        }

        $this->items = $this->encodingMode->appendNewInputItem(
            $this->items,
            Data::ENC_MODES['NM'],
            $pos,
            str_split($data)
        );
        return $pos;
    }

    /**
     * eatAn
     *
     * @param string $data Data
     *
     * @return int run
     */
    protected function eatAn(string $data): int
    {
        $lag = $this->encodingMode->getLengthIndicator(Data::ENC_MODES['AN'], $this->version);
        $lng = $this->encodingMode->getLengthIndicator(Data::ENC_MODES['NM'], $this->version);
        $pos = 1;
        while ($this->encodingMode->isAlphanumericAt($data, $pos)) {
            if ($this->encodingMode->isDigitAt($data, $pos)) {
                $qix = $pos;
                while ($this->encodingMode->isDigitAt($data, $qix)) {
                    ++$qix;
                }

                $dif = $this->encodingMode->estimateBitsModeAn($pos) // + 4 + lag
                    + $this->encodingMode->estimateBitsModeNum($qix - $pos) + 4 + $lng
                    - $this->encodingMode->estimateBitsModeAn($qix); // - 4 - la
                if ($dif < 0) {
                    break;
                } else {
                    $pos = $qix;
                }
            } else {
                ++$pos;
            }
        }

        if (! $this->encodingMode->isAlphanumericAt($data, $pos)) {
            $dif = $this->encodingMode->estimateBitsModeAn($pos) + 4 + $lag
                + $this->encodingMode->estimateBitsMode8(1) // + 4 + l8
                - $this->encodingMode->estimateBitsMode8($pos + 1); // - 4 - l8
            if ($dif > 0) {
                return $this->eat8($data);
            }
        }

        $this->items = $this->encodingMode->appendNewInputItem(
            $this->items,
            Data::ENC_MODES['AN'],
            $pos,
            str_split($data)
        );
        return $pos;
    }

    /**
     * eatKanji
     *
     * @param string $data Data
     *
     * @return int run
     */
    protected function eatKanji(string $data): int
    {
        $pos = 0;
        while ($this->encodingMode->getEncodingMode($data, $pos) == Data::ENC_MODES['KJ']) {
            $pos += 2;
        }

        $this->items = $this->encodingMode->appendNewInputItem(
            $this->items,
            Data::ENC_MODES['KJ'],
            $pos,
            str_split($data)
        );
        return $pos;
    }

    /**
     * eat8
     *
     * @param string $data Data
     *
     * @return int run
     */
    protected function eat8(string $data): int
    {
        $lag = $this->encodingMode->getLengthIndicator(Data::ENC_MODES['AN'], $this->version);
        $lng = $this->encodingMode->getLengthIndicator(Data::ENC_MODES['NM'], $this->version);
        $pos = 1;
        $dataStrLen = strlen($data);
        while ($pos < $dataStrLen) {
            $mode = $this->encodingMode->getEncodingMode($data, $pos);
            if ($mode == Data::ENC_MODES['KJ']) {
                break;
            }

            if ($mode == Data::ENC_MODES['NM']) {
                $qix = $pos;
                while ($this->encodingMode->isDigitAt($data, $qix)) {
                    ++$qix;
                }

                $dif = $this->encodingMode->estimateBitsMode8($pos) // + 4 + l8
                    + $this->encodingMode->estimateBitsModeNum($qix - $pos) + 4 + $lng
                    - $this->encodingMode->estimateBitsMode8($qix); // - 4 - l8
                if ($dif < 0) {
                    break;
                } else {
                    $pos = $qix;
                }
            } elseif ($mode == Data::ENC_MODES['AN']) {
                $qix = $pos;
                while ($this->encodingMode->isAlphanumericAt($data, $qix)) {
                    ++$qix;
                }

                $dif = $this->encodingMode->estimateBitsMode8($pos)  // + 4 + l8
                    + $this->encodingMode->estimateBitsModeAn($qix - $pos) + 4 + $lag
                    - $this->encodingMode->estimateBitsMode8($qix); // - 4 - l8
                if ($dif < 0) {
                    break;
                } else {
                    $pos = $qix;
                }
            } else {
                ++$pos;
            }
        }

        $this->items = $this->encodingMode->appendNewInputItem(
            $this->items,
            Data::ENC_MODES['8B'],
            $pos,
            str_split($data)
        );
        return $pos;
    }
}
