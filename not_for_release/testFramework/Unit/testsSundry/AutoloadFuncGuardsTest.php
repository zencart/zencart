<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

declare(strict_types=1);

namespace Tests\Unit\testsSundry;

use PHPUnit\Framework\TestCase;
use Tests\Support\UnitTestBootstrap;

/**
 * Covers the autoloader loop's handling of an action list that refers to a class
 * which was never loaded.
 *
 * A classInstantiate entry is queued whether or not the matching class entry found
 * its file, and the two carry no reference to each other, so this loop is the only
 * place the mismatch can be caught. Before v3.0.0 it ran `new $className()` blindly,
 * turning a missing plugin file into a fatal during bootstrap, which took out the
 * storefront and the admin page needed to disable the plugin responsible.
 */
class AutoloadFuncGuardsTest extends TestCase
{
    /**
     * @var string[]
     */
    private array $warnings = [];

    public static function setUpBeforeClass(): void
    {
        UnitTestBootstrap::initialize();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->warnings = [];
    }

    /**
     * Runs the real autoloader loop over a hand-built action list, capturing any
     * warnings it raises. Returns the variables the loop left behind.
     *
     * @param array<int, array<string, mixed>> $initSystemList
     * @return array<string, mixed>
     */
    private function runAutoloadLoop(array $initSystemList): array
    {
        set_error_handler(function (int $errno, string $errstr): bool {
            $this->warnings[] = $errstr;
            return true;
        });

        try {
            require DIR_FS_CATALOG . 'includes/autoload_func.php';
        } finally {
            restore_error_handler();
        }

        $created = get_defined_vars();
        unset($created['initSystemList'], $created['debugAutoload'], $created['entry']);
        return $created;
    }

    public function testMissingClassIsSkippedInsteadOfFatal(): void
    {
        $created = $this->runAutoloadLoop([
            ['type' => 'class', 'object' => 'missingObj', 'class' => 'ZcClassThatWasNeverLoaded', 'loaderType' => 'plugin'],
        ]);

        $this->assertArrayNotHasKey('missingObj', $created);
        $this->assertCount(1, $this->warnings);
        $this->assertStringContainsString('ZcClassThatWasNeverLoaded', $this->warnings[0]);
        $this->assertStringContainsString('was not loaded', $this->warnings[0]);
    }

    public function testMissingSessionClassIsSkippedInsteadOfFatal(): void
    {
        $sessionBefore = $GLOBALS['_SESSION'] ?? [];
        $GLOBALS['_SESSION'] = [];

        try {
            $this->runAutoloadLoop([
                [
                    'type' => 'sessionClass',
                    'object' => 'missingSessionObj',
                    'class' => 'ZcSessionClassThatWasNeverLoaded',
                    'checkInstantiated' => false,
                    'loaderType' => 'plugin',
                ],
            ]);
            $sessionAfter = $GLOBALS['_SESSION'];
        } finally {
            $GLOBALS['_SESSION'] = $sessionBefore;
        }

        $this->assertArrayNotHasKey('missingSessionObj', $sessionAfter);
        $this->assertCount(1, $this->warnings);
        $this->assertStringContainsString('was not loaded', $this->warnings[0]);
    }

    /**
     * A session serialized while the class still existed leaves an
     * __PHP_Incomplete_Class in the slot, which is_object() accepts. Leaving it there
     * would carry the failure forward to any objectMethod entry using that object, so
     * the unusable value has to be discarded.
     */
    public function testIncompleteSessionObjectIsDiscardedRatherThanCarriedForward(): void
    {
        $sessionBefore = $GLOBALS['_SESSION'] ?? [];
        $GLOBALS['_SESSION'] = [
            'staleObj' => unserialize('O:22:"ZcSessionClassLongGone":1:{s:1:"a";i:1;}'),
        ];
        self::assertTrue(is_object($GLOBALS['_SESSION']['staleObj']), 'fixture should be an incomplete object');

        try {
            $this->runAutoloadLoop([
                [
                    'type' => 'sessionClass',
                    'object' => 'staleObj',
                    'class' => 'ZcSessionClassLongGone',
                    'checkInstantiated' => false,
                    'loaderType' => 'plugin',
                ],
                ['type' => 'objectMethod', 'object' => 'staleObj', 'method' => 'someMethod', 'loaderType' => 'plugin'],
            ]);
            $sessionAfter = $GLOBALS['_SESSION'];
        } finally {
            $GLOBALS['_SESSION'] = $sessionBefore;
        }

        $this->assertArrayNotHasKey('staleObj', $sessionAfter);
        $this->assertCount(2, $this->warnings);
        $this->assertStringContainsString('is not available', $this->warnings[1]);
    }

    /**
     * The class being available again does not make the stale value usable. The
     * session is decoded before this loop runs, so the slot can hold an
     * __PHP_Incomplete_Class even on a request where the class loads fine, and
     * checkInstantiated would keep it because the slot is set.
     */
    public function testIncompleteSessionObjectIsReplacedWhenTheClassIsAvailable(): void
    {
        $sessionBefore = $GLOBALS['_SESSION'] ?? [];
        $GLOBALS['_SESSION'] = [
            'probeObj' => unserialize('O:24:"AutoloadFuncGuardsProbeX":1:{s:1:"a";i:1;}'),
        ];
        self::assertInstanceOf(\__PHP_Incomplete_Class::class, $GLOBALS['_SESSION']['probeObj']);

        try {
            $this->runAutoloadLoop([
                [
                    'type' => 'sessionClass',
                    'object' => 'probeObj',
                    'class' => AutoloadFuncGuardsProbe::class,
                    'checkInstantiated' => true,
                ],
                ['type' => 'objectMethod', 'object' => 'probeObj', 'method' => 'markCalled'],
            ]);
            $sessionAfter = $GLOBALS['_SESSION'];
        } finally {
            $GLOBALS['_SESSION'] = $sessionBefore;
        }

        $this->assertSame([], $this->warnings);
        $this->assertInstanceOf(AutoloadFuncGuardsProbe::class, $sessionAfter['probeObj']);
        $this->assertTrue($sessionAfter['probeObj']->called);
    }

    /**
     * Skipping an instantiation must not simply move the fatal to the objectMethod
     * entry that expects the object to exist.
     */
    public function testObjectMethodOnAMissingObjectIsSkippedInsteadOfFatal(): void
    {
        $this->runAutoloadLoop([
            ['type' => 'class', 'object' => 'missingObj', 'class' => 'ZcClassThatWasNeverLoaded', 'loaderType' => 'plugin'],
            ['type' => 'objectMethod', 'object' => 'missingObj', 'method' => 'someMethod', 'loaderType' => 'plugin'],
        ]);

        $this->assertCount(2, $this->warnings);
        $this->assertStringContainsString('is not available', $this->warnings[1]);
        $this->assertStringContainsString('someMethod', $this->warnings[1]);
    }

    /**
     * Recovery is for plugins only. A core class is part of the store itself, and
     * continuing without notifier, shoppingCart or the like would serve a
     * half-initialised request, so those keep failing fast exactly as before.
     */
    public function testMissingCoreClassStillFailsFast(): void
    {
        $this->expectException(\Error::class);

        $this->runAutoloadLoop([
            ['type' => 'class', 'object' => 'coreObj', 'class' => 'ZcCoreClassThatWasNeverLoaded', 'loaderType' => 'core'],
        ]);
    }

    /**
     * An entry with no loaderType at all is treated as core, so an action list built
     * before this field existed keeps its fail-fast behaviour.
     */
    public function testEntryWithoutLoaderTypeIsTreatedAsCore(): void
    {
        $this->expectException(\Error::class);

        $this->runAutoloadLoop([
            ['type' => 'class', 'object' => 'legacyObj', 'class' => 'ZcCoreClassThatWasNeverLoaded'],
        ]);
    }

    public function testMissingCoreObjectMethodStillFailsFast(): void
    {
        $this->expectException(\Error::class);

        $this->runAutoloadLoop([
            ['type' => 'objectMethod', 'object' => 'absentCoreObj', 'method' => 'someMethod', 'loaderType' => 'core'],
        ]);
    }

    /**
     * The guard must not disturb the normal path.
     */
    public function testExistingClassIsStillInstantiatedAndItsMethodCalled(): void
    {
        $created = $this->runAutoloadLoop([
            ['type' => 'class', 'object' => 'realObj', 'class' => AutoloadFuncGuardsProbe::class],
            ['type' => 'objectMethod', 'object' => 'realObj', 'method' => 'markCalled'],
        ]);

        $this->assertSame([], $this->warnings);
        $this->assertArrayHasKey('realObj', $created);
        $this->assertInstanceOf(AutoloadFuncGuardsProbe::class, $created['realObj']);
        $this->assertTrue($created['realObj']->called);
    }
}

class AutoloadFuncGuardsProbe
{
    public bool $called = false;

    public function markCalled(): void
    {
        $this->called = true;
    }
}
