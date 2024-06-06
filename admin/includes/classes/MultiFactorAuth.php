<?php

/**
 * Handle Multi-Factor authentication via TOTP
 * Compatible with most Authenticator apps and in-browser support.
 *
 * Based on / inspired by: https://github.com/RobThree/TwoFactorAuth
 * Based on / inspired by: https://github.com/PHPGangsta/GoogleAuthenticator
 *
 * Algorithms, digits, period etc. explained: https://github.com/google/google-authenticator/wiki/Key-Uri-Format
 *
 */

// handle autoloaders for 3rd party composer packages
foreach ([
    DIR_FS_CATALOG . DIR_WS_CLASSES . 'vendors/DaspridEnum/autoload.php', // required by BaconQrCode
    DIR_FS_CATALOG . DIR_WS_CLASSES . 'vendors/BaconQrCode/autoload.php', // required by BaconQrCode
//    DIR_FS_CATALOG . DIR_WS_CLASSES . 'vendors/tc-lib-color/autoload.php', // required by TCBarcode
//    DIR_FS_CATALOG . DIR_WS_CLASSES . 'vendors/tc-lib-barcode/autoload.php', // required by TCBarcode
] as $file) {
    if (file_exists($file)) {
        include $file;
    }
}

class MultiFactorAuth
{
    private static string $_base32dict = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567=';

    /** @var array<string> */
    private static array $_base32;

    /** @var array<string, int> */
    private static array $_base32lookup = [];

    public function __construct(
        private int     $codeLength = 6,
        private int     $period = 30,
        private string  $algorithm = 'sha1', // 'sha256', 'sha512'
        private ?string $issuer = null,
        private array   $qrProviderOrder = ['local', 'BaconQrCode', 'QrServerUrl', 'QRickitUrl'], // 'TCBarcode'
        private bool    $prependIssuer = true,
        private string  $encoding = 'utf-8',
    ) {
        if ($this->codeLength <= 0) {
            throw new ValueError('codeLength must be int > 0, usually 6, 7, or 8');
        }

        if ($this->period <= 0) {
            throw new ValueError('Period (seconds) must be int > 0, normally 30, optionally 15 or 60');
        }

        if ($this->issuer !== null) {
            $this->issuer = str_replace(':', '_', $this->issuer);
        }

        self::$_base32 = str_split(self::$_base32dict);
        self::$_base32lookup = array_flip(self::$_base32);
    }

    /**
     * Create a new secret
     * @throws Exception
     */
    public function createSecret(int $bits = 160): string
    {
        $secret = '';
        $bytes = (int)ceil($bits / 5);   // We use 5 bits of each byte (since we have a 32-character 'alphabet' / BASE32)
        $rnd = random_bytes($bytes);
        for ($i = 0; $i < $bytes; $i++) {
            $secret .= self::$_base32[ord($rnd[$i]) & 31];  //Mask out left 3 bits for 0-31 values
        }
        return $secret;
    }

    /**
     * Calculate the code with given secret and point in time
     */
    public function getCode(string $secret, ?int $time = null): string
    {
        $secretkey = $this->base32Decode($secret);

        $timestamp = "\0\0\0\0" . pack('N*', $this->getTimeSlice($time ?? time()));      // Pack time into binary string
        $hashhmac = hash_hmac($this->algorithm, $timestamp, $secretkey, true); // Hash it with users secret key
        $hashpart = substr($hashhmac, ord(substr($hashhmac, -1)) & 0x0F, 4); // Use last nibble of result as index/offset and grab 4 bytes of the result
        $value = unpack('N', $hashpart);                                       // Unpack binary value
        $value = $value[1] & 0x7FFFFFFF;                                              // Drop MSB, keep only 31 bits

        return str_pad((string)($value % 10 ** $this->codeLength), $this->codeLength, '0', STR_PAD_LEFT);
    }

    /**
     * Check if the code is correct. This will accept codes starting from ($discrepancy * $period) sec ago to ($discrepancy * period) sec from now
     */
    public function verifyCode(string $secret, string $code, int $discrepancy = 1, ?int $time = null, ?int &$timeslice = 0): bool
    {
        $timeslice = 0;

        // To keep safe from timing-attacks we iterate *all* possible codes even though we already may have
        // verified a code is correct. We use the timeslice variable to hold either 0 (no match) or the timeslice
        // of the match. Each iteration we either set the timeslice variable to the timeslice of the match
        // or set the value to itself.  This is an effort to maintain constant execution time for the code.
        for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
            $ts = ($time ?? time()) + ($i * $this->period);
            $slice = $this->getTimeSlice($ts);
            $timeslice = hash_equals($this->getCode($secret, $ts), $code) ? $slice : $timeslice;
        }

        return $timeslice > 0;
    }

    /**
     * Set the code length, should be >=6.
     */
    public function setCodeLength(int $length): static
    {
        $this->codeLength = $length;

        return $this;
    }

    public function getCodeLength(): int
    {
        return $this->codeLength;
    }

    private function getTimeSlice(?int $time = null, int $offset = 0): int
    {
        return (int)floor($time / $this->period) + ($offset * $this->period);
    }

    private function base32Decode(string $value): string
    {
        if ($value === '') {
            return '';
        }

        if (preg_match('/[^' . preg_quote(self::$_base32dict, '/') . ']/', $value) !== 0) {
            throw new ValueError('Invalid base32 string');
        }

        $buffer = '';
        foreach (str_split($value) as $char) {
            if ($char !== '=') {
                $buffer .= str_pad(decbin(self::$_base32lookup[$char]), 5, '0', STR_PAD_LEFT);
            }
        }
        $length = strlen($buffer);
        $blocks = trim(chunk_split(substr($buffer, 0, $length - ($length % 8)), 8, ' '));

        $output = '';
        foreach (explode(' ', $blocks) as $block) {
            $output .= chr(bindec(str_pad($block, 8, '0', STR_PAD_RIGHT)));
        }
        return $output;
    }

    /**
     * Builds a string to be encoded in a QR code
     */
    protected function getQRText(string $domain, string $secret, string $accountname = ''): string
    {
        if ($domain !== '') {
            $domain = str_replace(':', '_', $domain);
        }

        $issuer = $this->issuer ?? $domain;

        if ($accountname !== '') {
            $accountname = str_replace(':', '_', $accountname);
        }

        $label = $accountname;

        if ($this->prependIssuer && !empty($issuer)) {
            $label = $issuer . ':' . $label;
        }

        return 'otpauth://totp/' . rawurlencode($label)
            . '?secret=' . rawurlencode($secret)
            . '&issuer=' . rawurlencode($issuer)
            . '&period=' . $this->period
            . '&algorithm=' . rawurlencode(strtoupper($this->algorithm))
            . '&digits=' . $this->codeLength;
    }

    /**
     * Get QR-Code URL for image from QRserver.com.
     * See https://goqr.me/api/doc/create-qr-code/
     */
    public function getQrCodeQrServerUrl(string $data, int $size = 200): string
    {
        $queryParameters = [
            'size' => $size . 'x' . $size,
            'ecc' => 'L',
            'margin' => 4,
            'qzone' => 1,
            'format' => 'png', // 'svg'
            'data' => $data,
        ];

        return 'https://api.qrserver.com/v1/create-qr-code/?' . http_build_query($queryParameters);
    }

    /**
     * See http://qrickit.com/qrickit_apps/qrickit_api.php
     */
    public function getQrCodeQRickitUrl(string $data, int $size = 200): string
    {
        $queryParameters = [
            'qrsize' => $size,
            'e' => 'l',
            'bgdcolor' => 'ffffff',
            'fgdcolor' => '000000',
            't' => 'p', // png
            'd' => $data,
        ];

        return 'https://qrickit.com/api/qr?' . http_build_query($queryParameters);
    }

    /**
     * See https://github.com/Bacon/BaconQrCode
     *
     * Using SVG mode because it is not dependent on Imagemagick (but is dependent on XMLWriter)
     */
    public function getQrCodeBaconQrCode(string $data, int $size = 200): string
    {
        $renderer = new BaconQrCode\Renderer\ImageRenderer((new BaconQrCode\Renderer\RendererStyle\RendererStyle($size))->withSize($size), new BaconQrCode\Renderer\Image\SvgImageBackEnd());
        $writer = new BaconQrCode\Writer($renderer);

        return $writer->writeString($data, $this->encoding ?? 'utf-8');
    }

    /**
     * See https://github.com/tecnickcom/tc-lib-barcode
     * (To add this library, must create an autoloader.php for it to register with, and also include tc-lib-color)
     */
    public function getQrCodeTCBarcode(string $data, int $size = 200): string
    {
        $barcode = new \Com\Tecnick\Barcode\Barcode();

        $qrCode = $barcode->getBarcodeObj(
            'QRCODE,L',
            $data, // data string to encode
            $size,
            $size,
            'black',  // foreground color
            [2,2,2,2] // padding (use absolute or negative values as multiplication factors)
        )->setBackgroundColor('white'); // background color

        if (function_exists('imagecreate')) {
            return 'data:image/png;base64,' . base64_encode($qrCode->getPngData(true));
        }

        return $qrCode->getSvgCode(); // returns SVG as SVG markup, safe to render directly as HTML

        //return $qrCode->getHtmlDiv(); // returns a DIV containing multiple small rectangles for QR code, safe to render as HTML; however, QR Code is not as well recognized by in-browser scanners
    }

    public function getQrCode(string $domain, string $secret, string $accountname = '', int $size = 200): string
    {
        $data = $this->getQRText($domain, $secret, $accountname);

        $qr = '';
        foreach ($this->qrProviderOrder as $provider) {
            if ($provider === 'local' || $provider === 'BaconQrCode') {
                if (class_exists('\BaconQrCode\Encoder\QrCode')) {
                    $qr = $this->getQrCodeBaconQrCode($data, $size);
                }
                if (!empty($qr)) {
                    return $qr;
                }
            }

            if ($provider === 'local' || $provider === 'TCBarcode') {
                if (class_exists('\Com\Tecnick\Barcode\Barcode')) {
                    $qr = $this->getQrCodeTCBarcode($data, $size);
                }
                if (!empty($qr)) {
                    return $qr;
                }
            }

            if ($provider === 'QrServerUrl') {
                $qr = $this->getQrCodeQRserverUrl($data, $size);
            }

            if ($provider === 'QRickitUrl') {
                $qr = $this->getQrCodeQRickitUrl($data, $size);
            }

            if (!empty($qr)) {
                return $qr;
            }
        }

        return $qr;
    }
}
