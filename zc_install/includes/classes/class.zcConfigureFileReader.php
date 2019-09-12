<?php
/**
 * file contains zcConfigureFileReader Class
 * @package Installer
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Author: zcwilt  Sat Dec 5 18:49:20 2015 +0000 New in v1.5.5 $
 */
/**
 *
 * zcConfigureFileReader Class
 *
 */
class zcConfigureFileReader {

	/**
	 * The location of the configuration file.
	 * @var string
	 */
	protected $file;

	/**
	 * The cached contents of the configuration file.
	 * @var string
	 */
	protected $fileContent;

	/**
	 * Constructs a reader for Zen Cart configuration files.
	 *
	 * @param string $file the full path of the configuration file.
	 */
	public function __construct($file = null) {
		$this->setFile($file);
	}

	/**
	 * Sets the configuration file this reader will operate upon.
	 * Calling this function will reset the file contents cache.
	 *
	 * @param string $file the full path of the configuration file.
	 */
	public function setFile($file = null) {
		// Reset the cached file and contents
		$this->file = null;
		$this->fileContent = null;

		if($file !== null) {
			$realfile = realpath($file);
			if(file_exists($realfile)) {
				$this->file = $realfile;
				$content = @file_get_contents($realfile);
				if($content !== false && trim($content) !== '')
					$this->fileContent = $content;
			}
		}

		return $this;
	}

	/**
	 * Indicates if the configuration file exists.
	 *
	 * @return boolean true of the configuration file exists, false otherwise.
	 */
	public function fileExists() {
		return $this->file !== null;
	}

	/**
	 * Indicates the the configuration file could be loaded into memory.
	 *
	 * @return boolean true if the configuration file could be loaded, false otherwise.
	 */
	public function fileLoaded() {
		return $this->fileContent !== null;
	}

	/**
	 * Retrieves the raw value of a configured constant from the configure file.
	 * This method does not evaluate or cache the defined value.
	 *
	 * @param string $searchDefine the name / key of the constant to search for.
	 * @return NULL|string the value of the constant or null of the constant was not found.
	 */
	public function getRawDefine($searchDefine) {
		// Validate the file exists (and content is useable)
		if(!$this->fileLoaded()) return null;

		// Extract the contents of the define
        if(preg_match('|^\s*define\(\s*[\'"]' . $searchDefine . '[\'"]\s*,\s*(?!\s*\);)(.+?)\s*\);|m', $this->fileContent, $matches)) {
            return $matches[1];
        }
		return null;
	}

	/**
	 * Retrieves the value of a configured constant from the configure file
	 * without loading all the define statements. The value of the defined
	 * constant will be evaluated and cached in memory. The memory cache will
	 * not be reset until the PHP script has finished running.
	 *
	 * This method takes into consideration the script is being run from the
	 * Zen Cart installer and will replace some constants prior to evaluating
	 * the defined constant.
	 *
	 * @param string $searchDefine the name / key of the constant to search for.
	 * @return mixed|NULL the value of the constant or null of the constant was not found.
	 */
	public function getDefine($searchDefine) {
		// If we have already retrieved this key, simply return the answer.
		if(defined('TMP_' . $this->file . '_' . $searchDefine)) {
			return constant('TMP_' . $this->file . '_' . $searchDefine);
		}

		// Validate the file exists (and content is useable)
		$define = $this->getRawDefine($searchDefine);
		if($define !== null) {
			// This replaces DIR_FS_CATALOG with DIR_FS_ROOT so filesystem
			// based defines are correctly evaluated from the installer.
			$define = str_replace('DIR_FS_CATALOG', 'DIR_FS_ROOT', $define);

			// This code is already executing from the file when loaded
			// So using eval the same as the configure.php file being loaded
			// does not add an additional degree of risk / danger.
			$define = 'define(\'' . 'TMP_' . $this->file . '_' . $searchDefine . '\',' . $define . ');';
			eval("$define");
			if(defined('TMP_' . $this->file . '_' . $searchDefine)) {
				return constant('TMP_' . $this->file . '_' . $searchDefine);
			}
		}

		return null;
	}

    public function getStoreInputsFromLegacy()
    {
        $mapper = array(
            'HTTP_SERVER' => 'http_server_catalog',
            'HTTPS_SERVER' => 'https_server_catalog',
            'ENABLE_SSL' => 'enable_ssl_catalog',
            'DIR_WS_CATALOG' => 'dir_ws_http_catalog',
            'DIR_WS_HTTPS_CATALOG' => 'dir_ws_https_catalog',
            'DIR_FS_CATALOG' => 'physical_path',
            'DB_TYPE' => 'db_type',
            'DB_PREFIX' => 'db_prefix',
            'DB_CHARSET' => 'db_charset',
            'DB_SERVER' => 'db_host',
            'DB_SERVER_USERNAME'  => 'db_user',
            'DB_SERVER_PASSWORD' => 'db_password',
            'DB_DATABASE' => 'db_name',
            'SQL_CACHE_METHOD' => 'sql_cache_method',
        );
        return $this->processConfigureInputsMapper($mapper);
    }

    protected function processConfigureInputsMapper($mapper)
    {
        $inputs = array();
        foreach ($mapper as $defineKey => $inputsKey) {
            $value = $this->getRawDefine($defineKey);
            $value = trim($value, "'");
            $inputs[$inputsKey] = $value;
        }
        return $inputs;
    }
}
