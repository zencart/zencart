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
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Illuminate\Database\Capsule\Manager as Capsule;

class DumpAdminLogsCommand extends Command
{
    protected static $defaultName = 'clear:adminlogs';

    protected function configure()
    {
        $this
            ->setDescription('Dump(empty) the Admin Logs table')
            ->setHelp(
                'This will empty the contents of the admin logs table.' . "\n" .
                '')
            ->setDefinition(
                new InputDefinition(
                    [
//                        new InputOption('type', 't', InputOption::VALUE_IS_ARRAY),
                        new InputOption('backup', 'b', InputOption::VALUE_OPTIONAL, 'Output contents of log table to a file'),
//                        new InputOption('config', 'c', InputOption::VALUE_REQUIRED),
                    ])
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('Are you sure you want to delete all admin log data(y/n)?', false);

        if (!$helper->ask($input, $output, $question)) {
            return Command::SUCCESS;
        }
        $result = Capsule::table('admin_activity_log')->get();
        print_r($result);
        return 0;
    }
}