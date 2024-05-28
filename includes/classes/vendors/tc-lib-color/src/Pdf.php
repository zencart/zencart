<?php

/**
 * Pdf.php
 *
 * @since     2015-02-21
 * @category  Library
 * @package   Color
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2015-2024 Nicola Asuni - Tecnick.com LTD
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-color
 *
 * This file is part of tc-lib-color software library.
 */

namespace Com\Tecnick\Color;

use Com\Tecnick\Color\Exception as ColorException;

/**
 * Com\Tecnick\Color\Pdf
 *
 * PDF Color class
 *
 * @since     2015-02-21
 * @category  Library
 * @package   Color
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2015-2024 Nicola Asuni - Tecnick.com LTD
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-color
 */
class Pdf extends \Com\Tecnick\Color\Spot
{
    /**
     * Array of valid JavaScript color names to be used in PDF documents
     */
    public const JSCOLOR = [
        'transparent',
        'black',
        'white',
        'red',
        'green',
        'blue',
        'cyan',
        'magenta',
        'yellow',
        'dkGray',
        'gray',
        'ltGray',
    ];

    /**
     * Convert color to javascript string
     *
     * @param string $color color name or color object
     */
    public function getJsColorString(string $color): string
    {
        if (in_array($color, self::JSCOLOR)) {
            return 'color.' . $color;
        }

        try {
            if (($colobj = $this->getColorObj($color)) instanceof \Com\Tecnick\Color\Model) {
                return $colobj->getJsPdfColor();
            }
        } catch (ColorException $colorException) {
            assert(true); // noop
        }

        // default transparent color
        return 'color.' . self::JSCOLOR[0];
    }

    /**
     * Returns a color object from an HTML, CSS or Spot color representation.
     *
     * @param string $color HTML, CSS or Spot color to parse
     */
    public function getColorObject(string $color): ?\Com\Tecnick\Color\Model
    {
        try {
            return $this->getSpotColorObj($color);
        } catch (ColorException $colorException) {
            assert(true); // noop
        }

        try {
            return $this->getColorObj($color);
        } catch (ColorException $colorException) {
            assert(true); // noop
        }

        return null;
    }

    /**
     * Get the color components format used in PDF documents
     * NOTE: the alpha channel is omitted
     *
     * @param string $color  HTML, CSS or Spot color to parse
     * @param bool   $stroke True for stroking (lines, drawing) and false for non-stroking (text and area filling).
     * @param float  $tint   Intensity of the color (from 0 to 1; 1 = full intensity).
     */
    public function getPdfColor(string $color, bool $stroke = false, float $tint = 1): string
    {
        try {
            $col = $this->getSpotColor($color);
            $tint = sprintf('cs %F scn', (max(0, min(1, $tint))));
            if ($stroke) {
                $tint = strtoupper($tint);
            }

            return sprintf('/CS%d %s' . "\n", $col['i'], $tint);
        } catch (ColorException $colorException) {
            assert(true); // noop
        }

        try {
            $col = $this->getColorObj($color);
            if ($col instanceof \Com\Tecnick\Color\Model) {
                return $col->getPdfColor($stroke);
            }
        } catch (ColorException $colorException) {
            assert(true); // noop
        }

        return '';
    }

    /**
     * Get the RGB color components format used in PDF documents
     *
     * @param string $color HTML, CSS or Spot color to parse
     */
    public function getPdfRgbComponents(string $color): string
    {
        $model = $this->getColorObject($color);
        if (! $model instanceof \Com\Tecnick\Color\Model) {
            return '';
        }

        $cmp = $model->toRgbArray();
        return sprintf('%F %F %F', $cmp['red'], $cmp['green'], $cmp['blue']);
    }
}
