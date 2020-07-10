<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Exception\InvalidOptionException;

class MakeDefinesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:defines {--f=} {--d=} {--c=}';

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
    public function handle()
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
        $file = $this->option('f');
        $dir = $this->option('d');
        $config = $this->option('c');
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
        $file = $this->option('f');
        $dir = $this->option('d');
        $config = $this->option('c');
        if (!isset($file) && !isset($dir) && !isset($config)) {
            throw new InvalidOptionException('Seems you didn\'t pass any options');
        }
        $this->validateConfigFile($config);
        $this->validateFile($file);
        $this->validateDirectory($dir);

    }
    protected function validateConfigFile($configFile)
    {
        if (!isset($configFile)) {
            return;
        }
        if (!is_file($configFile)) {
            throw new InvalidOptionException('Invalid file for config option');
        }
    }

    protected function validateFile($file)
    {
        if (!isset($file)) {
            return;
        }
        if (!is_file($file)) {
            throw new InvalidOptionException('Invalid file for file option:' . $file);
        }
    }

    protected function validateDirectory($directory)
    {
        if (!isset($directory)) {
            return;
        }
        if (!is_dir($directory)) {
            throw new InvalidOptionException('Invalid directory for dir option: ' . $directory);
        }
    }
    protected function processSingleFile($fileToConvert)
    {
        $pathInfo = pathinfo($fileToConvert);
        $originalFile = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '.' . $pathInfo['extension'];
        $destinationFile = $pathInfo['dirname'] . '/' . 'lang.' . $pathInfo['filename'] . '.' . $pathInfo['extension'];
        $tokenized = $this->tokenizeData($originalFile);
        $parsed = $this->parseTokenizedData($tokenized);
        $outputData = $this->buildOutputData($parsed);
        if (count($outputData) == 0) {
            throw new InvalidOptionException($fileToConvert . ' does not appear to be a define file');
        }
        $this->writeOutputFile($destinationFile, $outputData);
    }

    protected function processDirectory($directory)
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

    protected function processConfigFile($configFile)
    {
        $configDetails = require $configFile;
        if (!isset($configDetails['files']) && !isset($configDetails['directories'])) {
            throw new InvalidOptionException('config file does not appear to be valid');
        }
        $this->processConfigFileFiles($configDetails);
        $this->processConfigFileDirectories($configDetails);
    }

    protected function processConfigFileFiles($configDetails)
    {
        if (!isset($configDetails['files'])) {
            return;
        }
        foreach ($configDetails['files'] as $file) {
            $this->validateFile($file);
            $this->processSingleFile($file);
        }
    }

    protected function processConfigFileDirectories($configDetails)
    {
        if (!isset($configDetails['directories'])) {
            return;
        }
        foreach ($configDetails['directories'] as $directory) {
            $this->validateDirectory($directory);
            $this->processDirectory($directory);
        }
    }

    protected function tokenizeData($originalFile)
    {
        $data = file_get_contents($originalFile);
        $tokenizedData = token_get_all($data);
        return $tokenizedData;
    }

    protected function parseTokenizedData($tokenizedData)
    {
        $currentLineNumber = -1;
        $currentLine = [];
        $builtLines = [];

        foreach ($tokenizedData as $token) {
            if ($this->canSkipCurrentToken($token)) continue;
            $lineNumber = $token[2];
            if ($currentLineNumber != $lineNumber) {
                $currentLineNumber = $lineNumber;
                $builtLines[] = $currentLine;
                $currentLine = [];
            }
            $currentLine[] = $token;
        }
        $builtLines[] = $currentLine;
        return $builtLines;
    }

    protected function canSkipCurrentToken($token)
    {
        if (!is_array($token)) return true;
        if (count($token) < 3) return true;
        return false;
    }

    public function buildOutputData($builtLines)
    {
        $outputData = [];
        foreach ($builtLines as $tokens) {
            $pointer = $this->skipLeadingWhiteSpace($tokens);
            if ($pointer == -1) continue;
            if (!isset($tokens[$pointer])) continue;
            if ($tokens[$pointer][1] != 'define') continue;
            $pointer++;
            $pointer = $this->skipLeadingWhiteSpace($tokens, $pointer);
            $defineKey = $tokens[$pointer][1];
            $pointer++;
            $pointer = $this->skipLeadingWhiteSpace($tokens, $pointer);
            $defineValue = $this->buildDefineValue($tokens, $pointer);
            $outputData[] = [$defineKey, $defineValue];
        }
        return $outputData;
    }

    protected function skipLeadingWhiteSpace($tokens, $start = 0)
    {
        if (!isset($tokens[$start])) return false;
        if (!isset($tokens[$start][0])) return false;
        foreach ($tokens as $tokenPointer => $token) {
            $pointer = -1;
            if ($tokenPointer < $start) continue;
            if ($token[0] === T_WHITESPACE) continue;
            $pointer = $tokenPointer;
            break;
        }
        return $pointer;
    }

    protected function buildDefineValue($tokens, $pointer)
    {
        $completed = false;
        $defineValue = '';
        $allowedTokens = [T_STRING, T_CONSTANT_ENCAPSED_STRING, T_LNUMBER];
        while ($completed == false) {
            if (in_array($tokens[$pointer][0], $allowedTokens)) {
                $defineValue .= $tokens[$pointer][1] . ' . ';
            }
            $pointer++;
            if ($pointer >= count($tokens)) {
                $defineValue = rtrim($defineValue, ' . ');
                $completed = true;
            }
        }
        return $defineValue;
    }

    public function writeOutputFile($destinationFile, $outputData)
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
            fwrite($fp, '    ' . $definevalue[0] . " => ");
            fwrite($fp, $definevalue[1]);
            fwrite($fp, ",\n");
        }
        fwrite($fp, '];' . "\n\n");
        fwrite($fp, 'return $define;' . "\n");
        fclose($fp);
    }
    protected function doVerboseOutput($output)
    {
        if (!$this->getOutput()->isVerbose()) {
            return;
        }
        $this->info($output);
    }
}
