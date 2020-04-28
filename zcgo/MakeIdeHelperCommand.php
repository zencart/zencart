<?php
/**
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  $
 */

namespace Zencart\Go;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class MakeIdeHelperCommand extends Command
{
    protected static $defaultName = 'make:idehelper';

    protected function configure()
    {
        $this
            ->setDescription('Creates files to help phpstorm resolve language file definitions')
            ->setHelp(
                '' . "\n" .
                '')
            ->setDefinition(
                new InputDefinition(
                    [
//                        new InputOption('type', 't', InputOption::VALUE_IS_ARRAY),
//                        new InputOption('dir', 'd', InputOption::VALUE_REQUIRED),
//                        new InputOption('config', 'c', InputOption::VALUE_REQUIRED),
                    ])
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->input = $input;
        $fileList = ['files' => ['zc_install/includes/languages/en_us/main.php']];
        $definitions = $this->processFileList($fileList);
        return 0;
    }

    protected function processFileList($fileList)
    {
        $definitioms = [];

    }
}