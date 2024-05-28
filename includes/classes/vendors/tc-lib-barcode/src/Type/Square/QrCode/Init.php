<?php

/**
 * Init.php
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

use Com\Tecnick\Barcode\Exception as BarcodeException;

/**
 * Com\Tecnick\Barcode\Type\Square\QrCode\Init
 *
 * @since       2015-02-21
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2010-2024 Nicola Asuni - Tecnick.com LTD
 * @license     http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 *
 * @phpstan-type RSItem array{
 *          'alpha_to': array<int, int>,
 *          'fcr': int,
 *          'genpoly': array<int, int>,
 *          'gfpoly': int,
 *          'index_of': array<int, int>,
 *          'iprim': int,
 *          'mm': int,
 *          'nn': int,
 *          'nroots': int,
 *          'pad': int,
 *          'prim': int,
 *      }
 *
 * @phpstan-type RSblock array{
 *          'data': array<int, int>,
 *          'dataLength': int,
 *          'ecc': array<int, int>,
 *          'eccLength': int,
 *      }
 */
abstract class Init extends \Com\Tecnick\Barcode\Type\Square\QrCode\Mask
{
    /**
     * Data code
     *
     * @var array<int, int>
     */
    protected array $datacode = [];

    /**
     * Error correction code
     *
     * @var array<int, int>
     */
    protected array $ecccode = [];

    /**
     * Blocks
     */
    protected int $blocks;

    /**
     * Reed-Solomon blocks
     *
     * @var array<int, RSblock>
     */
    protected array $rsblocks = []; //of RSblock

    /**
     * Counter
     */
    protected int $count;

    /**
     * Data length
     */
    protected int $dataLength;

    /**
     * Error correction length
     */
    protected int $eccLength;

    /**
     * Value bv1
     */
    protected int $bv1;

    /**
     * Width.
     */
    protected int $width;

    /**
     * Frame
     *
     * @var array<int, string>
     */
    protected array $frame = [];

    /**
     * Horizontal bit position
     */
    protected int $xpos;

    /**
     * Vertical bit position
     */
    protected int $ypos;

    /**
     * Direction
     */
    protected int $dir;

    /**
     * Single bit value
     */
    protected int $bit;

    /**
     * Reed-Solomon items
     *
     * @var array<int, RSItem>
     */
    protected array $rsitems = [];

    /**
     * Initialize code
     *
     * @param array<int, int> $spec Array of ECC specification
     */
    protected function init(array $spec): void
    {
        $dlv = $this->spc->rsDataCodes1($spec);
        $elv = $this->spc->rsEccCodes1($spec);
        $rsv = $this->initRs(8, 0x11d, 0, 1, $elv, 255 - $dlv - $elv);
        $blockNo = 0;
        $dataPos = 0;
        $eccPos = 0;
        $ecc = [];
        $endfor = $this->spc->rsBlockNum1($spec);
        $this->initLoop($endfor, $dlv, $elv, $rsv, $eccPos, $blockNo, $dataPos, $ecc);
        if ($this->spc->rsBlockNum2($spec) == 0) {
            return;
        }

        $dlv = $this->spc->rsDataCodes2($spec);
        $elv = $this->spc->rsEccCodes2($spec);
        $rsv = $this->initRs(8, 0x11d, 0, 1, $elv, 255 - $dlv - $elv);
        if ($rsv == null) {
            throw new BarcodeException('Empty RS');
        }

        $endfor = $this->spc->rsBlockNum2($spec);
        $this->initLoop($endfor, $dlv, $elv, $rsv, $eccPos, $blockNo, $dataPos, $ecc);
    }

    /**
     * Internal loop for init
     *
     * @param int   $endfor  End for
     * @param int   $dlv     Data length value
     * @param int   $elv     Error correction length value
     * @param RSItem $rsv Reed-Solomon values
     * @param int   $eccPos  Error correction code position
     * @param int   $blockNo Block number
     * @param int   $dataPos Data position
     * @param array<int, int> $ecc     Error correction code
     */
    protected function initLoop(
        int $endfor,
        int $dlv,
        int $elv,
        array $rsv,
        int &$eccPos,
        int &$blockNo,
        int &$dataPos,
        array &$ecc
    ): void {
        for ($idx = 0; $idx < $endfor; ++$idx) {
            $data = array_slice($this->datacode, $dataPos);
            $ecc = array_slice($this->ecccode, $eccPos);
            $ecc = $this->encodeRsChar($rsv, $data, $ecc);
            $this->rsblocks[$blockNo] = [
                'data' => $data,
                'dataLength' => $dlv,
                'ecc' => $ecc,
                'eccLength' => $elv,
            ];
            $this->ecccode = array_merge(array_slice($this->ecccode, 0, $eccPos), $ecc);
            $dataPos += $dlv;
            $eccPos += $elv;
            ++$blockNo;
        }
    }

    /**
     * Initialize a Reed-Solomon codec and add it to existing rsitems
     *
     * @param int $symsize Symbol size, bits
     * @param int $gfpoly  Field generator polynomial coefficients
     * @param int $fcr     First root of RS code generator polynomial, index form
     * @param int $prim    Primitive element to generate polynomial roots
     * @param int $nroots  RS code generator polynomial degree (number of roots)
     * @param int $pad     Padding bytes at front of shortened block
     *
     * @return RSItem Array of RS values:
     *          mm = Bits per symbol;
     *          nn = Symbols per block;
     *          alpha_to = log lookup table array;
     *          index_of = Antilog lookup table array;
     *          genpoly = Generator polynomial array;
     *          nroots = Number of generator;
     *          roots = number of parity symbols;
     *          fcr = First consecutive root, index form;
     *          prim = Primitive element, index form;
     *          iprim = prim-th root of 1, index form;
     *          pad = Padding bytes in shortened block;
     *          gfpoly.
     */
    protected function initRs(
        int $symsize,
        int $gfpoly,
        int $fcr,
        int $prim,
        int $nroots,
        int $pad
    ): array {
        foreach ($this->rsitems as $rsv) {
            if ($rsv['pad'] != $pad) {
                continue;
            }

            if ($rsv['nroots'] != $nroots) {
                continue;
            }

            if ($rsv['mm'] != $symsize) {
                continue;
            }

            if ($rsv['gfpoly'] != $gfpoly) {
                continue;
            }

            if ($rsv['fcr'] != $fcr) {
                continue;
            }

            if ($rsv['prim'] != $prim) {
                continue;
            }

            return $rsv;
        }

        $rsv = $this->initRsChar($symsize, $gfpoly, $fcr, $prim, $nroots, $pad);
        array_unshift($this->rsitems, $rsv);
        return $rsv;
    }

    /**
     * modnn
     *
     * @param RSItem $rsv  RS values
     * @param int   $xpos X position
     *
     * @return int X position
     */
    protected function modnn(array $rsv, int $xpos): int
    {
        while ($xpos >= $rsv['nn']) {
            $xpos -= $rsv['nn'];
            $xpos = (($xpos >> $rsv['mm']) + ($xpos & $rsv['nn']));
        }

        return $xpos;
    }

    /**
     * Check the params for the initRsChar and throws an exception in case of error.
     *
     * @param int $symsize Symbol size, bits
     * @param int $fcr     First root of RS code generator polynomial, index form
     * @param int $prim    Primitive element to generate polynomial roots
     *
     * @throws BarcodeException in case of error
     */
    protected function checkRsCharParamsA(int $symsize, int $fcr, int $prim): void
    {
        $shfsymsize = (1 << $symsize);
        if (
            ($symsize < 0)
            || ($symsize > 8)
            || ($fcr < 0)
            || ($fcr >= $shfsymsize)
            || ($prim <= 0)
            || ($prim >= $shfsymsize)
        ) {
            throw new BarcodeException('Invalid parameters');
        }
    }

    /**
     * Check the params for the initRsChar and throws an exception in case of error.
     *
     * @param int $symsize Symbol size, bits
     * @param int $nroots  RS code generator polynomial degree (number of roots)
     * @param int $pad     Padding bytes at front of shortened block
     *
     * @throws BarcodeException in case of error
     */
    protected function checkRsCharParamsB(int $symsize, int $nroots, int $pad): void
    {
        $shfsymsize = (1 << $symsize);
        if (
            ($nroots < 0)
            || ($nroots >= $shfsymsize)
            || ($pad < 0)
            || ($pad >= ($shfsymsize - 1 - $nroots))
        ) {
            throw new BarcodeException('Invalid parameters');
        }
    }

    /**
     * Initialize a Reed-Solomon codec and returns an array of values.
     *
     * @param int $symsize Symbol size, bits
     * @param int $gfpoly  Field generator polynomial coefficients
     * @param int $fcr     First root of RS code generator polynomial, index form
     * @param int $prim    Primitive element to generate polynomial roots
     * @param int $nroots  RS code generator polynomial degree (number of roots)
     * @param int $pad     Padding bytes at front of shortened block
     *
     * @return RSItem Array of RS values:
     *          mm = Bits per symbol;
     *          nn = Symbols per block;
     *          alpha_to = log lookup table array;
     *          index_of = Antilog lookup table array;
     *          genpoly = Generator polynomial array;
     *          nroots = Number of generator;
     *          roots = number of parity symbols;
     *          fcr = First consecutive root, index form;
     *          prim = Primitive element, index form;
     *          iprim = prim-th root of 1, index form;
     *          pad = Padding bytes in shortened block;
     *          gfpoly.
     */
    protected function initRsChar(
        int $symsize,
        int $gfpoly,
        int $fcr,
        int $prim,
        int $nroots,
        int $pad
    ): array {
        $this->checkRsCharParamsA($symsize, $fcr, $prim);
        $this->checkRsCharParamsB($symsize, $nroots, $pad);
        $rsv = [];
        $rsv['mm'] = $symsize;
        $rsv['nn'] = ((1 << $symsize) - 1);
        $rsv['pad'] = $pad;
        $rsv['alpha_to'] = array_fill(0, ($rsv['nn'] + 1), 0);
        $rsv['index_of'] = array_fill(0, ($rsv['nn'] + 1), 0);
        // PHP style macro replacement
        $nnv = &$rsv['nn'];
        $azv = &$nnv;
        // Generate Galois field lookup tables
        $rsv['index_of'][0] = $azv; // log(zero) = -inf
        $rsv['alpha_to'][$azv] = 0; // alpha**-inf = 0
        $srv = 1;
        for ($idx = 0; $idx < $rsv['nn']; ++$idx) {
            $rsv['index_of'][$srv] = $idx;
            $rsv['alpha_to'][$idx] = $srv;
            $srv <<= 1;
            if (($srv & (1 << $symsize)) !== 0) {
                $srv ^= $gfpoly;
            }

            $srv &= $rsv['nn'];
        }

        if ($srv != 1) {
            throw new BarcodeException('field generator polynomial is not primitive!');
        }

        // form RS code generator polynomial from its roots
        $rsv['genpoly'] = array_fill(0, ($nroots + 1), 0);
        $rsv['fcr'] = $fcr;
        $rsv['prim'] = $prim;
        $rsv['nroots'] = $nroots;
        $rsv['gfpoly'] = $gfpoly;
        // find prim-th root of 1, used in decoding
        for ($iprim = 1; $iprim % $prim != 0; $iprim += $rsv['nn']) {
            ; // intentional empty-body loop!
        }

        $rsv['iprim'] = (int) ($iprim / $prim);
        $rsv['genpoly'][0] = 1;
        for ($idx = 0, $root = ($fcr * $prim); $idx < $nroots; ++$idx, $root += $prim) {
            $rsv['genpoly'][($idx + 1)] = 1;
            // multiply rs->genpoly[] by  @**(root + x)
            for ($jdx = $idx; $jdx > 0; --$jdx) {
                if ($rsv['genpoly'][$jdx] != 0) {
                    $rsv['genpoly'][$jdx] = ($rsv['genpoly'][($jdx - 1)]
                        ^ $rsv['alpha_to'][$this->modnn($rsv, $rsv['index_of'][$rsv['genpoly'][$jdx]] + $root)]);
                } else {
                    $rsv['genpoly'][$jdx] = $rsv['genpoly'][($jdx - 1)];
                }
            }

            // rs->genpoly[0] can never be zero
            $rsv['genpoly'][0] = $rsv['alpha_to'][$this->modnn($rsv, $rsv['index_of'][$rsv['genpoly'][0]] + $root)];
        }

        // convert rs->genpoly[] to index form for quicker encoding
        for ($idx = 0; $idx <= $nroots; ++$idx) {
            $rsv['genpoly'][$idx] = $rsv['index_of'][$rsv['genpoly'][$idx]];
        }

        return $rsv;
    }

    /**
     * Encode a Reed-Solomon codec and returns the parity array
     *
     * @param RSItem $rsv    RS values
     * @param array<int, int> $data   Data
     * @param array<int, int> $parity Parity
     *
     * @return array<int, int> Parity array
     */
    protected function encodeRsChar(
        array $rsv,
        array $data,
        array $parity
    ): array {
        // the total number of symbols in a RS block
        $nnv = &$rsv['nn'];
        // the address of an array of NN elements to convert Galois field elements
        // in index (log) form to polynomial form
        $alphato = &$rsv['alpha_to'];
        // the address of an array of NN elements to convert Galois field elements
        // in polynomial form to index (log) form
        $indexof = &$rsv['index_of'];
        // an array of NROOTS+1 elements containing the generator polynomial in index form
        $genpoly = &$rsv['genpoly'];
        // the number of roots in the RS code generator polynomial,
        // which is the same as the number of parity symbols in a block
        $nroots = &$rsv['nroots'];
        // the number of pad symbols in a block
        $pad = &$rsv['pad'];
        $azv = &$nnv;
        $parity = array_fill(0, $nroots, 0);
        for ($idx = 0; $idx < ($nnv - $nroots - $pad); ++$idx) {
            $feedback = $indexof[$data[$idx] ^ $parity[0]];
            if ($feedback != $azv) {
                // feedback term is non-zero
                // This line is unnecessary when GENPOLY[NROOTS] is unity, as it must
                // always be for the polynomials constructed by initRs()
                $feedback = $this->modnn($rsv, ($nnv - $genpoly[$nroots] + $feedback));
                for ($jdx = 1; $jdx < $nroots; ++$jdx) {
                    $parity[$jdx] ^= $alphato[$this->modnn($rsv, $feedback + $genpoly[($nroots - $jdx)])];
                }
            }

            // Shift
            array_shift($parity);
            $parity[] = $feedback != $azv ? $alphato[$this->modnn($rsv, $feedback + $genpoly[0])] : 0;
        }

        return $parity;
    }
}
