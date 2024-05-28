<?php

/**
 * Layers.php
 *
 * @since       2023-10-13
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2023-2024 Nicola Asuni - Tecnick.com LTD
 * @license     http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 *
 * This file is part of tc-lib-barcode software library.
 */

namespace Com\Tecnick\Barcode\Type\Square\Aztec;

/**
 * Com\Tecnick\Barcode\Type\Square\Aztec\Layers
 *
 * Layers for Aztec Barcode type class
 *
 * @since       2023-10-13
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2023-2024 Nicola Asuni - Tecnick.com LTD
 * @license     http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
abstract class Layers extends \Com\Tecnick\Barcode\Type\Square\Aztec\Codeword
{
    /**
     * True for compact mode (up to 4 layers), false for full-range mode (up to 32 layers).
     */
    protected bool $compact = true;

    /**
     * Number of data layers.
     */
    protected int $numlayers = 0;

    /**
     * Size data for the selected layer.
     *
     * @var array{int, int, int, int, int, int, int}
     */
    protected array $layer = [0, 0, 0, 0, 0, 0, 0];

    /**
     * Returns the minimum number of layers required.
     *
     * @param array<int, array{int, int, int, int, int, int, int}> $data
     *        Either the Data::SIZE_COMPACT or Data::SIZE_FULL array.
     * @param int   $numbits The number of bits to encode.
     */
    protected function getMinLayers(array $data, int $numbits): int
    {
        if ($numbits <= $data[count($data)][3]) {
            foreach ($data as $numlayers => $size) {
                if ($numbits <= $size[3]) {
                    return $numlayers;
                }
            }
        }

        return 0;
    }

    /**
     * Select the layer by the number of bits to encode.
     *
     * @param int    $numbits The number of bits to encode.
     * @param string $mode    The mode to use (A = Automatic; F = Full Range mode).
     *
     * @return bool Returns true if the size computation was successful, false otherwise.
     */
    protected function setLayerByBits(int $numbits, string $mode = 'A'): bool
    {
        $this->numlayers = 0;
        if ($mode == 'A') {
            $this->compact = true;
            $this->numlayers = $this->getMinLayers(Data::SIZE_COMPACT, $numbits);
        }

        if ($this->numlayers == 0) {
            $this->compact = false;
            $this->numlayers = $this->getMinLayers(Data::SIZE_FULL, $numbits);
        }

        if ($this->numlayers == 0) {
            return false;
        }

        $this->layer = $this->compact ? Data::SIZE_COMPACT[$this->numlayers] : Data::SIZE_FULL[$this->numlayers];
        return true;
    }

    /**
     * Computes the type and number of required layers and performs bit stuffing
     *
     * @param int    $ecc  The error correction level.
     * @param string $mode The mode to use (A = Automatic; F = Full Range mode).
     *
     * @return bool Returns true if the size computation was successful, false otherwise.
     */
    protected function sizeAndBitStuffing(int $ecc, string $mode = 'A'): bool
    {
        $nsbits = 0;
        $eccbits = (11 + (int) (($this->totbits * $ecc) / 100));
        do {
            if (! $this->setLayerByBits(($this->totbits + $nsbits + $eccbits), $mode)) {
                return false;
            }

            $nsbits = $this->bitStuffing();
        } while (($nsbits + $eccbits) > $this->layer[3]);

        $this->bitstream = [];
        $this->totbits = 0;
        $this->mergeTmpCwdRaw();
        return true;
    }

    /**
     * Bit-stuffing the bitstream into Reed–Solomon codewords.
     * The resulting codewords are stored in the temporary tmpCdws array.
     *
     * @return int The number of bits in the bitstream after bit stuffing.
     */
    protected function bitStuffing(): int
    {
        $nsbits = 0;
        $wsize = $this->layer[2];
        $mask = ((1 << $wsize) - 2); // b-1 bits at 1 and last bit at 0
        $this->tmpCdws = [];
        for ($wid = 0; $wid < $this->totbits; $wid += $wsize) {
            $word = 0;
            for ($idx = 0; $idx < $wsize; ++$idx) {
                $bid = ($wid + $idx);
                if (($bid >= $this->totbits) || ($this->bitstream[$bid] == 1)) {
                    $word |= (1 << ($wsize - 1 - $idx)); // the first bit is MSB
                }
            }

            // If the first b−1 bits of a code word have the same value,
            // an extra bit with the complementary value is inserted into the data stream.
            if (($word & $mask) === $mask) {
                $word &= $mask;
                --$wid;
            } elseif (($word & $mask) == 0) {
                $word |= 1;
                --$wid;
            }

            $this->tmpCdws[] = [$wsize, $word];
            $nsbits += $wsize;
        }

        return $nsbits;
    }
}
