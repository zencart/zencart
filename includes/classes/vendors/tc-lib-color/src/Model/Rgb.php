<?php

/**
 * Rgb.php
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

namespace Com\Tecnick\Color\Model;

/**
 * Com\Tecnick\Color\Model\Rgb
 *
 * RGB Color Model class
 *
 * @since     2015-02-21
 * @category  Library
 * @package   Color
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2015-2024 Nicola Asuni - Tecnick.com LTD
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-color
 */
class Rgb extends \Com\Tecnick\Color\Model
{
    /**
     * Color Model type
     *
     * @var string
     */
    protected $type = 'RGB';

    /**
     * Value of the Red color component [0..1]
     *
     * @var float
     */
    protected $cmp_red = 0.0;

    /**
     * Value of the Green color component [0..1]
     *
     * @var float
     */
    protected $cmp_green = 0.0;

    /**
     * Value of the Blue color component [0..1]
     *
     * @var float
     */
    protected $cmp_blue = 0.0;

    /**
     * Get an array with all color components
     *
     * @return array<string, float> with keys ('R', 'G', 'B', 'A')
     */
    public function getArray(): array
    {
        return [
            'R' => $this->cmp_red,
            'G' => $this->cmp_green,
            'B' => $this->cmp_blue,
            'A' => $this->cmp_alpha,
        ];
    }

    /**
     * Get an array with color components values normalized between 0 and $max.
     * NOTE: the alpha and other fraction component values are kept in the [0..1] range.
     *
     * @param int $max Maximum value to return (reference value)
     *
     * @return array<string, float> with keys ('R', 'G', 'B', 'A')
     */
    public function getNormalizedArray(int $max): array
    {
        return [
            'R' => $this->getNormalizedValue($this->cmp_red, $max),
            'G' => $this->getNormalizedValue($this->cmp_green, $max),
            'B' => $this->getNormalizedValue($this->cmp_blue, $max),
            'A' => $this->cmp_alpha,
        ];
    }

    /**
     * Get the CSS representation of the color: rgba(R, G, B, A)
     * NOTE: Supported since CSS3 and above.
     *       Use getHexadecimalColor() for CSS1 and CSS2
     */
    public function getCssColor(): string
    {
        return 'rgba('
            . $this->getNormalizedValue($this->cmp_red, 100) . '%,'
            . $this->getNormalizedValue($this->cmp_green, 100) . '%,'
            . $this->getNormalizedValue($this->cmp_blue, 100) . '%,'
            . $this->cmp_alpha
            . ')';
    }

    /**
     * Get the color format used in Acrobat JavaScript
     * NOTE: the alpha channel is omitted from this representation unless is 0 = transparent
     */
    public function getJsPdfColor(): string
    {
        if ($this->cmp_alpha == 0) {
            return '["T"]'; // transparent color
        }

        return sprintf('["RGB",%F,%F,%F]', $this->cmp_red, $this->cmp_green, $this->cmp_blue);
    }

    /**
     * Get a space separated string with color component values.
     */
    public function getComponentsString(): string
    {
        return sprintf('%F %F %F', $this->cmp_red, $this->cmp_green, $this->cmp_blue);
    }

    /**
     * Get the color components format used in PDF documents (RGB)
     * NOTE: the alpha channel is omitted
     *
     * @param bool $stroke True for stroking (lines, drawing) and false for non-stroking (text and area filling).
     */
    public function getPdfColor(bool $stroke = false): string
    {
        $mode = 'rg';
        if ($stroke) {
            $mode = strtoupper($mode);
        }

        return $this->getComponentsString() . ' ' . $mode . "\n";
    }

    /**
     * Get an array with Gray color components
     *
     * @return array<string, float> with keys ('gray')
     */
    public function toGrayArray(): array
    {
        // convert using the SMPTE 295M-1997 standard conversion constants
        return [
            'gray' => (max(
                0,
                min(
                    1,
                    ((0.2126 * $this->cmp_red) + (0.7152 * $this->cmp_green) + (0.0722 * $this->cmp_blue))
                )
            )),
            'alpha' => $this->cmp_alpha,
        ];
    }

    /**
     * Get an array with RGB color components
     *
     * @return array<string, float> with keys ('red', 'green', 'blue', 'alpha')
     */
    public function toRgbArray(): array
    {
        return [
            'red' => $this->cmp_red,
            'green' => $this->cmp_green,
            'blue' => $this->cmp_blue,
            'alpha' => $this->cmp_alpha,
        ];
    }

    /**
     * Get an array with HSL color components
     *
     * @return array<string, float> with keys ('hue', 'saturation', 'lightness', 'alpha')
     */
    public function toHslArray(): array
    {
        $min = min($this->cmp_red, $this->cmp_green, $this->cmp_blue);
        $max = max($this->cmp_red, $this->cmp_green, $this->cmp_blue);
        $lightness = (($min + $max) / 2);
        $saturation = 0;
        $hue = 0;
        if ($min != $max) {
            $diff = ($max - $min);
            $saturation = $lightness < 0.5 ? $diff / ($max + $min) : $diff / (2.0 - $max - $min);

            switch ($max) {
                case $this->cmp_red:
                    $dgb = ($this->cmp_green - $this->cmp_blue);
                    $hue = ($dgb / $diff) + (($dgb < 0) ? 6 : 0);
                    break;
                case $this->cmp_green:
                    $hue = (2.0 + (($this->cmp_blue - $this->cmp_red) / $diff));
                    break;
                case $this->cmp_blue:
                    $hue = (4.0 + (($this->cmp_red - $this->cmp_green) / $diff));
                    break;
            }

            $hue /= 6; // 6 = 360 / 60
        }

        return [
            'hue' => max(0, min(1, $hue)),
            'saturation' => max(0, min(1, $saturation)),
            'lightness' => max(0, min(1, $lightness)),
            'alpha' => $this->cmp_alpha,
        ];
    }

    /**
     * Get an array with CMYK color components
     *
     * @return array<string, float> with keys ('cyan', 'magenta', 'yellow', 'key', 'alpha')
     */
    public function toCmykArray(): array
    {
        $cyan = (1 - $this->cmp_red);
        $magenta = (1 - $this->cmp_green);
        $yellow = (1 - $this->cmp_blue);
        $key = 1;
        if ($cyan < $key) {
            $key = $cyan;
        }

        if ($magenta < $key) {
            $key = $magenta;
        }

        if ($yellow < $key) {
            $key = $yellow;
        }

        if ($key == 1) {
            // black
            $cyan = 0;
            $magenta = 0;
            $yellow = 0;
        } else {
            $cyan = (($cyan - $key) / (1 - $key));
            $magenta = (($magenta - $key) / (1 - $key));
            $yellow = (($yellow - $key) / (1 - $key));
        }

        return [
            'cyan' => max(0, min(1, $cyan)),
            'magenta' => max(0, min(1, $magenta)),
            'yellow' => max(0, min(1, $yellow)),
            'key' => max(0, min(1, $key)),
            'alpha' => $this->cmp_alpha,
        ];
    }

    /**
     * Invert the color
     */
    public function invertColor(): self
    {
        $this->cmp_red = (1 - $this->cmp_red);
        $this->cmp_green = (1 - $this->cmp_green);
        $this->cmp_blue = (1 - $this->cmp_blue);
        return $this;
    }
}
