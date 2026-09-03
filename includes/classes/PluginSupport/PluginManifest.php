<?php
declare(strict_types=1);

/**
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2025 Sep 27 New in v3.0.0 $
 *
 * @since ZC v3.0.0
 */
namespace Zencart\PluginSupport;

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal access');
}

class PluginManifest
{
    protected array $manifest_info;
    protected string $pluginsRoot;  //- Note: No ending DIRECTORY_SEPARATOR!

    /**
     * @since ZC v3.0.0
     */
    public function __construct(?string $pluginsRoot = null)
    {
        if ($pluginsRoot === null) {
            $pluginsRoot = DIR_FS_CATALOG . 'zc_plugins';
        }

        $this->pluginsRoot = rtrim($pluginsRoot, '/\\');
    }

    /**
     * Returns the array of information returned by the requested plugin
     * key/version's manifest.php. Returns `null` if no such file is found for
     * the plugin or the returned value isn't an array.
     *
     * @since ZC v3.0.0
     */
    public function get(string $plugin_key, string $version): ?array
    {
        if (isset($this->manifest_info[$plugin_key][$version])) {
            return $this->manifest_info[$plugin_key][$version]['contents'];
        }

        $manifest_filename = $this->exists($plugin_key, $version);
        if ($manifest_filename === null) {
            return null;
        }

        $manifest = require $manifest_filename;
        if (!is_array($manifest)) {
            return null;
        }

        $this->manifest_info[$plugin_key][$version] = [
            'contents' => $manifest,
            'template_key' => $manifest['template']['key'] ?? null,
            'removes_uncapsulated_version' => !empty($manifest['removesUnencapsulatedVersion']),
        ];

        return $manifest;
    }

    /**
     * @since ZC v3.0.0
     */
    public function exists(string $plugin_key, string $version): ?string
    {
        $manifest_filename = $this->pluginsRoot . "/$plugin_key/$version/manifest.php";
        if (isset($this->manifest_info[$plugin_key][$version]) || is_file($manifest_filename)) {
            return $manifest_filename;
        }
        return null;
    }

    /**
     * @since ZC v3.0.0
     */
    public function isSelectableTemplate(string $plugin_key, string $version): bool
    {
        if ($this->get($plugin_key, $version) === null) {
            return false;
        }
        return $this->manifest_info[$plugin_key][$version]['template_key'] !== null;
    }

    /**
     * @since ZC v3.0.0
     */
    public function removesUnencapsulatedVersion(string $plugin_key, string $version): bool
    {
        if ($this->get($plugin_key, $version) === null) {
            return false;
        }
        return $this->manifest_info[$plugin_key][$version]['removes_uncapsulated_version'];
    }

    /**
     * @since ZC v3.0.0
     */
    public function getSelectableTemplateKey(string $plugin_key, string $version): ?string
    {
        if ($this->get($plugin_key, $version) === null) {
            return null;
        }
        return $this->manifest_info[$plugin_key][$version]['template_key'];
    }
}
