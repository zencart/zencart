<?php

/**
 * InputItem.php
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
 * Com\Tecnick\Barcode\Type\Square\QrCode\InputItem
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
abstract class InputItem extends \Com\Tecnick\Barcode\Type\Square\QrCode\Estimate
{
    /**
     * Look up the alphabet-numeric conversion table (see JIS X0510:2004, pp.19)
     *
     * @param int $chr Character value
     */
    public function lookAnTable(int $chr): int
    {
        return (($chr > 127) ? -1 : Data::AN_TABLE[$chr]);
    }

    /**
     * Append data to an input object.
     * The data is copied and appended to the input object.
     *
     * @param array<int, Item> $items Input items
     * @param int   $mode  Encoding mode.
     * @param int   $size  Size of data (byte).
     * @param array<int, string> $data  Array of input data.
     *
     * @return array<int, Item> items
     */
    public function appendNewInputItem(
        array $items,
        int $mode,
        int $size,
        array $data
    ): array {
        $newitem = $this->newInputItem($mode, $size, $data);
        if ($newitem !== []) {
            $items[] = $newitem;
        }

        return $items;
    }

    /**
     * newInputItem
     *
     * @param int   $mode    Encoding mode.
     * @param int   $size    Size of data (byte).
     * @param array<int, string> $data    Array of input data.
     * @param array<int, int> $bstream Binary stream
     *
     * @return Item input item
     */
    protected function newInputItem(
        int $mode,
        int $size,
        array $data,
        array $bstream = []
    ): array {
        $setData = array_slice($data, 0, $size);
        if (count($setData) < $size) {
            $setData = array_merge($setData, array_fill(0, ($size - count($setData)), '0'));
        }

        if (! $this->check($mode, $size, $setData)) {
            throw new BarcodeException('Invalid input item');
        }

        return [
            'mode' => $mode,
            'size' => $size,
            'data' => $setData,
            'bstream' => $bstream,
        ];
    }

    /**
     * Validate the input data.
     *
     * @param int   $mode Encoding mode.
     * @param int   $size Size of data (byte).
     * @param array<int, string> $data Data to validate
     *
     * @return bool true in case of valid data, false otherwise
     */
    protected function check(
        int $mode,
        int $size,
        array $data
    ): bool {
        if ($size <= 0) {
            return false;
        }

        return match ($mode) {
            Data::ENC_MODES['NM'] => $this->checkModeNum($size, $data),
            Data::ENC_MODES['AN'] => $this->checkModeAn($size, $data),
            Data::ENC_MODES['KJ'] => $this->checkModeKanji($size, $data),
            Data::ENC_MODES['8B'] => true,
            Data::ENC_MODES['ST'] => true,
            default => false,
        };
    }

    /**
     * checkModeNum
     *
     * @param int   $size Size of data (byte).
     * @param array<int, string> $data Data to validate
     *
     * @return bool true or false
     */
    protected function checkModeNum(int $size, array $data): bool
    {
        for ($idx = 0; $idx < $size; ++$idx) {
            if ((ord($data[$idx]) < ord('0')) || (ord($data[$idx]) > ord('9'))) {
                return false;
            }
        }

        return true;
    }

    /**
     * checkModeAn
     *
     * @param int   $size Size of data (byte).
     * @param array<int, string> $data Data to validate
     *
     * @return bool true or false
     */
    protected function checkModeAn(int $size, array $data): bool
    {
        for ($idx = 0; $idx < $size; ++$idx) {
            if ($this->lookAnTable(ord($data[$idx])) == -1) {
                return false;
            }
        }

        return true;
    }

    /**
     * checkModeKanji
     *
     * @param int   $size Size of data (byte).
     * @param array<int, string> $data Data to validate
     *
     * @return bool true or false
     */
    protected function checkModeKanji(int $size, array $data): bool
    {
        if (($size & 1) !== 0) {
            return false;
        }

        for ($idx = 0; $idx < $size; $idx += 2) {
            $val = (ord($data[$idx]) << 8) | ord($data[($idx + 1)]);
            if (($val < 0x8140) || (($val > 0x9ffc) && ($val < 0xe040)) || ($val > 0xebbf)) {
                return false;
            }
        }

        return true;
    }
}
