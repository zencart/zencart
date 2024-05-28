<?php

/**
 * Gray.php
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
 * Com\Tecnick\Color\Model\Gray
 *
 * Gray Color Model class
 *
 * @since     2015-02-21
 * @category  Library
 * @package   Color
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2015-2024 Nicola Asuni - Tecnick.com LTD
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-color
 */
class Gray extends \Com\Tecnick\Color\Model
{
    /**
     * Color Model type
     *
     * @var string
     */
    protected $type = 'GRAY';

    /**
     * Value of the Gray color component [0..1]
     *
     * @var float
     */
    protected $cmp_gray = 0.0;

    /**
     * Get an array with all color components
     *
     * @return array<string, float> with keys ('G', 'A')
     */
    public function getArray(): array
    {
        return [
            'G' => $this->cmp_gray,
            'A' => $this->cmp_alpha,
        ];
    }

    /**
     * Get an array with color components values normalized between 0 and $max.
     * NOTE: the alpha and other fraction component values are kept in the [0..1] range.
     *
     * @param int $max Maximum value to return (reference value)
     *
     * @return array<string, float> with keys ('G', 'A')
     */
    public function getNormalizedArray(int $max): array
    {
        return [
            'G' => $this->getNormalizedValue($this->cmp_gray, $max),
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
            . $this->getNormalizedValue($this->cmp_gray, 100) . '%,'
            . $this->getNormalizedValue($this->cmp_gray, 100) . '%,'
            . $this->getNormalizedValue($this->cmp_gray, 100) . '%,'
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

        return sprintf('["G",%F]', $this->cmp_gray);
    }

    /**
     * Get a space separated string with color component values.
     */
    public function getComponentsString(): string
    {
        return sprintf('%F', $this->cmp_gray);
    }

    /**
     * Get the color components format used in PDF documents (G)
     * NOTE: the alpha channel is omitted
     *
     * @param bool $stroke True for stroking (lines, drawing) and false for non-stroking (text and area filling).
     */
    public function getPdfColor(bool $stroke = false): string
    {
        $mode = 'g';
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
        return [
            'gray' => $this->cmp_gray,
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
            'red' => $this->cmp_gray,
            'green' => $this->cmp_gray,
            'blue' => $this->cmp_gray,
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
        return [
            'hue' => 0,
            'saturation' => 0,
            'lightness' => $this->cmp_gray,
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
        return [
            'cyan' => 0,
            'magenta' => 0,
            'yellow' => 0,
            'key' => $this->cmp_gray,
            'alpha' => $this->cmp_alpha,
        ];
    }

    /**
     * Invert the color
     */
    public function invertColor(): self
    {
        $this->cmp_gray = (1 - $this->cmp_gray);
        return $this;
    }
}
