<?php

/**
 * Model.php
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

/**
 * Com\Tecnick\Color\Model
 *
 * Color Model class
 *
 * @since     2015-02-21
 * @category  Library
 * @package   Color
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2015-2024 Nicola Asuni - Tecnick.com LTD
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-color
 */
abstract class Model implements \Com\Tecnick\Color\Model\Template
{
    /**
     * Color Model type (GRAY, RGB, HSL, CMYK)
     *
     * @var string
     */
    protected $type;

    /**
     * Value of the Alpha channel component.
     * Values range between 0.0 (fully transparent) and 1.0 (fully opaque)
     *
     * @var float
     */
    protected $cmp_alpha = 1.0;

    /**
     * Initialize a new color object.
     *
     * @param array<string, int|float|string> $components color components.
     */
    public function __construct(array $components)
    {
        foreach ($components as $color => $value) {
            $property = 'cmp_' . $color;
            if (property_exists($this, $property)) {
                $this->$property = (max(0, min(1, (float) $value)));
            }
        }
    }

    /**
     * Get the color model type (GRAY, RGB, HSL, CMYK)
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Get the normalized integer value of the specified float fraction
     *
     * @param float $value Fraction value to convert [0..1]
     * @param int   $max   Maximum value to return (reference value)
     *
     * @return float value [0..$max]
     */
    public function getNormalizedValue(float $value, int $max): float
    {
        return round(max(0, min($max, ($max * $value))));
    }

    /**
     * Get the normalized hexadecimal value of the specified float fraction
     *
     * @param float $value Fraction value to convert [0..1]
     * @param int   $max   Maximum value to return (reference value)
     */
    public function getHexValue(float $value, int $max): string
    {
        return sprintf('%02x', $this->getNormalizedValue($value, $max));
    }

    /**
     * Get the Hexadecimal representation of the color with alpha channel: #RRGGBBAA
     */
    public function getRgbaHexColor(): string
    {
        $rgba = $this->toRgbArray();
        return '#'
            . $this->getHexValue($rgba['red'], 255)
            . $this->getHexValue($rgba['green'], 255)
            . $this->getHexValue($rgba['blue'], 255)
            . $this->getHexValue($rgba['alpha'], 255);
    }

    /**
     * Get the Hexadecimal representation of the color: #RRGGBB
     */
    public function getRgbHexColor(): string
    {
        $rgba = $this->toRgbArray();
        return '#'
            . $this->getHexValue($rgba['red'], 255)
            . $this->getHexValue($rgba['green'], 255)
            . $this->getHexValue($rgba['blue'], 255);
    }
}
