<?php

declare(strict_types=1);
/**
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2025 Sep 18 Modified in v2.2.0 $
 */

namespace Zencart\Templates;

use Zencart\FileSystem\FileSystem;
use Zencart\PluginManager\PluginManager;
use Zencart\PluginSupport\PluginStatus;

if (!defined('IS_ADMIN_FLAG') || IS_ADMIN_FLAG !== true) {
    die('Illegal Access');
}

/**
 * A class that provides helper methods to the admin's "Template Select" tool, aiding
 * in the creation of child-templates.
 *
 * @since ZC v3.0.0
 */
class TemplateChild
{
    public const int ERROR_NONE = 0;
    public const int ERROR_EMPTY = 1;
    public const int ERROR_INVALID_SUBDIR = 2;
    public const int ERROR_DUPLICATE_SUBDIR = 3;
    public const int ERROR_NO_PARENT = 4;
    public const int ERROR_CHILD_VALUES_INVALID = 5;
    public const int ERROR_VALUE_TOO_LONG = 6;
    public const int ERROR_CREATING_DIRS = 7;
    public const int ERROR_NO_TEMPLATE_INFO = 8;
    public const int ERROR_CREATING_SCREENSHOT = 9;
    public const int ERROR_CREATING_TEMPLATE_SETTINGS = 10;
    public const int ERROR_CREATING_MANIFEST = 11;

    private string $parentTemplateDirectory;

    private string $templateName = '';
    private bool $templateNameValid = false;

    private string $templateKey = '';
    private bool $templateKeyValid = false;
    private int $templateKeyMaxLength;

    private string $templateDirectory = '';
    private bool $templateDirectoryValid = false;
    private int $templateDirectoryMaxLength;

    private string $pluginName = '';
    private bool $pluginNameValid = false;
    private int $pluginNameMaxLength;

    private TemplateSelect $templateSelect;
    private ?array $parentTemplateInfo;
    private array $allPlugins;
    private array $templateInfo;
    private FileSystem $fs;

    public function __construct(PluginManager $pluginManager, string $parent_template_directory)
    {
        $this->parentTemplateDirectory = $parent_template_directory;

        $this->templateSelect = new TemplateSelect();
        $this->templateInfo = $this->templateSelect->getSelectableTemplates();
        $this->parentTemplateInfo = $this->templateInfo[$parent_template_directory] ?? null;

        $this->allPlugins = $pluginManager->getAllPlugins();

        $this->fs = new FileSystem();

        $this->templateKeyMaxLength = $pluginManager->getPluginKeyMaxLength();
        $this->pluginNameMaxLength = $pluginManager->getPluginNameMaxLength();
        $this->templateDirectoryMaxLength = zen_field_length(TABLE_TEMPLATE_SELECT, 'template_dir');
    }

    /**
     * Determine whether the current parent-template is capable of supporting
     * children. The template must be a first-generation encapsulated "template package",
     * i.e. its 'base' template is 'template_default'.
     *
     * @since ZC v3.0.0
     */
    public function canCreate(): bool
    {
        if ($this->parentTemplateInfo === null) {
            return false;
        }
        return $this->parentTemplateInfo['is_plugin_template'] === true && $this->parentTemplateInfo['base_template'] === 'template_default';
    }

    /**
     * @since ZC v3.0.0
     */
    public function setChildInfo(string $template_name, string $template_key, string $plugin_name, string $template_directory): void
    {
        $this->templateName = $template_name;
        $this->templateNameValid = false;
        $this->templateKey = $template_key;
        $this->templateKeyValid = false;
        $this->pluginName = $plugin_name;
        $this->pluginNameValid = false;
        $this->templateDirectory = $template_directory;
        $this->templateDirectoryValid = false;
    }

    /**
     * @since ZC v3.0.0
     */
    protected function isTemplateKeyUnique(string $template_key): bool
    {
        return !array_key_exists($template_key, $this->allPlugins);
    }

    /**
     * @since ZC v3.0.0
     */
    public function getTemplateKeyMaxLength(): int
    {
        return $this->templateKeyMaxLength;
    }

    /**
     * @since ZC v3.0.0
     */
    protected function isTemplateDirectoryUnique(string $template_directory): bool
    {
        return !array_key_exists($template_directory, $this->templateInfo);
    }

    /**
     * @since ZC v3.0.0
     */
    public function getTemplateDirectoryMaxLength(): int
    {
        return $this->templateDirectoryMaxLength;
    }

    /**
     * @since ZC v3.0.0
     */
    public function getPluginNameMaxLength(): int
    {
        return $this->pluginNameMaxLength;
    }

    /**
     * @since ZC v3.0.0
     */
    public function validateTemplateKey(): int
    {
        $rc = $this->checkForEmpty($this->templateKey);
        if ($rc !== self::ERROR_NONE) {
            return $rc;
        }
        
        $rc = $this->validateSubDirectoryName($this->templateKey);
        if ($rc !== self::ERROR_NONE) {
            return $rc;
        }

        if ($this->isTemplateKeyUnique($this->templateKey) === false) {
            return self::ERROR_DUPLICATE_SUBDIR;
        }

        if (mb_strlen($this->templateKey) > $this->templateKeyMaxLength) {
            return self::ERROR_VALUE_TOO_LONG;
        }

        $this->templateKeyValid = true;
        return self::ERROR_NONE;
    }

    /**
     * @since ZC v3.0.0
     */
    public function validateTemplateName(): int
    {
        $rc = $this->checkForEmpty($this->templateName);
        if ($rc !== self::ERROR_NONE) {
            return $rc;
        }
        $this->templateNameValid = true;
        return self::ERROR_NONE;
    }

    /**
     * @since ZC v3.0.0
     */
    public function validateTemplateDirectory(): int
    {
        $rc = $this->checkForEmpty($this->templateDirectory);
        if ($rc !== self::ERROR_NONE) {
            return $rc;
        }

        $rc = $this->validateSubDirectoryName($this->templateDirectory);
        if ($rc !== self::ERROR_NONE) {
            return $rc;
        }

        if ($this->isTemplateDirectoryUnique($this->templateDirectory) === false) {
            return self::ERROR_DUPLICATE_SUBDIR;
        }

        if (mb_strlen($this->templateDirectory) > $this->templateDirectoryMaxLength) {
            return self::ERROR_VALUE_TOO_LONG;
        }

        $this->templateDirectoryValid = true;
        return self::ERROR_NONE;
    }

    /**
     * @since ZC v3.0.0
     */
    public function validatePluginName(): int
    {
        $rc = $this->checkForEmpty($this->pluginName);
        if ($rc !== self::ERROR_NONE) {
            return $rc;
        }

        if (mb_strlen($this->pluginName) > $this->pluginNameMaxLength) {
            return self::ERROR_VALUE_TOO_LONG;
        }

        $this->pluginNameValid = true;
        return self::ERROR_NONE;
    }

    /**
     * @since ZC v3.0.0
     */
    protected function checkForEmpty(string $value): int
    {
        return $value === '' ? self::ERROR_EMPTY : self::ERROR_NONE;
    }

    /**
     * @since ZC v3.0.0
     */
    protected function validateSubDirectoryName(string $value): int
    {
        return !$this->fs->validateSubDirectoryName($value) ? self::ERROR_INVALID_SUBDIR : self::ERROR_NONE;
    }

    /**
     * @since ZC v3.0.0
     */
    public function createChildTemplate(): int
    {
        /**
         * Note: The return of either of these errorcodes implies a sequencing error by the
         * instantiating script.
         */
        if ($this->parentTemplateInfo === null) {
            return self::ERROR_NO_PARENT;
        }
        if ($this->templateKeyValid === false || $this->templateNameValid === false || $this->templateDirectoryValid === false || $this->pluginNameValid === false) {
            return self::ERROR_CHILD_VALUES_INVALID;
        }

        /**
         * Create the child template's directory structure, copying empty subdirectories
         * that mimic the structure of its parent.
         */
        $parent_template_root = DIR_FS_CATALOG . 'zc_plugins/' . $this->parentTemplateInfo['plugin_key'] . '/' . $this->parentTemplateInfo['plugin_version'] . '/catalog/includes';
        $child_template_root = DIR_FS_CATALOG . 'zc_plugins/' . $this->templateKey . '/v1.0.0/catalog/includes';
        if ($this->createChildTemplateDirectories($parent_template_root, $child_template_root) === false) {
            return self::ERROR_CREATING_DIRS;
        }

        /**
         * Create the (modified) template_info.php file for the child template; the method sets
         * the parent template's screenshot file's name.
         */
        $template_screenshot = '';  //- Note updated by the following method, if successful.
        if ($this->createChildTemplateInfo($child_template_root, $template_screenshot) === false) {
            return self::ERROR_NO_TEMPLATE_INFO;
        }

        /**
         * If the parent template has a screenshot image, the child template uses a copy of that image.
         */
        if ($template_screenshot !== '') {
            $parent_template_screenshot = $parent_template_root . '/templates/' . $this->parentTemplateDirectory . '/images/' . $template_screenshot;
            if (is_file($parent_template_screenshot)) {
                $child_template_screenshot = $child_template_root . '/templates/' . $this->templateDirectory . '/images/' . $template_screenshot;
                if (copy($parent_template_screenshot, $child_template_screenshot) === false) {
                    return self::ERROR_CREATING_SCREENSHOT;
                }
            }
        }

        /**
         * If the parent template has a template_settings.php file, the child template uses a copy.
         */
        if ($this->parentTemplateInfo['has_template_settings'] === true) {
            $parent_template_settings_path = $this->parentTemplateInfo['template_settings_path'];
            $child_template_settings_path = $child_template_root . '/templates/' . $this->templateDirectory . '/template_settings.php';
            if (copy($parent_template_settings_path, $child_template_settings_path) === false) {
                return self::ERROR_CREATING_TEMPLATE_SETTINGS;
            }
        }

        /**
         * Create the child-template's manifest.php file, using a combination of the child-specific
         * and parent's information.
         */
        if ($this->createChildManifest() === false) {
            return self::ERROR_CREATING_MANIFEST;
        }

        return self::ERROR_NONE;
    }

    /**
     * @since ZC v3.0.0
     */
    protected function createChildTemplateDirectories(string $parent_template_root, string $child_template_root): bool
    {
        if (!is_dir($child_template_root) && !mkdir($child_template_root, 0755, recursive: true)) {
            return false;
        }

        $parent_template_subdir = '/' . $this->parentTemplateDirectory . '/';
        $child_template_subdir = '/' . $this->templateDirectory . '/';
        $parent_subdirs = $this->fs->getSubdirectoriesRecursive($parent_template_root, returnRelativeDir: true);
        foreach ($parent_subdirs as $next_subdir) {
            $next_subdir .= '/';    //- Note that returned subdirectories don't end with the directory separator!
            if (!str_contains($next_subdir, $parent_template_subdir)) {
                continue;
            }
            if (!$this->fs->createDirectoryRecursive($child_template_root, str_replace($parent_template_subdir, $child_template_subdir, $next_subdir))) {
                return false;
            }
        }

        return true;
    }

    /**
     * Creates a child-template's template_info.php using the parent's as the basis.
     *
     * Note that the parent's file's definition of the $template_name and
     * $template_description variables must have the variable name
     * followed by at least one space (e.g. $template_name = 'My template name');
     * the field is not included in the child template's version otherwise.
     *
     * @since ZC v3.0.0
     */
    protected function createChildTemplateInfo(string $child_template_root, string &$template_screenshot): bool
    {
        $parent_template_info_vars = $this->getParentTemplateInfo();
        if ($parent_template_info_vars === false) {
            return false;
        }

        $parent_template_info = file($parent_template_info_vars['parent_template_info_path']);
        if ($parent_template_info === false) {
            return false;
        }

        $template_screenshot = $parent_template_info_vars['template_screenshot'] ?? '';

        $parent_template_name = $parent_template_info_vars['template_name'] ?? '';
        $child_template_info = '';
        foreach ($parent_template_info as $next_record) {
            if (str_contains($next_record, '$template_name ')) {
                $next_record = "\$template_name = '" . $this->sanitizeSingleQuotes($this->templateName) . "';\n";
            } elseif (str_contains($next_record, '$template_description ')) {
                $parent_description = $parent_template_info_vars['template_description'] ?? '';
                $child_description = ' ' . sprintf(TEXT_INFO_CHILD_CREATED, $parent_template_name, $this->parentTemplateDirectory, date(PHP_DATE_TIME_FORMAT));
                $next_record = "\$template_description = '" . $this->sanitizeSingleQuotes($parent_description . $child_description) . "';\n";
            }
            $child_template_info .= $next_record;
        }

        return (bool)file_put_contents($child_template_root . '/templates/' . $this->templateDirectory . '/template_info.php' , $child_template_info);
    }

    /**
     * @since ZC v3.0.0
     */
    protected function createChildManifest(): bool
    {
        $child_manifest = $this->parentTemplateInfo['manifest'];
        $child_manifest['pluginVersion'] = 'v1.0.0';
        $parent_plugin_name = $child_manifest['pluginName'];
        $child_manifest['pluginName'] = $this->pluginName;
        $child_manifest['pluginDescription'] ??= '';
        $child_manifest['pluginDescription'] .= ' ' . sprintf(TEXT_INFO_CHILD_CREATED, $parent_plugin_name, $this->parentTemplateDirectory, date(PHP_DATE_TIME_FORMAT));
        $child_manifest['template']['key'] = $this->templateDirectory;
        $child_manifest['template']['baseTemplate'] = $this->parentTemplateDirectory;

        $child_manifest_contents = "<?php\nreturn [\n";
        $error = false;
        $child_manifest_contents .= $this->arrayToPhp('', $child_manifest, 4, $error);
        if ($error === true) {
            return false;
        }
        $child_manifest_contents .= "];\n\n";

        $child_manifest_file = DIR_FS_CATALOG . 'zc_plugins/' . $this->templateKey . '/v1.0.0/manifest.php';
        return (bool)file_put_contents($child_manifest_file , $child_manifest_contents);
    }

    /**
     * @since ZC v3.0.0
     */
    protected function arrayToPhp(string $contents, array $array_element, int $indent_level, bool &$error): string
    {
        foreach ($array_element as $key => $value) {
            $contents .= str_repeat(' ', $indent_level) . "'" . $this->sanitizeSingleQuotes($key) . "' => ";
            switch (gettype($value)) {
                case 'string':
                    $contents .= "'" . $this->sanitizeSingleQuotes($value) . "',\n";
                    break;
                case 'integer':
                    $contents .= "$value,\n";
                    break;
                case 'NULL':
                    $contents .= "null,\n";
                    break;
                case 'boolean':
                    $contents .= ($value === false ? 'false' : 'true') . ",\n";
                    break;
                case 'array':
                    $contents .= "[\n";
                    $indent_level += 4;
                    $contents = $this->arrayToPhp($contents, $value, $indent_level, $error);
                    $indent_level -= 4;
                    $contents .= str_repeat(' ', $indent_level) . "],\n";
                    break;
                default:
                    $error = true;
                    trigger_error("Unknown value type (" . gettype($value) . ") for manifest key ($key); the child template cannot be created.", E_USER_WARNING);
                    return '';
            }
        }
        return $contents;
    }

    /**
     * @since ZC v3.0.0
     */
    private function sanitizeSingleQuotes(string $string): string
    {
        return str_replace(['\\', "'"], ['\\\\', "\\'"], $string);
    }

    /**
     * Retrieves the variables defined in the parent template's template_info.php file.
     *
     * @since ZC v3.0.0
     */
    private function getParentTemplateInfo(): false|array
    {
        $parent_template_info_path = $this->parentTemplateInfo['template_info_path'];
        if (!is_file($parent_template_info_path)) {
            return false;
        }

        require $parent_template_info_path;
        return get_defined_vars();
    }

    public function removeChildTemplatePlugin(string $template_key): void
    {
        if ($this->templateKey === '' || $template_key !== $this->templateKey) {
            return;
        }
        $this->fs->deleteDirectory(DIR_FS_CATALOG . 'zc_plugins/' . $this->templateKey);
    }
}
