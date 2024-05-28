<?php

/**
 * Type.php
 *
 * @since     2015-02-21
 * @category  Library
 * @package   Barcode
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2015-2024 Nicola Asuni - Tecnick.com LTD
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-barcode
 *
 * This file is part of tc-lib-barcode software library.
 */

namespace Com\Tecnick\Barcode;

use Com\Tecnick\Barcode\Exception as BarcodeException;
use Com\Tecnick\Color\Exception as ColorException;
use Com\Tecnick\Color\Model\Rgb;
use Com\Tecnick\Color\Pdf;

/**
 * Com\Tecnick\Barcode\Type
 *
 * Barcode Type class
 *
 * @since     2015-02-21
 * @category  Library
 * @package   Barcode
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2015-2024 Nicola Asuni - Tecnick.com LTD
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-barcode
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
abstract class Type extends \Com\Tecnick\Barcode\Type\Convert implements Model
{
    /**
     * Initialize a new barcode object
     *
     * @param string                    $code    Barcode content
     * @param int                       $width   Barcode width in user units (excluding padding).
     *                                           A negative value indicates the multiplication
     *                                           factor for each column.
     * @param int                       $height  Barcode height in user units (excluding padding).
     *                                           A negative value indicates the multiplication
     *                                           factor for each row.
     * @param string                    $color   Foreground color in Web notation
     *                                           (color name, or hexadecimal code, or CSS syntax)
     * @param array<int|float|string>   $params  Array containing extra parameters for the specified barcode type
     * @param array{int, int, int, int} $padding Additional padding to add around the barcode
     *                                           (top, right, bottom, left) in user units. A
     *                                           negative value indicates the number or rows
     *                                           or columns.
     *
     * @throws BarcodeException in case of error
     * @throws ColorException in case of color error
     */
    public function __construct(
        string $code,
        int $width = -1,
        int $height = -1,
        string $color = 'black',
        array $params = [],
        array $padding = [0, 0, 0, 0]
    ) {
        $this->code = $code;
        $this->extcode = $code;
        $this->params = $params;
        $this->setParameters();
        $this->setBars();
        $this->setSize($width, $height, $padding);
        $this->setColor($color);
    }

    /**
     * Set extra (optional) parameters
     */
    protected function setParameters(): void
    {
    }

    /**
     * Set the bars array
     *
     * @throws BarcodeException in case of error
     */
    protected function setBars(): void
    {
    }

    /**
     * Set the size of the barcode to be exported
     *
     * @param int                       $width   Barcode width in user units (excluding padding).
     *                                           A negative value indicates the multiplication
     *                                           factor for each column.
     * @param int                       $height  Barcode height in user units (excluding padding).
     *                                           A negative value indicates the multiplication
     *                                           factor for each row.
     * @param array{int, int, int, int} $padding Additional padding to add around the barcode
     *                                           (top, right, bottom, left) in user units. A
     *                                           negative value indicates the number or rows
     *                                           or columns.
     */
    public function setSize(
        int $width,
        int $height,
        array $padding = [0, 0, 0, 0]
    ): static {
        $this->width = $width;
        if ($this->width <= 0) {
            $this->width = (abs(min(-1, $this->width)) * $this->ncols);
        }

        $this->height = $height;
        if ($this->height <= 0) {
            $this->height = (abs(min(-1, $this->height)) * $this->nrows);
        }

        $this->width_ratio = ($this->width / $this->ncols);
        $this->height_ratio = ($this->height / $this->nrows);

        $this->setPadding($padding);

        return $this;
    }

    /**
     * Set the barcode padding
     *
     * @param array{int, int, int, int} $padding Additional padding to add around the barcode
     *                                           (top, right, bottom, left) in user units.
     *                                           A negative value indicates the number or rows or columns.
     *
     * @throws BarcodeException in case of error
     */
    protected function setPadding(array $padding): static
    {
        if (! is_array($padding) || (count($padding) != 4)) {
            throw new BarcodeException(
                'Invalid padding, expecting an array of 4 numbers (top, right, bottom, left)'
            );
        }

        $map = [
            ['T', $this->height_ratio],
            ['R', $this->width_ratio],
            ['B', $this->height_ratio],
            ['L', $this->width_ratio],
        ];
        foreach ($padding as $key => $val) {
            if ($val < 0) {
                $val = (abs(min(-1, $val)) * $map[$key][1]);
            }

            $this->padding[$map[$key][0]] = (int) $val;
        }

        return $this;
    }

    /**
     * Set the color of the bars.
     * If the color is transparent or empty it will be set to the default black color.
     *
     * @param string $color Foreground color in Web notation (color name, or hexadecimal code, or CSS syntax)
     *
     * @throws ColorException in case of color error
     * @throws BarcodeException in case of empty or transparent color
     */
    public function setColor(string $color): static
    {
        $colobj = $this->getRgbColorObject($color);
        if (! $colobj instanceof \Com\Tecnick\Color\Model\Rgb) {
            throw new BarcodeException('The foreground color cannot be empty or transparent');
        }

        $this->color_obj = $colobj;
        return $this;
    }

    /**
     * Set the background color
     *
     * @param string $color Background color in Web notation (color name, or hexadecimal code, or CSS syntax)
     *
     * @throws ColorException in case of color error
     */
    public function setBackgroundColor(string $color): static
    {
        $this->bg_color_obj = $this->getRgbColorObject($color);
        return $this;
    }

    /**
     * Get the RGB Color object for the given color representation
     *
     * @param string $color Color in Web notation (color name, or hexadecimal code, or CSS syntax)
     *
     * @throws ColorException in case of color error
     */
    protected function getRgbColorObject(string $color): ?Rgb
    {
        $pdf = new Pdf();
        $cobj = $pdf->getColorObject($color);
        if ($cobj instanceof \Com\Tecnick\Color\Model) {
            return new Rgb($cobj->toRgbArray());
        }

        return null;
    }

    /**
     * Get the barcode raw array
     *
     * @return array{
     *             'type': string,
     *             'format': string,
     *             'params': array<int|float|string>,
     *             'code': string,
     *             'extcode': string,
     *             'ncols': int,
     *             'nrows': int,
     *             'width': int,
     *             'height': int,
     *             'width_ratio': float,
     *             'height_ratio': float,
     *             'padding': array{'T': int, 'R': int, 'B': int, 'L': int},
     *             'full_width': int,
     *             'full_height': int,
     *             'color_obj': Rgb,
     *             'bg_color_obj': ?Rgb,
     *             'bars': array<array{int, int, int, int}>,
     *         }
     */
    public function getArray(): array
    {
        return [
            'type' => $this::TYPE,
            'format' => $this::FORMAT,
            'params' => $this->params,
            'code' => $this->code,
            'extcode' => $this->extcode,
            'ncols' => $this->ncols,
            'nrows' => $this->nrows,
            'width' => $this->width,
            'height' => $this->height,
            'width_ratio' => $this->width_ratio,
            'height_ratio' => $this->height_ratio,
            'padding' => $this->padding,
            'full_width' => ($this->width + $this->padding['L'] + $this->padding['R']),
            'full_height' => ($this->height + $this->padding['T'] + $this->padding['B']),
            'color_obj' => $this->color_obj,
            'bg_color_obj' => $this->bg_color_obj,
            'bars' => $this->bars,
        ];
    }

    /**
     * Get the extended code (code + checksum)
     */
    public function getExtendedCode(): string
    {
        return $this->extcode;
    }

    /**
     * Sends the data as file to the browser.
     *
     * @param string $data The file data.
     * @param string $mime The file MIME type (i.e. 'application/svg+xml' or 'image/png').
     * @param string $fileext The file extension (i.e. 'svg' or 'png').
     * @param string|null $filename The file name without extension (optional).
     *                              Only allows alphanumeric characters, underscores and hyphens.
     *                              Defaults to a md5 hash of the data.
     *
     * @return void
     */
    protected function getHTTPFile(
        string $data,
        string $mime,
        string $fileext,
        ?string $filename = null,
    ): void {
        if (is_null($filename) || (preg_match('/^[a-zA-Z0-9_\-]{1,250}$/', $filename) !== 1)) {
            $filename = md5($data);
        }

        header('Content-Type: ' . $mime);
        header('Cache-Control: private, must-revalidate, post-check=0, pre-check=0, max-age=1');
        header('Pragma: public');
        header('Expires: Thu, 04 jan 1973 00:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Content-Disposition: inline; filename="' . $filename . '.' . $fileext . '";');
        if (empty($_SERVER['HTTP_ACCEPT_ENCODING'])) {
            // the content length may vary if the server is using compression
            header('Content-Length: ' . strlen($data));
        }

        echo $data;
    }

    /**
     * Get the barcode as SVG image object
     *
     * @param string|null $filename The file name without extension (optional).
     *                              Only allows alphanumeric characters, underscores and hyphens.
     *                              Defaults to a md5 hash of the data.
     *                              The file extension is always '.svg'.
     */
    public function getSvg(?string $filename = null): void
    {
        $this->getHTTPFile($this->getSvgCode(), 'application/svg+xml', 'svg', $filename);
    }

    /**
     * Get the barcode as SVG code
     *
     * @return string SVG code
     */
    public function getSvgCode(): string
    {
        // flags for htmlspecialchars
        $hflag = ENT_NOQUOTES;
        if (defined('ENT_XML1') && defined('ENT_DISALLOWED')) {
            $hflag = ENT_XML1 | ENT_DISALLOWED;
        }

        $width = sprintf('%F', ($this->width + $this->padding['L'] + $this->padding['R']));
        $height = sprintf('%F', ($this->height + $this->padding['T'] + $this->padding['B']));
        $svg = '<?xml version="1.0" standalone="no" ?>' . "\n"
            . '<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">'
            . "\n"
            . '<svg'
            . ' width="' . $width . '"'
            . ' height="' . $height . '"'
            . ' viewBox="0 0 ' . $width . ' ' . $height . '"'
            . ' version="1.1"'
            . ' xmlns="http://www.w3.org/2000/svg"'
            . '>' . "\n"
            . "\t" . '<desc>' . htmlspecialchars($this->code, $hflag, 'UTF-8') . '</desc>' . "\n";
        if ($this->bg_color_obj instanceof \Com\Tecnick\Color\Model\Rgb) {
            $svg .= '	<rect x="0" y="0" width="' . $width . '"'
                . ' height="' . $height . '"'
                . ' fill="' . $this->bg_color_obj->getRgbHexColor() . '"'
                . ' stroke="none"'
                . ' stroke-width="0"'
                . ' stroke-linecap="square"'
                . ' />' . "\n";
        }

        $svg .= '	<g id="bars" fill="' . $this->color_obj->getRgbHexColor() . '"'
            . ' stroke="none"'
            . ' stroke-width="0"'
            . ' stroke-linecap="square"'
            . '>' . "\n";
        $bars = $this->getBarsArrayXYWH();
        foreach ($bars as $bar) {
            $svg .= '		<rect x="' . sprintf('%F', $bar[0]) . '"'
                . ' y="' . sprintf('%F', $bar[1]) . '"'
                . ' width="' . sprintf('%F', $bar[2]) . '"'
                . ' height="' . sprintf('%F', $bar[3]) . '"'
                . ' />' . "\n";
        }

        return $svg . ('	</g>' . "\n"
            . '</svg>' . "\n");
    }

    /**
     * Get an HTML representation of the barcode.
     *
     * @return string HTML code (DIV block)
     */
    public function getHtmlDiv(): string
    {
        $html = '<div style="width:' . sprintf('%F', ($this->width + $this->padding['L'] + $this->padding['R'])) . 'px;'
            . 'height:' . sprintf('%F', ($this->height + $this->padding['T'] + $this->padding['B'])) . 'px;'
            . 'position:relative;'
            . 'font-size:0;'
            . 'border:none;'
            . 'padding:0;'
            . 'margin:0;';
        if ($this->bg_color_obj instanceof \Com\Tecnick\Color\Model\Rgb) {
            $html .= 'background-color:' . $this->bg_color_obj->getCssColor() . ';';
        }

        $html .= '">' . "\n";
        $bars = $this->getBarsArrayXYWH();
        foreach ($bars as $bar) {
            $html .= '	<div style="background-color:' . $this->color_obj->getCssColor() . ';'
                . 'left:' . sprintf('%F', $bar[0]) . 'px;'
                . 'top:' . sprintf('%F', $bar[1]) . 'px;'
                . 'width:' . sprintf('%F', $bar[2]) . 'px;'
                . 'height:' . sprintf('%F', $bar[3]) . 'px;'
                . 'position:absolute;'
                . 'border:none;'
                . 'padding:0;'
                . 'margin:0;'
                . '">&nbsp;</div>' . "\n";
        }

        return $html . ('</div>' . "\n");
    }

    /**
     * Get Barcode as PNG Image (requires GD or Imagick library)
     *
     * @param string|null $filename The file name without extension (optional).
     *                              Only allows alphanumeric characters, underscores and hyphens.
     *                              Defaults to a md5 hash of the data.
     *                              The file extension is always '.png'.
     */
    public function getPng(?string $filename = null): void
    {
        $this->getHTTPFile($this->getPngData(), 'image/png', 'png', $filename);
    }

    /**
     * Get the barcode as PNG image (requires GD or Imagick library)
     *
     * @param bool $imagick If true try to use the Imagick extension
     *
     * @return string PNG image data
     */
    public function getPngData(bool $imagick = true): string
    {
        if ($imagick && extension_loaded('imagick')) {
            return $this->getPngDataImagick();
        }

        $gdImage = $this->getGd();
        ob_start();
        imagepng($gdImage);
        $data = ob_get_clean();
        if ($data === false) {
            throw new BarcodeException('Unable to get PNG data');
        }
        return $data;
    }

    /**
     * Get the barcode as PNG image (requires Imagick library)
     *
     * @throws BarcodeException if the Imagick library is not installed
     */
    public function getPngDataImagick(): string
    {
        $imagick = new \Imagick();
        $width = (int) ceil($this->width + $this->padding['L'] + $this->padding['R']);
        $height = (int) ceil($this->height + $this->padding['T'] + $this->padding['B']);
        $imagick->newImage($width, $height, 'none', 'png');
        $imagickdraw = new \imagickdraw();
        if ($this->bg_color_obj instanceof \Com\Tecnick\Color\Model\Rgb) {
            $rgbcolor = $this->bg_color_obj->getNormalizedArray(255);
            $bg_color = new \imagickpixel('rgb(' . $rgbcolor['R'] . ',' . $rgbcolor['G'] . ',' . $rgbcolor['B'] . ')');
            $imagickdraw->setfillcolor($bg_color);
            $imagickdraw->rectangle(0, 0, $width, $height);
        }

        $rgbcolor = $this->color_obj->getNormalizedArray(255);
        $bar_color = new \imagickpixel('rgb(' . $rgbcolor['R'] . ',' . $rgbcolor['G'] . ',' . $rgbcolor['B'] . ')');
        $imagickdraw->setfillcolor($bar_color);
        $bars = $this->getBarsArrayXYXY();
        foreach ($bars as $bar) {
            $imagickdraw->rectangle($bar[0], $bar[1], $bar[2], $bar[3]);
        }

        $imagick->drawimage($imagickdraw);
        return $imagick->getImageBlob();
    }

    /**
     * Get the barcode as GD image object (requires GD library)
     *
     * @throws BarcodeException if the GD library is not installed
     */
    public function getGd(): \GdImage
    {
        $width = (int) ceil($this->width + $this->padding['L'] + $this->padding['R']);
        $height = (int) ceil($this->height + $this->padding['T'] + $this->padding['B']);
        $img = imagecreate($width, $height);
        if ($img === false) {
            throw new BarcodeException('Unable to create GD image');
        }

        if (! $this->bg_color_obj instanceof \Com\Tecnick\Color\Model\Rgb) {
            $bgobj = clone $this->color_obj;
            $rgbcolor = $bgobj->invertColor()->getNormalizedArray(255);
            $background_color = imagecolorallocate(
                $img,
                (int) round($rgbcolor['R']),
                (int) round($rgbcolor['G']),
                (int) round($rgbcolor['B']),
            );
            if ($background_color === false) {
                throw new BarcodeException('Unable to allocate default GD background color');
            }
            imagecolortransparent($img, $background_color);
        } else {
            $rgbcolor = $this->bg_color_obj->getNormalizedArray(255);
            $bg_color = imagecolorallocate(
                $img,
                (int) round($rgbcolor['R']),
                (int) round($rgbcolor['G']),
                (int) round($rgbcolor['B']),
            );
            if ($bg_color === false) {
                throw new BarcodeException('Unable to allocate GD background color');
            }
            imagefilledrectangle($img, 0, 0, $width, $height, $bg_color);
        }

        $rgbcolor = $this->color_obj->getNormalizedArray(255);
        $bar_color = imagecolorallocate(
            $img,
            (int) round($rgbcolor['R']),
            (int) round($rgbcolor['G']),
            (int) round($rgbcolor['B']),
        );
        if ($bar_color === false) {
            throw new BarcodeException('Unable to allocate GD foreground color');
        }
        $bars = $this->getBarsArrayXYXY();
        foreach ($bars as $bar) {
            imagefilledrectangle(
                $img,
                (int) floor($bar[0]),
                (int) floor($bar[1]),
                (int) floor($bar[2]),
                (int) floor($bar[3]),
                $bar_color
            );
        }

        return $img;
    }

    /**
     * Get a raw barcode string representation using characters
     *
     * @param string $space_char Character or string to use for filling empty spaces
     * @param string $bar_char   Character or string to use for filling bars
     */
    public function getGrid(
        string $space_char = '0',
        string $bar_char = '1'
    ): string {
        $raw = $this->getGridArray($space_char, $bar_char);
        $grid = '';
        foreach ($raw as $row) {
            $grid .= implode('', $row) . "\n";
        }

        return $grid;
    }

    /**
     * Get the array containing all the formatted bars coordinates
     *
     * @return array<int, array{float, float, float, float}>
     */
    public function getBarsArrayXYXY(): array
    {
        $rect = [];
        foreach ($this->bars as $bar) {
            if ($bar[2] <= 0) {
                continue;
            }

            if ($bar[3] <= 0) {
                continue;
            }

            $rect[] = $this->getBarRectXYXY($bar);
        }

        if ($this->nrows > 1) {
            // reprint rotated to cancel row gaps
            $rot = $this->getRotatedBarArray();
            foreach ($rot as $bar) {
                if ($bar[2] <= 0) {
                    continue;
                }

                if ($bar[3] <= 0) {
                    continue;
                }

                $rect[] = $this->getBarRectXYXY($bar);
            }
        }

        return $rect;
    }

    /**
     * Get the array containing all the formatted bars coordinates
     *
     * @return array<int, array{float, float, float, float}>
     */
    public function getBarsArrayXYWH(): array
    {
        $rect = [];
        foreach ($this->bars as $bar) {
            if ($bar[2] <= 0) {
                continue;
            }

            if ($bar[3] <= 0) {
                continue;
            }

            $rect[] = $this->getBarRectXYWH($bar);
        }

        if ($this->nrows > 1) {
            // reprint rotated to cancel row gaps
            $rot = $this->getRotatedBarArray();
            foreach ($rot as $bar) {
                if ($bar[2] <= 0) {
                    continue;
                }

                if ($bar[3] <= 0) {
                    continue;
                }

                $rect[] = $this->getBarRectXYWH($bar);
            }
        }

        return $rect;
    }
}
