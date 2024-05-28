<?php

/**
 * EncodingMode.php
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

/**
 * Com\Tecnick\Barcode\Type\Square\QrCode\EncodingMode
 *
 * @since       2015-02-21
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2010-2024 Nicola Asuni - Tecnick.com LTD
 * @license     http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
abstract class EncodingMode extends \Com\Tecnick\Barcode\Type\Square\QrCode\InputItem
{
    /**
     * Get the encoding mode to use
     *
     * @param string $data Data
     * @param int    $pos  Position
     *
     * @return int mode
     */
    public function getEncodingMode(string $data, int $pos): int
    {
        if (! isset($data[$pos])) {
            return Data::ENC_MODES['NL'];
        }

        if ($this->isDigitAt($data, $pos)) {
            return Data::ENC_MODES['NM'];
        }

        if ($this->isAlphanumericAt($data, $pos)) {
            return Data::ENC_MODES['AN'];
        }

        return $this->getEncodingModeKj($data, $pos);
    }

    /**
     * Get the encoding mode for KJ or 8B
     *
     * @param string $data Data
     * @param int    $pos  Position
     *
     * @return int mode
     */
    protected function getEncodingModeKj(string $data, int $pos): int
    {
        if (($this->hint == Data::ENC_MODES['KJ']) && isset($data[($pos + 1)])) {
            $word = ((ord($data[$pos]) << 8) | ord($data[($pos + 1)]));
            if ((($word >= 0x8140) && ($word <= 0x9ffc)) || (($word >= 0xe040) && ($word <= 0xebbf))) {
                return Data::ENC_MODES['KJ'];
            }
        }

        return Data::ENC_MODES['8B'];
    }

    /**
     * Return true if the character at specified position is a number
     *
     * @param string $str Data
     * @param int    $pos Character position
     */
    public function isDigitAt(string $str, int $pos): bool
    {
        if (! isset($str[$pos])) {
            return false;
        }

        return ((ord($str[$pos]) >= ord('0')) && (ord($str[$pos]) <= ord('9')));
    }

    /**
     * Return true if the character at specified position is an alphanumeric character
     *
     * @param string $str Data
     * @param int    $pos Character position
     */
    public function isAlphanumericAt(string $str, int $pos): bool
    {
        if (! isset($str[$pos])) {
            return false;
        }

        return ($this->lookAnTable(ord($str[$pos])) >= 0);
    }

    /**
     * Append one bitstream to another
     *
     * @param array<int, int> $bitstream Original bitstream
     * @param array<int, int> $append    Bitstream to append
     *
     * @return array<int, int> bitstream
     */
    protected function appendBitstream(array $bitstream, array $append): array
    {
        if ((! is_array($append)) || (count($append) == 0)) {
            return $bitstream;
        }

        if (count($bitstream) == 0) {
            return $append;
        }

        return array_values(array_merge($bitstream, $append));
    }

    /**
     * Append one bitstream created from number to another
     *
     * @param array<int, int> $bitstream Original bitstream
     * @param int   $bits      Number of bits
     * @param int   $num       Number
     *
     * @return array<int, int> bitstream
     */
    protected function appendNum(array $bitstream, int $bits, int $num): array
    {
        if ($bits == 0) {
            return [];
        }

        return $this->appendBitstream($bitstream, $this->newFromNum($bits, $num));
    }

    /**
     * Append one bitstream created from bytes to another
     *
     * @param array<int, int> $bitstream Original bitstream
     * @param int   $size      Size
     * @param array<int, int> $data      Bytes
     *
     * @return array<int, int> bitstream
     */
    protected function appendBytes(array $bitstream, int $size, array $data): array
    {
        if ($size == 0) {
            return [];
        }

        return $this->appendBitstream($bitstream, $this->newFromBytes($size, $data));
    }

    /**
     * Return new bitstream from number
     *
     * @param int $bits Number of bits
     * @param int $num  Number
     *
     * @return array<int, int> bitstream
     */
    protected function newFromNum(int $bits, int $num): array
    {
        $bstream = $this->allocate($bits);
        $mask = 1 << ($bits - 1);
        for ($idx = 0; $idx < $bits; ++$idx) {
            $bstream[$idx] = ($num & $mask) !== 0 ? 1 : 0;

            $mask >>= 1;
        }

        return $bstream;
    }

    /**
     * Return new bitstream from bytes
     *
     * @param int   $size Size
     * @param array<int, int> $data Bytes
     *
     * @return array<int, int> bitstream
     */
    protected function newFromBytes(int $size, array $data): array
    {
        $bstream = $this->allocate($size * 8);
        $pval = 0;
        for ($idx = 0; $idx < $size; ++$idx) {
            $mask = 0x80;
            for ($jdx = 0; $jdx < 8; ++$jdx) {
                $bstream[$pval] = ($data[$idx] & $mask) !== 0 ? 1 : 0;

                ++$pval;
                $mask >>= 1;
            }
        }

        return $bstream;
    }

    /**
     * Return an array with zeros
     *
     * @param int $setLength Array size
     *
     * @return array<int, int> array
     */
    protected function allocate(int $setLength): array
    {
        return array_fill(0, $setLength, 0);
    }
}
