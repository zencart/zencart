<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Zcwilt 2023 Nov 16 Modified in v2.0.0-alpha1 $
 */

class VersionServer
{
    protected string $projectVersionServer = 'https://ping.zen-cart.com/zcversioncheck';
    protected string $pluginVersionServer = 'https://ping.zen-cart.com/plugincheck';

    protected const TIMEOUT = 3;

    protected const CONNECTTIMEOUT = 2;

    public function __construct()
    {
        if (defined('PROJECT_VERSIONSERVER_URL')) {
            $this->projectVersionServer = PROJECT_VERSIONSERVER_URL;
        }
        if (defined('PLUGIN_VERSIONSERVER_URL')) {
            $this->pluginVersionServer = PLUGIN_VERSIONSERVER_URL;
        }
    }

    public function getZcVersioninfo(): array
    {
        return $this->buildCurrentInfo();
    }

    public function getProjectVersion(): mixed
    {
        $currentInfo = $this->getZcVersioninfo();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->projectVersionServer);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($currentInfo));
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, self::TIMEOUT);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::CONNECTTIMEOUT);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Core Version Check ' . HTTP_SERVER);
        $response = curl_exec($ch);
        $error = curl_error($ch);
        $errno = curl_errno($ch);
        if ($errno > 0) {
            return json_decode($this->formatCurlError($errno, $error), true);
        }
        return json_decode($response, true);
    }

    /**
     * @param int|string $ids An integer or a comma-separated string of integers denoting the plugin ID from the ZC plugin library
     * @return bool|false|string json string
     */
    public function getPluginVersion($ids): bool|string
    {
        $keylist = implode(',', array_map(static fn($value) => (int)trim($value), explode(',', $ids)));
        $type = '[' . (int)$ids . ']';
        if (strpos($ids, ',') > 0) {
            $type = '[Batch]';
        }

        $currentInfo = $this->getZcVersioninfo();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->pluginVersionServer . '/' . $keylist);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($currentInfo));
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, self::TIMEOUT);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::CONNECTTIMEOUT);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Plugin Version Check ' . $type . ' ' . HTTP_SERVER);
        $response = curl_exec($ch);
        $error = curl_error($ch);
        $errno = curl_errno($ch);
        if ($errno > 0) {
            return $this->formatCurlError($errno, $error);
        }
        return $response;
    }

    public function isProjectCurrent(?array $newVersionInfo = null): bool
    {
        if (empty($newVersionInfo)) {
            $newVersionInfo = $this->getProjectVersion();
        }

        if (trim($newVersionInfo['versionMajor'] ?? 0) > PROJECT_VERSION_MAJOR) {
            return false;
        }

        if ((int)trim($newVersionInfo['versionMajor'] ?? 0) === (int)PROJECT_VERSION_MAJOR && trim($newVersionInfo['versionMinor'] ?? 0) > PROJECT_VERSION_MINOR) {
            return false;
        }

        return true;
    }

    public function hasProjectPatches(?array $newVersionInfo = null): int
    {
        if (empty($newVersionInfo)) {
            $newVersionInfo = $this->getProjectVersion();
        }

        $result = 0;
        if (isset($newVersionInfo['versionPatch1']) && trim($newVersionInfo['versionPatch1'] ?? 0) > (int)PROJECT_VERSION_PATCH1) {
            $result++;
        }
        if (isset($newVersionInfo['versionPatch2']) && trim($newVersionInfo['versionPatch2'] ?? 0) > (int)PROJECT_VERSION_PATCH2) {
            $result++;
            $result++;
        }
        return $result;
    }

    protected function buildCurrentInfo(): array
    {
        $systemInfo = json_encode(zen_get_system_information(true));

        $moduleInfo = json_encode($this->getModuleInfo());

        $country_iso = $this->getCountryIso();

        $results = [
            'currentVersionMajor' => PROJECT_VERSION_MAJOR,
            'currentVersionMinor' => PROJECT_VERSION_MINOR,
            'httpServer' => HTTP_SERVER,
            'httpsServer' => HTTPS_SERVER,
            'storeCountry' => $country_iso,
            'systemInfo' => $systemInfo,
            'moduleInfo' => $moduleInfo,
        ];
        return $results;
    }

    protected function getModuleinfo(): array
    {
        $modules = [
            'MODULE_PAYMENT_INSTALLED' => MODULE_PAYMENT_INSTALLED,
            'MODULE_SHIPPING_INSTALLED' => MODULE_SHIPPING_INSTALLED,
            'MODULE_ORDER_TOTAL_INSTALLED' => MODULE_ORDER_TOTAL_INSTALLED,
        ];

        return $modules;
    }

    protected function getCountryIso()
    {
        global $db;
        $result = $db->Execute('SELECT countries_iso_code_3 FROM ' . TABLE_COUNTRIES . ' WHERE countries_id = ' . (int)STORE_COUNTRY);
        if ($result->RecordCount()) {
            return $result->fields['countries_iso_code_3'];
        }

        return '';
    }

    protected function formatCurlError($errorno, $error): bool|string
    {
        return json_encode(['error' => $error . '[' . $errorno . ']']);
    }
}
