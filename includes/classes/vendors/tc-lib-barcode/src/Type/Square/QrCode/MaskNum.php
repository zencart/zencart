<?php

/**
 * MaskNum.php
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
 * Com\Tecnick\Barcode\Type\Square\QrCode\MaskNum
 *
 * @since       2015-02-21
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2010-2024 Nicola Asuni - Tecnick.com LTD
 * @license     http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
abstract class MaskNum
{
    /**
     * Make Mask Number
     *
     * @param int   $maskNo Mask number
     * @param int   $width  Width
     * @param array<int, string> $frame  Frame
     * @param array<int, string> $mask   Mask
     *
     * @return int mask number
     */
    protected function makeMaskNo(
        int $maskNo,
        int $width,
        array $frame,
        array &$mask
    ): int {
        $bnum = 0;
        $bitMask = $this->generateMaskNo($maskNo, $width, $frame);
        $mask = $frame;
        for ($ypos = 0; $ypos < $width; ++$ypos) {
            for ($xpos = 0; $xpos < $width; ++$xpos) {
                if ($bitMask[$ypos][$xpos] == 1) {
                    $mask[$ypos][$xpos] = chr(ord($frame[$ypos][$xpos]) ^ ((int) ($bitMask[$ypos][$xpos])));
                }

                $bnum += ord($mask[$ypos][$xpos]) & 1;
            }
        }

        return $bnum;
    }

    /**
     * Return bit mask
     *
     * @param int   $maskNo Mask number
     * @param int   $width  Width
     * @param array<int, string> $frame  Frame
     *
     * @return array<int, array<int, int>> bit mask
     */
    protected function generateMaskNo(
        int $maskNo,
        int $width,
        array $frame
    ): array {
        $bitMask = array_fill(0, $width, array_fill(0, $width, 0));
        for ($ypos = 0; $ypos < $width; ++$ypos) {
            for ($xpos = 0; $xpos < $width; ++$xpos) {
                if ((ord($frame[$ypos][$xpos]) & 0x80) !== 0) {
                    $bitMask[$ypos][$xpos] = 0;
                    continue;
                }
                $maskFunc = match ($maskNo) {
                    0 => (($xpos + $ypos) & 1),
                    1 => ($ypos & 1),
                    2 => ($xpos % 3),
                    3 => (($xpos + $ypos) % 3),
                    4 => ((((int) ($ypos / 2)) + ((int) ($xpos / 3))) & 1),
                    5 => ((($xpos * $ypos) & 1) + ($xpos * $ypos) % 3),
                    6 => (((($xpos * $ypos) & 1) + ($xpos * $ypos) % 3) & 1),
                    7 => (((($xpos * $ypos) % 3) + (($xpos + $ypos) & 1)) & 1),
                    default => 1,
                };
                $bitMask[$ypos][$xpos] = (($maskFunc == 0) ? 1 : 0);
            }
        }

        return $bitMask;
    }
}
