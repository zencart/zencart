<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Unit\testsSundry;

use PHPUnit\Framework\TestCase;
use Tests\Support\UnitTestBootstrap;
use Zencart\Console\ConsoleInput;

class ConsoleInputTest extends TestCase
{
    protected $preserveGlobalState = false;

    public static function setUpBeforeClass(): void
    {
        UnitTestBootstrap::initialize();
        require_once DIR_FS_CATALOG . 'includes/classes/vendors/AuraAutoload/src/Loader.php';
        $psr4Autoloader = new \Aura\Autoload\Loader();
        $psr4Autoloader->register();
        require DIR_FS_CATALOG . 'includes/psr4Autoload.php';
    }

    public function testParsesCommandArgumentsAndOptions(): void
    {
        $input = new ConsoleInput([
            'zc_cli.php',
            'demo:run',
            'first',
            '--name=zen',
            '--verbose',
            '-q',
            '-o',
            'value',
            '--',
            '--literal',
        ]);

        $this->assertSame('demo:run', $input->getCommandName());
        $this->assertSame(['first', '--literal'], $input->getArguments());
        $this->assertSame('zen', $input->getOption('name'));
        $this->assertTrue($input->getOption('verbose'));
        $this->assertTrue($input->getOption('q'));
        $this->assertSame('value', $input->getOption('o'));
    }
}
