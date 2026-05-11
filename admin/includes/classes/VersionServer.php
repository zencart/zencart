<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2025 Dec 01 Modified in v2.2.1 $
 * @since ZC v1.5.5f
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

    /**
     * @since ZC v1.5.5f
     */
    public function getZcVersioninfo(): array
    {
        return $this->buildCurrentInfo();
    }

    /**
     * @since ZC v1.5.5f
     */
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
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 2);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Core Version Check ' . HTTP_SERVER);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $errno = curl_errno($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($errno > 0 || $response === false || $http_code > 299) {
            return json_decode($this->formatCurlError($errno, $error), true);
        }

        return json_decode($response, true) ?? [];
    }

    /**
     * @param int|string|null $ids An integer or a comma-separated string of integers denoting the plugin ID from the ZC plugin library
     * @return bool|false|string json string
     * @since ZC v1.5.5f
     */
    public function getPluginVersion(mixed $ids): bool|string
    {
        if (empty($ids)) {
            return false;
        }

        $ids = (string)$ids;
        $keylist = implode(',', array_map(static fn($value) => (int)trim($value), explode(',', $ids)));

        $type = '[' . (int)$ids . ']';
        if (str_contains($ids, ',')) {
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
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 2);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Plugin Version Check ' . $type . ' ' . HTTP_SERVER);
        $response = curl_exec($ch);
        $error = curl_error($ch);
        $errno = curl_errno($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($errno > 0 || $response === false || $http_code > 299) {
            return $this->formatCurlError($errno, $error);
        }
        return $response;
    }

    /**
     * This method checks the major and minor version numbers to determine if the project is current.
     *
     * Since v2.0.0, Zen Cart follows semantic versioning.
     * But the version number is split across 2 constants: PROJECT_VERSION_MAJOR and PROJECT_VERSION_MINOR.
     * PROJECT_VERSION_MAJOR is always the first digit(s) of the version number.
     * PROJECT_VERSION_MINOR is the second digit(s) of the version number.
     * The 2 segments must always be paired together for thorough comparison.
     *
     * @since ZC v1.5.5f
     */
    public function isProjectCurrent(?array $newVersionInfo = null): bool
    {
        if (empty($newVersionInfo)) {
            $newVersionInfo = $this->getProjectVersion();
        }

        // If major version is higher on the server than the present major version, then this site is not current. So return false immediately.
        if (trim($newVersionInfo['versionMajor'] ?? 0) > PROJECT_VERSION_MAJOR) {
            return false;
        }

        // Note: If the formula used here for `PROJECT_VERSION_MAJOR . '.' . PROJECT_VERSION_MINOR` changes, be sure to also update zen_get_zcversion() in functions_general_shared.php.

        // Now use version_compare for a more thorough comparison with major/minor, including semantic-versioning support for pre-release identifiers such as -dev, -alpha, -beta, -rc, -pl.
        $currentVersion = PROJECT_VERSION_MAJOR . '.' . PROJECT_VERSION_MINOR;
        $newVersion = trim($newVersionInfo['versionMajor'] ?? 0) . '.' . trim($newVersionInfo['versionMinor'] ?? 0);
        if (version_compare($newVersion, $currentVersion, '>')) {
            return false;
        }

        return true;
    }

    /**
     * Deprecated.
     * Note: Since semantic versioning already accommodates "pl" or "p" as a patch-level indicator, we no longer use the patch-level constants (PROJECT_VERSION_PATCH1 and PROJECT_VERSION_PATCH2).
     *
     * @deprecated in v3.0.0. This method is no longer used since v2.0.0, and will be removed in a future major release.
     * @since was added to this class in ZC v1.5.5f
     */
    public function hasProjectPatches(?array $newVersionInfo = null): int
    {
        return 0;
    }

    /**
     * @since ZC v1.5.5f
     */
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

    /**
     * @since ZC v1.5.5f
     */
    protected function getModuleinfo(): array
    {
        $modules = [
            'MODULE_PAYMENT_INSTALLED' => MODULE_PAYMENT_INSTALLED,
            'MODULE_SHIPPING_INSTALLED' => MODULE_SHIPPING_INSTALLED,
            'MODULE_ORDER_TOTAL_INSTALLED' => MODULE_ORDER_TOTAL_INSTALLED,
        ];

        return $modules;
    }

    /**
     * @since ZC v1.5.5f
     */
    protected function getCountryIso()
    {
        global $db;
        $result = $db->Execute('SELECT countries_iso_code_3 FROM ' . TABLE_COUNTRIES . ' WHERE countries_id = ' . (int)STORE_COUNTRY);
        if ($result->RecordCount()) {
            return $result->fields['countries_iso_code_3'] ?? '';
        }

        return '';
    }

    /**
     * @since ZC v1.5.5f
     */
    protected function formatCurlError($errorno, $error): bool|string
    {
        return json_encode(['error' => $error . '[' . $errorno . ']']);
    }
}
