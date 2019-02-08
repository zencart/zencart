<?php

/*
 * This file is part of the "jakoch/phantomjs-installer" package.
 *
 * Copyright (c) 2013-2015 Jens-André Koch <jakoch@web.de>
 *
 * The content is released under the MIT License. Please view
 * the LICENSE file that was distributed with this source code.
 */

namespace PhantomInstaller;

use Composer\Script\Event;
use Composer\Composer;

use Composer\Package\Package;
use Composer\Package\RootPackageInterface;
use Composer\Package\Version\VersionParser;

class Installer
{
    const PHANTOMJS_NAME = 'PhantomJS';

    const PHANTOMJS_TARGETDIR = '/jakoch/phantomjs';

    const PHANTOMJS_CHMODE = 0770; // octal !

    /**
     * Operating system dependend installation of PhantomJS
     */
    public static function installPhantomJS(Event $event)
    {
        $composer = $event->getComposer();

        $version = self::getVersion($composer);

        $config = $composer->getConfig();

        $binDir = $config->get('bin-dir');

        // the installation folder depends on the vendor-dir (default prefix is './vendor')
        $targetDir = $config->get('vendor-dir') . self::PHANTOMJS_TARGETDIR;

        $io = $event->getIO();

        /* @var \Composer\Downloader\DownloadManager $downloadManager */
        $downloadManager = $composer->getDownloadManager();

        // Download the Archive

        if(self::download($io, $downloadManager, $targetDir, $version) === true)
        {
            // Copy only the PhantomJS binary from the installation "target dir" to the "bin" folder

            self::copyPhantomJsBinaryToBinFolder($targetDir, $binDir);
        }
    }

    public static function download($io, $downloadManager, $targetDir, $version)
    {
        $retries = count(self::getPhantomJsVersions());

        while ($retries--)
        {
            $package = self::createComposerInMemoryPackage($targetDir, $version);

            try {
                $downloadManager->download($package, $targetDir, false);
                return true;
            } catch (\Exception $e) {
                if ($e instanceof \Composer\Downloader\TransportException && $e->getStatusCode() === 404) {
                    $version = self::getLowerVersion($version);
                    $io->write(PHP_EOL . '<warning>Let\'s retry the download with a lower version number: "'. $version .'".</warning>');
                }
            }
        }
    }

    public static function createComposerInMemoryPackage($targetDir, $version)
    {
        $url = self::getURL($version);

        $versionParser = new VersionParser();
        $normVersion = $versionParser->normalize($version);

        $package = new Package(self::PHANTOMJS_NAME, $normVersion, $version);
        $package->setTargetDir($targetDir);
        $package->setInstallationSource('dist');
        $package->setDistType(pathinfo($url, PATHINFO_EXTENSION) === 'zip' ? 'zip' : 'tar'); // set zip, tarball
        $package->setDistUrl($url);

        return $package;
    }

    public static function getPhantomJsVersions()
    {
        return array('2.1.1', '2.0.0', '1.9.8', '1.9.7');
    }

    public static function getLatestPhantomJsVersion()
    {
        $versions = self::getPhantomJsVersions();

        return $versions[0];
    }

    public static function getLowerVersion($old_version)
    {
        foreach(self::getPhantomJsVersions() as $idx => $version)
        {
            // if $old_version is bigger than $version from versions array, return $version
            if(version_compare($old_version, $version) == 1) {
                return $version;
            }
        }
    }

    /**
     * Returns the PhantomJS version number.
     *
     * Firstly, we search for a version number in the local repository,
     * secondly, in the root package.
     * A version specification of "dev-master#<commit-reference>" is disallowed.
     *
     * @param Composer $composer
     * @return string $version Version
     */
    public static function getVersion($composer)
    {
        $packages = $composer->getRepositoryManager()->getLocalRepository()->getCanonicalPackages();

        foreach($packages as $package) {
            if($package->getName() === 'jakoch/phantomjs-installer') {
                $version = $package->getPrettyVersion();
            }
        }

        // version was not found in the local repository, let's take a look at the root package
        if($version == null) {
            $version = self::getRequiredVersion($composer->getPackage(), 'jakoch/phantomjs-installer');
        }

        // fallback to the hardcoded latest version, if "dev-master" was set
        if ($version === 'dev-master') {
            return self::getLatestPhantomJsVersion();
        }

        // grab version from commit-reference, e.g. "dev-master#<commit-ref> as version"
        if(preg_match('/dev-master#(?:.*)(\d.\d.\d)/i', $version, $matches)) {
            return $matches[1];
        }

        // grab version from a git version tag with a patch level, like "1.9.8-2"
        if(preg_match('/(\d.\d.\d)(?:(?:-\d)?)/i', $version, $matches)) {
            return $matches[1];
        }

        // grab version from a Composer patch version tag with a patch level, like "1.9.8-p02"
        if(preg_match('/(\d.\d.\d)(?:(?:-p\d{2})?)/i', $version, $matches)) {
            return $matches[1];
        }

        return $version;
    }

    /**
     * Returns the version for the given package either from the "require" or "require-dev" packages array.
     *
     * @param RootPackageInterface $package
     * @param string $packageName
     * @throws \RuntimeException
     * @return mixed
     */
    public static function getRequiredVersion(RootPackageInterface $package, $packageName = 'jakoch/phantomjs-installer')
    {
        foreach (array($package->getRequires(), $package->getDevRequires()) as $requiredPackages) {
            if (isset($requiredPackages[$packageName])) {
                return $requiredPackages[$packageName]->getPrettyConstraint();
            }
        }
        throw new \RuntimeException('Can not determine required version of ' . $packageName);
    }

    /**
     * Copies the PhantomJs binary to the bin folder.
     * Takes different "folder structure" of the archives and different "binary file names" into account.
     */
    public static function copyPhantomJsBinaryToBinFolder($targetDir, $binDir)
    {
        if (!is_dir($binDir)) {
            mkdir($binDir);
        }

        $os = self::getOS();

        $sourceName = '/bin/phantomjs';
        $targetName = $binDir . '/phantomjs';

        if ($os === 'windows') {
            // the suffix for binaries on windows is ".exe"
            $sourceName .= '.exe';
            $targetName .= '.exe';

            /**
             * The release folder structure changed between versions.
             * For versions up to v1.9.8, the executables resides at the root.
             * From v2.0.0 on, the executable resides in the bin folder.
             */
            if(is_file($targetDir . '/phantomjs.exe')) {
                $sourceName = str_replace('/bin', '', $sourceName);
            }

            // slash fix (not needed, but looks better on the dropped php file)
            $targetName = str_replace('/', '\\', $targetName);
        }

        if ($os !== 'unknown') {
            copy($targetDir . $sourceName, $targetName);
            chmod($targetName, self::PHANTOMJS_CHMODE);
        }

        self::dropClassWithPathToInstalledBinary($targetName);
    }

    /**
     * Drop php class with path to installed phantomjs binary for easier usage.
     *
     * Usage:
     *
     * use PhantomInstaller\PhantomBinary;
     *
     * $bin = PhantomInstaller\PhantomBinary::BIN;
     * $dir = PhantomInstaller\PhantomBinary::DIR;
     *
     * $bin = PhantomInstaller\PhantomBinary::getBin();
     * $dir = PhantomInstaller\PhantomBinary::getDir();
     *
     * @param  string $targetDir  path to /vendor/jakoch/phantomjs
     * @param  string $BinaryPath full path to binary
     *
     * @return bool True, if file dropped. False, otherwise.
     */
    public static function dropClassWithPathToInstalledBinary($binaryPath)
    {
        $code  = "<?php\n";
        $code .= "\n";
        $code .= "namespace PhantomInstaller;\n";
        $code .= "\n";
        $code .= "class PhantomBinary\n";
        $code .= "{\n";
        $code .= "    const BIN = '%binary%';\n";
        $code .= "    const DIR = '%binary_dir%';\n";
        $code .= "\n";
        $code .= "    public static function getBin() {\n";
        $code .= "        return self::BIN;\n";
        $code .= "    }\n";
        $code .= "\n";
        $code .= "    public static function getDir() {\n";
        $code .= "        return self::DIR;\n";
        $code .= "    }\n";
        $code .= "}\n";

        // binary      = full path to the binary
        // binary_dir  = the folder the binary resides in
        $fileContent = str_replace(
            array('%binary%', '%binary_dir%'),
            array($binaryPath, dirname($binaryPath)),
            $code
        );

        return (bool) file_put_contents(__DIR__ . '/PhantomBinary.php', $fileContent);
    }

    /**
     * Returns the URL of the PhantomJS distribution for the installing OS.
     *
     * @param string $version
     * @return string Download URL
     */
    public static function getURL($version)
    {
        $url = false;
        $os = self::getOS();

        // old versions up to v1.9.2 were hosted on https://phantomjs.googlecode.com/files/
        // newer versions are hosted on https://bitbucket.org/ariya/phantomjs/downloads/

        if ($os === 'windows') {
            $url = 'https://bitbucket.org/ariya/phantomjs/downloads/phantomjs-' . $version . '-windows.zip';
        }

        if ($os === 'linux') {
            $bitsize = self::getBitSize();

            if ($bitsize === 32) {
                $url = 'https://bitbucket.org/ariya/phantomjs/downloads/phantomjs-' . $version . '-linux-i686.tar.bz2';
            }

            if ($bitsize === 64) {
                $url = 'https://bitbucket.org/ariya/phantomjs/downloads/phantomjs-' . $version . '-linux-x86_64.tar.bz2';
            }
        }

        if ($os === 'macosx') {
            $url = 'https://bitbucket.org/ariya/phantomjs/downloads/phantomjs-' . $version . '-macosx.zip';
        }

        # OS unknown
        if ($url === false) {
            throw new \RuntimeException(
                'The Installer could not select a PhantomJS package for this OS.
                Please install PhantomJS manually into the /bin folder of your project.'
            );
        }

        return $url;
    }

    /**
     * Returns the Operating System.
     *
     * @return string OS, e.g. macosx, windows, linux.
     */
    public static function getOS()
    {
        $uname = strtolower(php_uname());

        if (strpos($uname, "darwin") !== false) {
            return 'macosx';
        } elseif (strpos($uname, "win") !== false) {
            return 'windows';
        } elseif (strpos($uname, "linux") !== false) {
            return 'linux';
        } else {
            return 'unknown';
        }
    }

    /**
     * Returns the Bit-Size.
     *
     * @return string BitSize, e.g. 32, 64.
     */
    public static function getBitSize()
    {
        if (PHP_INT_SIZE === 4) {
            return 32;
        }

        if (PHP_INT_SIZE === 8) {
            return 64;
        }

        return PHP_INT_SIZE; // 16-bit?
    }
}
