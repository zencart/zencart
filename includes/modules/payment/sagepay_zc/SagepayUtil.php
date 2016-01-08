<?php
/**
 * sagepay form
 *
 * @package paymentMethod
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright portions (c) 2013, Sage Pay Europe Ltd.
 * @copyright Portions Copyright Nixak
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Author: Wilt New in v1.5.5 $
 */

/**
 * Class SagepayUtil
 */
class SagepayUtil
{
    /**
     * @param $url
     * @param $data
     * @param int $ttl
     * @param string $caCertPath
     * @return array
     */
    static public function requestPost($url, $data, $ttl = 30, $caCertPath = '')
    {
        set_time_limit(60);
        $output = array();
        $curlSession = curl_init();

        curl_setopt($curlSession, CURLOPT_URL, $url);
        curl_setopt($curlSession, CURLOPT_HEADER, 0);
        curl_setopt($curlSession, CURLOPT_POST, 1);
        curl_setopt($curlSession, CURLOPT_POSTFIELDS, SagepayUtil::arrayToQueryString($data, '&', true));
        curl_setopt($curlSession, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlSession, CURLOPT_TIMEOUT, $ttl);
        curl_setopt($curlSession, CURLOPT_SSL_VERIFYHOST, 2);

        if (!empty($caCertPath)) {
            curl_setopt($curlSession, CURLOPT_SSL_VERIFYPEER, 1);
            curl_setopt($curlSession, CURLOPT_CAINFO, $caCertPath);
        } else {
            curl_setopt($curlSession, CURLOPT_SSL_VERIFYPEER, 0);
        }

        $rawresponse = curl_exec($curlSession);

        if (curl_getinfo($curlSession, CURLINFO_HTTP_CODE) !== 200) {
            $output['Status'] = "FAIL";
            $output['StatusDetails'] = "Server Response: " . curl_getinfo($curlSession, CURLINFO_HTTP_CODE);
            $output['Response'] = $rawresponse;

            return $output;
        }
        if (curl_error($curlSession)) {
            $output['Status'] = "FAIL";
            $output['StatusDetail'] = curl_error($curlSession);
            $output['Response'] = $rawresponse;
            return $output;
        }
        curl_close($curlSession);
        $response = SagepayUtil::queryStringToArray($rawresponse, "\r\n");
        return array_merge($output, $response);
    }

    /**
     * @return array
     */
    public static function getUsStateCodes()
    {
        $usStateCodes = array(
            'Alabama' => 'AL',
            'Alaska' => 'AK',
            'American Samoa' => 'AS',
            'Arizona' => 'AZ',
            'Arkansas' => 'AR',
            'Armed Forces Africa' => 'AF',
            'Armed Forces Americas' => 'AA',
            'Armed Forces Canada' => 'AC',
            'Armed Forces Europe' => 'AE',
            'Armed Forces Middle East' => 'AM',
            'Armed Forces Pacific' => 'AP',
            'California' => 'CA',
            'Colorado' => 'CO',
            'Connecticut' => 'CT',
            'Delaware' => 'DE',
            'District of Columbia' => 'DC',
            'Federated States Of Micronesia' => 'FM',
            'Florida' => 'FL',
            'Georgia' => 'GA',
            'Guam' => 'GU',
            'Hawaii' => 'HI',
            'Idaho' => 'ID',
            'Illinois' => 'IL',
            'Indiana' => 'IN',
            'Iowa' => 'IA',
            'Kansas' => 'KS',
            'Kentucky' => 'KY',
            'Louisiana' => 'LA',
            'Maine' => 'ME',
            'Marshall Islands' => 'MH',
            'Maryland' => 'MD',
            'Massachusetts' => 'MA',
            'Michigan' => 'MI',
            'Minnesota' => 'MN',
            'Mississippi' => 'MS',
            'Missouri' => 'MO',
            'Montana' => 'MT',
            'Nebraska' => 'NE',
            'Nevada' => 'NV',
            'New Hampshire' => 'NH',
            'New Jersey' => 'NJ',
            'New Mexico' => 'NM',
            'New York' => 'NY',
            'North Carolina' => 'NC',
            'North Dakota' => 'ND',
            'Northern Mariana Islands' => 'MP',
            'Ohio' => 'OH',
            'Oklahoma' => 'OK',
            'Oregon' => 'OR',
            'Pennsylvania' => 'PA',
            'Puerto Rico' => 'PR',
            'Rhode Island' => 'RI',
            'South Carolina' => 'SC',
            'South Dakota' => 'SD',
            'Tennessee' => 'TN',
            'Texas' => 'TX',
            'Utah' => 'UT',
            'Vermont' => 'VT',
            'Virgin Islands' => 'VI',
            'Virginia' => 'VA',
            'Washington' => 'WA',
            'West Virginia' => 'WV',
            'Wisconsin' => 'WI',
            'Wyoming' => 'WY'
        );
        return $usStateCodes;
    }

    /**
     * @param $strIn
     * @param $strEncryptionPassword
     * @return string
     */
    public static function encryptAndEncode($strIn, $strEncryptionPassword)
    {
        $strIV = $strEncryptionPassword;
        $strIn = self::addPKCS5Padding($strIn);
        $strCrypt = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $strEncryptionPassword, $strIn, MCRYPT_MODE_CBC, $strIV);
        return "@" . bin2hex($strCrypt);
    }

    /**
     * @param $input
     * @return string
     */
    public static function addPKCS5Padding($input)
    {
        $blocksize = 16;
        $padding = "";
        $padlength = $blocksize - (strlen($input) % $blocksize);
        for ($i = 1; $i <= $padlength; $i++) {
            $padding .= chr($padlength);
        }
        return $input . $padding;
    }

    /**
     * @param $strIn
     * @param $strEncryptionPassword
     * @return string
     */
    public static function decodeAndDecrypt($strIn, $strEncryptionPassword)
    {
        if (substr($strIn, 0, 1) == "@") {
            $strIV = $strEncryptionPassword;
            $strIn = substr($strIn, 1);
            $strIn = pack('H*', $strIn);
            return mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $strEncryptionPassword, $strIn, MCRYPT_MODE_CBC, $strIV);
        } else {
            echo "Error: #69053";
        }
    }

    /**
     * @param $cryptEntries
     * @return string
     */
    public static function processCryptEntries($cryptEntries)
    {
        $plain = '';
        foreach ($cryptEntries as $key => $value) {
            $plain .= $key . '=' . $value . '&';
        }
        $plain = rtrim($plain, "&");
        return $plain;
    }

    /**
     * @param $query_string
     * @return array
     */
    public static function getResponseTokens($query_string)
    {
        $result = array();
        parse_str($query_string, $result);
        return $result;
    }

    /**
     * @param array $data
     * @param string $delimiter
     * @param bool|false $urlencoded
     * @return string
     */
    static public function arrayToQueryString(array $data, $delimiter = '&', $urlencoded = false)
    {
        $queryString = '';
        $delimiterLength = strlen($delimiter);

        // Parse each value pairs and concate to query string
        foreach ($data as $name => $value) {
            // Apply urlencode if it is required
            if ($urlencoded) {
                $value = urlencode($value);
            }
            $queryString .= $name . '=' . $value . $delimiter;
        }

        // remove the last delimiter
        return substr($queryString, 0, -1 * $delimiterLength);
    }

    /**
     * @param $data
     * @param string $delimeter
     * @return array
     */
    static public function queryStringToArray($data, $delimeter = "&")
    {
        // Explode query by delimiter
        $pairs = explode($delimeter, $data);
        $queryArray = array();

        // Explode pairs by "="
        foreach ($pairs as $pair) {
            $keyValue = explode('=', $pair);

            // Use first value as key
            $key = array_shift($keyValue);

            // Implode others as value for $key
            $queryArray[$key] = implode('=', $keyValue);
        }
        return $queryArray;
    }
}
