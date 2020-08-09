<?php declare(strict_types=1);
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: $
 */

namespace App\Console\Commands;

use App\Console\DefinesConverter\DefinesNodeVisitor;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Exception\InvalidOptionException;
use PhpParser\ParserFactory;
use PhpParser\NodeTraverser;

class ZencartConvertDefines extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zencart:convertdefines {--file=} {--dir=} {--config=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates array type language files from legacy define files';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->fs = new Filesystem();
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() : int
    {
        try {
            $this->validateInputOptions();
        } catch (InvalidOptionException $e) {
            $this->error($e->getMessage());
            exit(1);
        }
        try {
            $this->processFilesFromInput();
        } catch (InvalidOptionException $e) {
            $this->error($e->getMessage());
            exit(1);
        }
        return 0;
    }

    protected function processFilesFromInput()
    {
        $file = $this->option('file');
        $dir = $this->option('dir');
        $config = $this->option('config');
        try {
            if (isset($file)) {
                $this->processSingleFile($file);
            }
            if (isset($dir)) {
                $this->processDirectory($dir);
            }
            if (isset($config)) {
                $this->processConfigFile($config);
            }
        } catch (InvalidOptionException $e) {
            throw new InvalidOptionException($e->getMessage());
        }
    }

    private function validateInputOptions()
    {
        $file = $this->option('file');
        $dir = $this->option('dir');
        $config = $this->option('config');
        if (!isset($file) && !isset($dir) && !isset($config)) {
            throw new InvalidOptionException('Seems you didn\'t pass any options');
        }
        $this->validateConfigFile($config);
        $this->validateFile($file);
        $this->validateDirectory($dir);

    }
    protected function validateConfigFile(?string $configFile)
    {
        if (!isset($configFile)) {
            return;
        }
        if (!is_file($configFile)) {
            throw new InvalidOptionException('Invalid file for config option');
        }
    }

    protected function validateFile(?string $file)
    {
        if (!isset($file)) {
            return;
        }
        if (!is_file($file)) {
            throw new InvalidOptionException('Invalid file for file option:' . $file);
        }
    }

    protected function validateDirectory(?string $directory)
    {
        if (!isset($directory)) {
            return;
        }
        if (!is_dir($directory)) {
            throw new InvalidOptionException('Invalid directory for dir option: ' . $directory);
        }
    }
    protected function processSingleFile(string $fileToConvert)
    {
        $pathInfo = pathinfo($fileToConvert);
        $originalFile = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '.' . $pathInfo['extension'];
        $destinationFile = $pathInfo['dirname'] . '/' . 'lang.' . $pathInfo['filename'] . '.' . $pathInfo['extension'];
        $defineList = $this->parseFile($originalFile);
        if (count($defineList) == 0) {
            throw new InvalidOptionException($fileToConvert . ' does not appear to be a define file');
        }
        $this->writeOutputFile($destinationFile, $defineList);
    }

    protected function processDirectory(string $directory)
    {
        $this->output->writeln('processing directory ' . $directory);
        $fileList = $this->fs->files($directory);
        foreach ($fileList as $file) {
            if (!preg_match('~(.*)\.php$~', $file->getFilename())) continue;
            if (preg_match('~^lang\.~', $file->getFilename())) continue;
            try {
                $this->processSingleFile($file->getPathname());
                $this->doVerboseOutput('processing file ' . $file->getPathname());
            } catch (InvalidOptionException $e) {
                $this->doVerboseOutput('Invalid file :' . $file->getPathname());
            }
        }
    }

    protected function processConfigFile(string $configFile)
    {
        $configDetails = require $configFile;
        if (!isset($configDetails['files']) && !isset($configDetails['directories'])) {
            throw new InvalidOptionException('config file does not appear to be valid');
        }
        $this->processConfigFileFiles($configDetails);
        $this->processConfigFileDirectories($configDetails);
    }

    protected function processConfigFileFiles(array $configDetails)
    {
        if (!isset($configDetails['files'])) {
            return;
        }
        foreach ($configDetails['files'] as $file) {
            $this->validateFile($file);
            $this->processSingleFile($file);
        }
    }

    protected function processConfigFileDirectories(array $configDetails)
    {
        if (!isset($configDetails['directories'])) {
            return;
        }
        foreach ($configDetails['directories'] as $directory) {
            $this->validateDirectory($directory);
            $this->processDirectory($directory);
        }
    }

    protected function parseFile(string $originalFile)
    {
        $data = file_get_contents($originalFile);
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $ast = $parser->parse($data);
        $nodeVisitor = new DefinesNodeVisitor();
        $traverser = new NodeTraverser;
        $traverser->addVisitor($nodeVisitor);
        $traverser->traverse($ast);
        $defines = $nodeVisitor->getCollected();
        return $defines;
    }

    public function writeOutputFile(string $destinationFile, array $outputData)
    {
        $this->doVerboseOutput('Writing Destination ' . $destinationFile);
        $fp = fopen($destinationFile, 'w');
        fwrite($fp, '<?php' . "\n");
        fwrite($fp, '/**' . "\n");
        fwrite($fp, ' * @copyright Copyright 2003-' . date('Y') . ' Zen Cart Development Team' . "\n");
        fwrite($fp, ' * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0' . "\n");
        fwrite($fp, ' * @version $Id:' . "\n");
        fwrite($fp, '*/' . "\n\n");
        fwrite($fp, '$define = [' . "\n");
        foreach ($outputData as $definevalue) {
            fwrite($fp, "   '" . $definevalue[0] . "' => ");
            fwrite($fp, $definevalue[1]);
            fwrite($fp, ",\n");
        }
        fwrite($fp, '];' . "\n\n");
        fwrite($fp, 'return $define;' . "\n");
        fclose($fp);
    }

    protected function doVerboseOutput(string $output)
    {
        if (!$this->getOutput()->isVerbose()) {
            return;
        }
        $this->info($output);
    }
}
