<?php

/**
 * Spot.php
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
use Com\Tecnick\Color\Model\Cmyk;

/**
 * Com\Tecnick\Color\Spot
 *
 * Spot Color class
 *
 * @since     2015-02-21
 * @category  Library
 * @package   Color
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2015-2024 Nicola Asuni - Tecnick.com LTD
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-color
 */
class Spot extends \Com\Tecnick\Color\Web
{
    /**
     * Array of default Spot colors
     * Color keys must be in lowercase and without spaces.
     *
     * @var array<string, array{
     *       'name': string,
     *       'color': array{
     *           'cyan': int|float,
     *           'magenta': int|float,
     *           'yellow': int|float,
     *           'key': int|float,
     *           'alpha': int|float,
     *       }
     *     }>
     */
    public const DEFAULT_SPOT_COLORS = [
        'none' => [
            'name' => 'None',
            'color' => [
                'cyan' => 0,
                'magenta' => 0,
                'yellow' => 0,
                'key' => 0,
                'alpha' => 1,
            ],
        ],
        'all' => [
            'name' => 'All',
            'color' => [
                'cyan' => 1,
                'magenta' => 1,
                'yellow' => 1,
                'key' => 1,
                'alpha' => 1,
            ],
        ],
        'cyan' => [
            'name' => 'Cyan',
            'color' => [
                'cyan' => 1,
                'magenta' => 0,
                'yellow' => 0,
                'key' => 0,
                'alpha' => 1,
            ],
        ],
        'magenta' => [
            'name' => 'Magenta',
            'color' => [
                'cyan' => 0,
                'magenta' => 1,
                'yellow' => 0,
                'key' => 0,
                'alpha' => 1,
            ],
        ],
        'yellow' => [
            'name' => 'Yellow',
            'color' => [
                'cyan' => 0,
                'magenta' => 0,
                'yellow' => 1,
                'key' => 0,
                'alpha' => 1,
            ],
        ],
        'key' => [
            'name' => 'Key',
            'color' => [
                'cyan' => 0,
                'magenta' => 0,
                'yellow' => 0,
                'key' => 1,
                'alpha' => 1,
            ],
        ],
        'white' => [
            'name' => 'White',
            'color' => [
                'cyan' => 0,
                'magenta' => 0,
                'yellow' => 0,
                'key' => 0,
                'alpha' => 1,
            ],
        ],
        'black' => [
            'name' => 'Black',
            'color' => [
                'cyan' => 0,
                'magenta' => 0,
                'yellow' => 0,
                'key' => 1,
                'alpha' => 1,
            ],
        ],
        'red' => [
            'name' => 'Red',
            'color' => [
                'cyan' => 0,
                'magenta' => 1,
                'yellow' => 1,
                'key' => 0,
                'alpha' => 1,
            ],
        ],
        'green' => [
            'name' => 'Green',
            'color' => [
                'cyan' => 1,
                'magenta' => 0,
                'yellow' => 1,
                'key' => 0,
                'alpha' => 1,
            ],
        ],
        'blue' => [
            'name' => 'Blue',
            'color' => [
                'cyan' => 1,
                'magenta' => 1,
                'yellow' => 0,
                'key' => 0,
                'alpha' => 1,
            ],
        ],
    ];

    /**
     * Array of Spot colors
     *
     * @var array<string, array{'i': int, 'n': int, 'name': string, 'color': Cmyk}>
     */
    protected $spot_colors = [];

    /**
     * Returns the array of spot colors.
     *
     * @return array<string, array{'i': int, 'n': int, 'name': string, 'color': Cmyk}>
     */
    public function getSpotColors(): array
    {
        return $this->spot_colors;
    }

    /**
     * Return the normalized version of the spot color name
     *
     * @param string $name Full name of the spot color.
     */
    public function normalizeSpotColorName(string $name): string
    {
        $ret = preg_replace('/[^a-z0-9]*/', '', strtolower($name));
        return $ret ?? '';
    }

    /**
     * Return the requested spot color data array
     *
     * @param string $name Full name of the spot color.
     *
     * @return array{'i': int, 'n': int, 'name': string, 'color': Cmyk}
     *
     * @throws ColorException if the color is not found
     */
    public function getSpotColor(string $name): array
    {
        $key = $this->normalizeSpotColorName($name);
        if (empty($this->spot_colors[$key])) {
            // search on default spot colors
            if (empty(self::DEFAULT_SPOT_COLORS[$key])) {
                throw new ColorException('unable to find the spot color: ' . $key);
            }

            $this->addSpotColor($key, new Cmyk(self::DEFAULT_SPOT_COLORS[$key]['color']));
        }

        return $this->spot_colors[$key];
    }

    /**
     * Return the requested spot color CMYK object
     *
     * @param string $name Full name of the spot color.
     *
     * @throws ColorException if the color is not found
     */
    public function getSpotColorObj(string $name): Cmyk
    {
        $spot = $this->getSpotColor($name);
        return $spot['color'];
    }

    /**
     * Add a new spot color or overwrite an existing one with the same name.
     *
     * @param string $name Full name of the spot color.
     * @param Cmyk   $cmyk CMYK color object
     */
    public function addSpotColor(string $name, Cmyk $cmyk): void
    {
        $key = $this->normalizeSpotColorName($name);
        $num = isset($this->spot_colors[$key]) ? $this->spot_colors[$key]['i'] : (count($this->spot_colors) + 1);

        $this->spot_colors[$key] = [
            'i' => $num, // color index
            'n' => 0, // PDF object number
            'name' => $name, // color name (key)
            'color' => $cmyk, // CMYK color object
        ];
    }

    /**
     * Returns the PDF command to output Spot color objects.
     *
     * @param int $pon Current PDF object number
     *
     * @return string PDF command
     */
    public function getPdfSpotObjects(int &$pon): string
    {
        $out = '';
        foreach ($this->spot_colors as $name => $color) {
            $out .= (++$pon) . ' 0 obj' . "\n";
            $this->spot_colors[$name]['n'] = $pon;
            $out .= '[/Separation /' . str_replace(' ', '#20', $name)
                . ' /DeviceCMYK <<'
                . '/Range [0 1 0 1 0 1 0 1]'
                . ' /C0 [0 0 0 0]'
                . ' /C1 [' . $color['color']->getComponentsString() . ']'
                . ' /FunctionType 2'
                . ' /Domain [0 1]'
                . ' /N 1'
                . '>>]' . "\n"
                . 'endobj' . "\n";
        }

        return $out;
    }

    /**
     * Returns the PDF command to output Spot color resources.
     *
     * @return string PDF command
     */
    public function getPdfSpotResources(): string
    {
        if ($this->spot_colors === []) {
            return '';
        }

        $out = '/ColorSpace << ';
        foreach ($this->spot_colors as $spot_color) {
            $out .= '/CS' . $spot_color['i'] . ' ' . $spot_color['n'] . ' 0 R ';
        }

        return $out . ('>>' . "\n");
    }
}
