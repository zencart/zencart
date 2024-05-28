<?php

/**
 * Spec.php
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
 * Com\Tecnick\Barcode\Type\Square\QrCode\Spec
 *
 * @since       2015-02-21
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2010-2024 Nicola Asuni - Tecnick.com LTD
 * @license     http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 *
 * @phpstan-type EccSpec array{
 *            0: int,
 *            1: int,
 *            2: int,
 *            3: int,
 *            4: int,
 *        }
 */
class Spec extends \Com\Tecnick\Barcode\Type\Square\QrCode\SpecRs
{
    /**
     * Return maximum data code length (bytes) for the version.
     *
     * @param int $version Version
     * @param int $level   Error correction level
     *
     * @return int maximum size (bytes)
     */
    public function getDataLength(int $version, int $level): int
    {
        return (Data::CAPACITY[$version][Data::QRCAP_WORDS] - Data::CAPACITY[$version][Data::QRCAP_EC][$level]);
    }

    /**
     * Return maximum error correction code length (bytes) for the version.
     *
     * @param int $version Version
     * @param int $level   Error correction level
     *
     * @return int ECC size (bytes)
     */
    public function getECCLength(int $version, int $level): int
    {
        return Data::CAPACITY[$version][Data::QRCAP_EC][$level];
    }

    /**
     * Return the width of the symbol for the version.
     *
     * @param int $version Version
     *
     * @return int width
     */
    public function getWidth(int $version): int
    {
        return Data::CAPACITY[$version][Data::QRCAP_WIDTH];
    }

    /**
     * Return the numer of remainder bits.
     *
     * @param int $version Version
     *
     * @return int number of remainder bits
     */
    public function getRemainder(int $version): int
    {
        return Data::CAPACITY[$version][Data::QRCAP_REMINDER];
    }

    /**
     * Return the maximum length for the mode and version.
     *
     * @param int $mode    Encoding mode
     * @param int $version Version
     *
     * @return int the maximum length (bytes)
     */
    public function maximumWords(int $mode, int $version): int
    {
        if ($mode == Data::ENC_MODES['ST']) {
            return 3;
        }

        if ($version <= 9) {
            $lval = 0;
        } elseif ($version <= 26) {
            $lval = 1;
        } else {
            $lval = 2;
        }

        $bits = Data::LEN_TABLE_BITS[$mode][$lval];
        $words = (1 << $bits) - 1;
        if ($mode == Data::ENC_MODES['KJ']) {
            $words *= 2; // the number of bytes is required
        }

        return $words;
    }

    /**
     * Return an array of ECC specification.
     *
     * @param int   $version Version
     * @param int   $level   Error correction level
     * @param EccSpec $spec Array of ECC specification
     *
     * @return EccSpec spec:
     *            0 = # of type1 blocks
     *            1 = # of data code
     *            2 = # of ecc code
     *            3 = # of type2 blocks
     *            4 = # of data code
     */
    public function getEccSpec(int $version, int $level, array $spec): array
    {
        if (count($spec) < 5) {
            $spec = [0, 0, 0, 0, 0];
        }

        $bv1 = Data::ECC_TABLE[$version][$level][0];
        $bv2 = Data::ECC_TABLE[$version][$level][1];
        $data = $this->getDataLength($version, $level);
        $ecc = $this->getECCLength($version, $level);
        if ($bv2 == 0) {
            $spec[0] = $bv1;
            $spec[1] = (int) ($data / $bv1); /* @phpstan-ignore-line */
            $spec[2] = (int) ($ecc / $bv1); /* @phpstan-ignore-line */
            $spec[3] = 0;
            $spec[4] = 0;
        } else {
            $spec[0] = $bv1;
            $spec[1] = (int) ($data / ($bv1 + $bv2));
            $spec[2] = (int) ($ecc / ($bv1 + $bv2));
            $spec[3] = $bv2;
            $spec[4] = $spec[1] + 1;
        }

        return $spec;
    }

    /**
     * Return BCH encoded format information pattern.
     *
     * @param int $maskNo Mask number
     * @param int $level  Error correction level
     */
    public function getFormatInfo(int $maskNo, int $level): int
    {
        if (
            ($maskNo < 0)
            || ($maskNo > 7)
            || ($level < 0)
            || ($level > 3)
        ) {
            return 0;
        }

        return Data::FORMAT_INFO[$level][$maskNo];
    }
}
