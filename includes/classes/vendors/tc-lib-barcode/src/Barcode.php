<?php

/**
 * Barcode.php
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

/**
 * Com\Tecnick\Barcode\Barcode
 *
 * Barcode Barcode class
 *
 * @since     2015-02-21
 * @category  Library
 * @package   Barcode
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2010-2024 Nicola Asuni - Tecnick.com LTD
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-barcode
 */
class Barcode
{
    /**
     * List of supported Barcode Types with description.
     *
     * @var array<string, string>
     */
    public const BARCODETYPES = [
        'C128' => 'CODE 128',
        'C128A' => 'CODE 128 A',
        'C128B' => 'CODE 128 B',
        'C128C' => 'CODE 128 C',
        'C39' => 'CODE 39 - ANSI MH10.8M-1983 - USD-3 - 3 of 9.',
        'C39+' => 'CODE 39 + CHECKSUM',
        'C39E' => 'CODE 39 EXTENDED',
        'C39E+' => 'CODE 39 EXTENDED + CHECKSUM',
        'C93' => 'CODE 93 - USS-93',
        'CODABAR' => 'CODABAR',
        'CODE11' => 'CODE 11',
        'EAN13' => 'EAN 13',
        'EAN2' => 'EAN 2-Digits UPC-Based Extension',
        'EAN5' => 'EAN 5-Digits UPC-Based Extension',
        'EAN8' => 'EAN 8',
        'I25' => 'Interleaved 2 of 5',
        'I25+' => 'Interleaved 2 of 5 + CHECKSUM',
        'IMB' => 'IMB - Intelligent Mail Barcode - Onecode - USPS-B-3200',
        'IMBPRE' => 'IMB - Intelligent Mail Barcode pre-processed',
        'KIX' => 'KIX (Klant index - Customer index)',
        'LRAW' => '1D RAW MODE (comma-separated rows of 01 strings)',
        'MSI' => 'MSI (Variation of Plessey code)',
        'MSI+' => 'MSI + CHECKSUM (modulo 11)',
        'PHARMA' => 'PHARMACODE',
        'PHARMA2T' => 'PHARMACODE TWO-TRACKS',
        'PLANET' => 'PLANET',
        'POSTNET' => 'POSTNET',
        'RMS4CC' => 'RMS4CC (Royal Mail 4-state Customer Bar Code)',
        'S25' => 'Standard 2 of 5',
        'S25+' => 'Standard 2 of 5 + CHECKSUM',
        'UPCA' => 'UPC-A',
        'UPCE' => 'UPC-E',
        'AZTEC' => 'AZTEC Code (ISO/IEC 24778:2008)',
        'DATAMATRIX' => 'DATAMATRIX (ISO/IEC 16022)',
        'PDF417' => 'PDF417 (ISO/IEC 15438:2006)',
        'QRCODE' => 'QR-CODE',
        'SRAW' => '2D RAW MODE (comma-separated rows of 01 strings)',
    ];

    /**
     * Get the barcode object
     *
     * @param string                    $type    Barcode type
     * @param string                    $code    Barcode content
     * @param int                       $width   Barcode width in user units (excluding padding).
     *                                           A negative value indicates the multiplication
     *                                           factor for each column.
     * @param int                       $height  Barcode height in user units (excluding padding).
     *                                           A negative value indicates the multiplication
     *                                           factor for each row.
     * @param string                    $color   Foreground color in Web notation
     *                                           (color name, or hexadecimal code, or CSS syntax)
     * @param array{int, int, int, int} $padding Additional padding to add around the barcode
     *                                           (top, right, bottom, left) in user units. A
     *                                           negative value indicates the multiplication
     *                                           factor for each row or column.
     *
     * @throws BarcodeException in case of error
     */
    public function getBarcodeObj(
        string $type,
        string $code,
        int $width = -1,
        int $height = -1,
        string $color = 'black',
        array $padding = [0, 0, 0, 0]
    ): Model {
        // extract extra parameters (if any)
        $params = explode(',', $type);
        $type = array_shift($params);

        $bclass = match ($type) {
            'C128' => 'Linear\\CodeOneTwoEight',           // CODE 128
            'C128A' => 'Linear\\CodeOneTwoEight\\CodeOneTwoEightA', // CODE 128 A
            'C128B' => 'Linear\\CodeOneTwoEight\\CodeOneTwoEightB', // CODE 128 B
            'C128C' => 'Linear\\CodeOneTwoEight\\CodeOneTwoEightC', // CODE 128 C
            'C39' => 'Linear\\CodeThreeNine',              // CODE 39 - ANSI MH10.8M-1983 - USD-3 - 3 of 9.
            'C39+' => 'Linear\\CodeThreeNineCheck',        // CODE 39 + CHECKSUM
            'C39E' => 'Linear\\CodeThreeNineExt',          // CODE 39 EXTENDED
            'C39E+' => 'Linear\\CodeThreeNineExtCheck',    // CODE 39 EXTENDED + CHECKSUM
            'C93' => 'Linear\\CodeNineThree',              // CODE 93 - USS-93
            'CODABAR' => 'Linear\\Codabar',                // CODABAR
            'CODE11' => 'Linear\\CodeOneOne',              // CODE 11
            'EAN13' => 'Linear\\EanOneThree',              // EAN 13
            'EAN2' => 'Linear\\EanTwo',                    // EAN 2-Digits UPC-Based Extension
            'EAN5' => 'Linear\\EanFive',                   // EAN 5-Digits UPC-Based Extension
            'EAN8' => 'Linear\\EanEight',                  // EAN 8
            'I25' => 'Linear\\InterleavedTwoOfFive',       // Interleaved 2 of 5
            'I25+' => 'Linear\\InterleavedTwoOfFiveCheck', // Interleaved 2 of 5 + CHECKSUM
            'IMB' => 'Linear\\Imb',                        // IMB - Intelligent Mail Barcode - Onecode - USPS-B-3200
            'IMBPRE' => 'Linear\\ImbPre',                  // IMB - Intelligent Mail Barcode pre-processed
            'KIX' => 'Linear\\KlantIndex',                 // KIX (Klant index - Customer index)
            'LRAW' => 'Linear\\Raw',                       // 1D RAW MODE (comma-separated rows of 01 strings)
            'MSI' => 'Linear\\Msi',                        // MSI (Variation of Plessey code)
            'MSI+' => 'Linear\\MsiCheck',                  // MSI + CHECKSUM (modulo 11)
            'PHARMA' => 'Linear\\Pharma',                  // PHARMACODE
            'PHARMA2T' => 'Linear\\PharmaTwoTracks',       // PHARMACODE TWO-TRACKS
            'PLANET' => 'Linear\\Planet',                  // PLANET
            'POSTNET' => 'Linear\\Postnet',                // POSTNET
            'RMS4CC' => 'Linear\\RoyalMailFourCc',         // RMS4CC (Royal Mail 4-state Customer Bar Code)
            'S25' => 'Linear\\StandardTwoOfFive',          // Standard 2 of 5
            'S25+' => 'Linear\\StandardTwoOfFiveCheck',    // Standard 2 of 5 + CHECKSUM
            'UPCA' => 'Linear\\UpcA',                      // UPC-A
            'UPCE' => 'Linear\\UpcE',                      // UPC-E
            'AZTEC' => 'Square\\Aztec',                    // AZTEC Code (ISO/IEC 24778:2008)
            'DATAMATRIX' => 'Square\\Datamatrix',          // DATAMATRIX (ISO/IEC 16022)
            'PDF417' => 'Square\\PdfFourOneSeven',         // PDF417 (ISO/IEC 15438:2006)
            'QRCODE' => 'Square\\QrCode',                  // QR-CODE
            'SRAW' => 'Square\\Raw',                       // 2D RAW MODE (comma-separated rows of 01 strings)
            default => throw new BarcodeException('Unsupported barcode type: ' . $type)
        };

        $class = '\\Com\\Tecnick\\Barcode\\Type\\' . $bclass;
        /* @phpstan-ignore-next-line */
        return new $class($code, $width, $height, $color, $params, $padding);
    }
}
