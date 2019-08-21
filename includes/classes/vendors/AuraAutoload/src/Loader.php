<?php
/**
 *
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 *
 */
namespace Aura\Autoload;

/**
 *
 * An SPL autoloader adhering to PSR-4.
 *
 * @package Aura.Autoload
 *
 */
class Loader
{
    /**
     *
     * A map of explicit class names to their file paths.
     *
     * @var array
     *
     */
    protected $class_files = array();

    /**
     *
     * Debug information populated by loadClass().
     *
     * @var array
     *
     */
    protected $debug = array();

    /**
     *
     * Classes, interfaces, and traits loaded by the autoloader; the key is
     * the class name and the value is the file name.
     *
     * @var array
     *
     */
    protected $loaded_classes = array();

    /**
     *
     * A map of namespace prefixes to base directories.
     *
     * @var array
     *
     */
    protected $prefixes = array();

    /**
     *
     * Registers this autoloader with SPL.
     *
     * @param bool $prepend True to prepend to the autoload stack.
     *
     * @return null
     *
     */
    public function register($prepend = false)
    {
        spl_autoload_register(
            array($this, 'loadClass'),
            true,
            (bool) $prepend
        );
    }

    /**
     *
     * Unregisters this autoloader from SPL.
     *
     * @return null
     *
     */
    public function unregister()
    {
        spl_autoload_unregister(array($this, 'loadClass'));
    }

    /**
     *
     * Returns the debugging information array from the last loadClass()
     * attempt.
     *
     * @return array
     *
     */
    public function getDebug()
    {
        return $this->debug;
    }

    /**
     *
     * Adds a base directory for a namespace prefix.
     *
     * @param string $prefix The namespace prefix.
     *
     * @param string|array $base_dirs One or more base directories for the
     * namespace prefix.
     *
     * @param bool $prepend If true, prepend the base directories to the
     * prefix instead of appending them; this causes them to be searched
     * first rather than last.
     *
     * @return null
     *
     */
    public function addPrefix($prefix, $base_dirs, $prepend = false)
    {
        // normalize the namespace prefix
        $prefix = trim($prefix, '\\') . '\\';

        // initialize the namespace prefix array if needed
        if (! isset($this->prefixes[$prefix])) {
            $this->prefixes[$prefix] = array();
        }

        // normalize each base dir with a trailing separator
        $base_dirs = (array) $base_dirs;
        foreach ($base_dirs as $key => $base_dir) {
            $base_dirs[$key] = rtrim($base_dir, DIRECTORY_SEPARATOR)
                             . DIRECTORY_SEPARATOR;
        }

        // prepend or append?
        if ($prepend) {
            $this->prefixes[$prefix] = array_merge($base_dirs, $this->prefixes[$prefix]);
        } else {
            $this->prefixes[$prefix] = array_merge($this->prefixes[$prefix], $base_dirs);
        }
    }

    /**
     *
     * Sets all namespace prefixes and their base directories. This overwrites
     * the existing prefixes.
     *
     * @param array $prefixes An associative array of namespace prefixes and
     * their base directories.
     *
     * @return null
     *
     */
    public function setPrefixes(array $prefixes)
    {
        $this->prefixes = array();
        foreach ($prefixes as $key => $val) {
            $this->addPrefix($key, $val);
        }
    }

    /**
     *
     * Returns the list of all class name prefixes and their base directories.
     *
     * @return array
     *
     */
    public function getPrefixes()
    {
        return $this->prefixes;
    }

    /**
     *
     * Sets the explicit file path for an explicit class name.
     *
     * @param string $class The explicit class name.
     *
     * @param string $file The file path to that class.
     *
     * @return null
     *
     */
    public function setClassFile($class, $file)
    {
        $this->class_files[$class] = $file;
    }

    /**
     *
     * Sets all file paths for all class names; this overwrites all previous
     * explicit mappings.
     *
     * @param array $class_files An array of class-to-file mappings where the
     * key is the class name and the value is the file path.
     *
     * @return null
     *
     */
    public function setClassFiles(array $class_files)
    {
        $this->class_files = $class_files;
    }

    /**
     *
     * Adds file paths for class names to the existing explicit mappings.
     *
     * @param array $class_files An array of class-to-file mappings where the
     * key is the class name and the value is the file path.
     *
     * @return null
     *
     */
    public function addClassFiles(array $class_files)
    {
        $this->class_files = array_merge($this->class_files, $class_files);
    }

    /**
     *
     * Returns the list of explicit class names and their file paths.
     *
     * @return array
     *
     */
    public function getClassFiles()
    {
        return $this->class_files;
    }

    /**
     *
     * Returns the list of classes, interfaces, and traits loaded by the
     * autoloader.
     *
     * @return array An array of key-value pairs where the key is the class
     * or interface name and the value is the file name.
     *
     */
    public function getLoadedClasses()
    {
        return $this->loaded_classes;
    }

    /**
     *
     * Loads the class file for a given class name.
     *
     * @param string $class The fully-qualified class name.
     *
     * @return mixed The mapped file name on success, or boolean false on
     * failure.
     *
     */
    public function loadClass($class)
    {
        // reset debug info
        $this->debug = array("Loading $class");

        // is an explicit class file noted?
        if (isset($this->class_files[$class])) {
            $file = $this->class_files[$class];
            $found = $this->requireFile($file);
            if ($found) {
                $this->debug[] = "Loaded from explicit: $file";
                $this->loaded_classes[$class] = $file;
                return $file;
            }
        }

        // no explicit class file
        $this->debug[] = "No explicit class file";

        // the current namespace prefix
        $prefix = $class;

        // work backwards through the namespace names of the fully-qualified
        // class name to find a mapped file name
        while (false !== $pos = strrpos($prefix, '\\')) {

            // retain the trailing namespace separator in the prefix
            $prefix = substr($class, 0, $pos + 1);

            // the rest is the relative class name
            $relative_class = substr($class, $pos + 1);

            // try to load a mapped file for the prefix and relative class
            $file = $this->loadFile($prefix, $relative_class);
            if ($file) {
                $this->debug[] = "Loaded from $prefix: $file";
                $this->loaded_classes[$class] = $file;
                return $file;
            }

            // remove the trailing namespace separator for the next iteration
            // of strrpos()
            $prefix = rtrim($prefix, '\\');
        }

        // did not find a file for the class
        $this->debug[] = "$class not loaded";
        return false;
    }

    /**
     *
     * Load the mapped file for a namespace prefix and relative class.
     *
     * @param string $prefix The namespace prefix.
     *
     * @param string $relative_class The relative class name.
     *
     * @return mixed Boolean false if no mapped file can be loaded, or the
     * name of the mapped file that was loaded.
     *
     */
    protected function loadFile($prefix, $relative_class)
    {
        // are there any base directories for this namespace prefix?
        if (! isset($this->prefixes[$prefix])) {
            $this->debug[] = "$prefix: no base dirs";
            return false;
        }

        // look through base directories for this namespace prefix
        foreach ($this->prefixes[$prefix] as $base_dir) {

            // replace the namespace prefix with the base directory,
            // replace namespace separators with directory separators
            // in the relative class name, append with .php
            $file = $base_dir
                  . str_replace('\\', DIRECTORY_SEPARATOR, $relative_class)
                  . '.php';

            // if the mapped file exists, require it
            if ($this->requireFile($file)) {
                // yes, we're done
                return $file;
            }

            // not in the base directory
            $this->debug[] = "$prefix: $file not found";
        }

        // never found it
        return false;
    }

    /**
     *
     * If a file exists, require it from the file system.
     *
     * @param string $file The file to require.
     *
     * @return bool True if the file exists, false if not.
     *
     */
    protected function requireFile($file)
    {
        if (file_exists($file)) {
            require $file;
            return true;
        }
        return false;
    }
}
