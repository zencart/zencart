<?php
/**
 * Common error-information class for the PayPalRestful (paypalr) Payment Module
 *
 * @copyright Copyright 2023-2024 Zen Cart Development Team
 * @license https://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2023 Nov 16 Modified in v2.0.0 $
 *
 * Last updated: v1.3.0
 */
namespace PayPalRestful\Common;

class ErrorInfo
{
    /**
     * Error information, when a CURL or RESTful error occurs.
     */
    protected $errorInfo;

    public function __construct()
    {
        $this->resetErrorInfo();
    }

    public function getErrorInfo(): array
    {
        return $this->errorInfo;
    }

    public function copyErrorInfo(array $error_info)
    {
        $this->errorInfo = $error_info;
    }

    protected function setErrorInfo(int $errNum, string $errMsg, int $curlErrno = 0, $response = [])
    {
        $name = $response['name'] ?? 'n/a';
        $message = $response['message'] ?? 'n/a';
        $details = $response['details'] ?? 'n/a';
        $debug_id = $response['debug_id'] ?? 'n/a';
        $this->errorInfo = compact('errNum', 'errMsg', 'curlErrno', 'name', 'message', 'details', 'debug_id');
    }

    protected function resetErrorInfo()
    {
        $this->errorInfo = [
            'errMsg' => '',
            'errNum' => 0,
            'curlErrno' => 0,
            'name' => '',
            'message' => '',
            'details' => [],
            'debug_id' => '',
        ];
    }
}
