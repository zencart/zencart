<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

declare(strict_types=1);

namespace Tests\Unit\testsTemplateResolver;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Tests\Support\UnitTestBootstrap;
use Zencart\Templates\TemplateChild;

/**
 * TemplateChild writes two PHP files from admin-supplied values: the child template's
 * template_info.php and its manifest.php. Both are later require'd by the application,
 * so a value that escapes its string literal becomes executable code.
 *
 * These tests assert the generated source round-trips every input exactly and never
 * places anything in code position. They do not inspect the escaped string: they write
 * the file TemplateChild would write, lint it, and require it back.
 *
 * @see \Zencart\Templates\TemplateChild::createChildTemplateInfo()
 * @see \Zencart\Templates\TemplateChild::createChildManifest()
 */
class TemplateChildGeneratedPhpTest extends TestCase
{
    /** Set by an injected payload if one ever reaches code position. */
    private const MARKER = 'zc_template_child_injection_marker';

    /**
     * Local-variable sentinel for the one payload that both escapes its literal and
     * still parses. The generated file is require'd into the test method's scope, so
     * an injected assignment lands here.
     */
    private const EXECUTION_SENTINEL = 'zc_template_child_execution_sentinel';

    private string $workDir;

    public static function setUpBeforeClass(): void
    {
        /**
         * TemplateChild.php refuses to load unless IS_ADMIN_FLAG is true, and
         * UnitTestBootstrap defines it as false when it is still missing, so the
         * admin value has to be established first.
         */
        if (!defined('IS_ADMIN_FLAG')) {
            define('IS_ADMIN_FLAG', true);
        }
        UnitTestBootstrap::initialize();
        require_once DIR_FS_CATALOG . 'includes/classes/FileSystem.php';
        require_once DIR_FS_CATALOG . 'includes/classes/TemplateChild.php';
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->workDir = sys_get_temp_dir() . '/zc-template-child-' . bin2hex(random_bytes(6));
        mkdir($this->workDir, 0755, true);
        unset($GLOBALS[self::MARKER]);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->workDir . '/*') ?: [] as $file) {
            @unlink($file);
        }
        @rmdir($this->workDir);
        unset($GLOBALS[self::MARKER]);
        parent::tearDown();
    }

    /**
     * Values a store owner can legitimately type, plus the shapes that break a
     * naive single-quote-only escaper. A trailing backslash is the important one:
     * '\' . "'" emits \\' , which closes the literal and opens code position.
     */
    public static function templateNamePayloadProvider(): array
    {
        $marker = self::MARKER;

        return [
            'plain text' => ['My Child Template'],
            'ampersand' => ['Bob & Sons Theme'],
            'apostrophe' => ["O'Brien's Theme"],
            'double quote' => ['The "Big" Theme'],
            'angle brackets' => ['<b>Bold</b> Theme'],
            'single trailing backslash' => ['Theme\\'],
            'double trailing backslash' => ['Theme\\\\'],
            'embedded backslashes' => ['a\\\\b\\c'],
            'backslash then quote' => ["Theme\\'"],
            'php close tag' => ['Theme ?> text'],
            'newline' => ["Line one\nLine two"],
            'break-out attempt' => ['\\\'; $GLOBALS[\'' . $marker . '\'] = \'HIT\'; //'],
            'break-out via comment' => ['\\\'; /* ' . $marker . ' */ $GLOBALS[\'' . $marker . '\'] = \'HIT\'; //'],
            /**
             * The payload above carries apostrophes of its own, so a broken escaper
             * yields unparseable PHP and the lint assertion is what catches it. This
             * one is deliberately quote-free after the break-out, so a broken escaper
             * produces a file that parses cleanly and runs — the only shape in which
             * execution itself can be observed.
             */
            'break-out that parses cleanly' => ['\\\'; $' . self::EXECUTION_SENTINEL . ' = 1; //'],
        ];
    }

    #[DataProvider('templateNamePayloadProvider')]
    public function testGeneratedTemplateInfoRoundTripsValueAndExecutesNothing(string $payload): void
    {
        $escaped = $this->sanitize($payload);
        $file = $this->workDir . '/template_info_probe.php';

        /**
         * The assignment form matters: createChildTemplateInfo() emits
         * "$template_name = '<value>';", so anything that escapes the literal lands in
         * code position and runs. A "return '<value>';" probe would short-circuit past
         * an injected statement and could never observe it.
         */
        file_put_contents($file, "<?php\n\$template_name = '" . $escaped . "';\n");

        $this->assertFileParses($file, $payload);

        $template_name = null;
        ${self::EXECUTION_SENTINEL} = null;
        require $file;

        /**
         * Execution is asserted before the round trip on purpose. A successful break-out
         * also truncates the stored value, so the round-trip assertion would catch it
         * either way — but it reports a confusing string diff, where this reports the
         * actual problem.
         */
        $this->assertNull(
            ${self::EXECUTION_SENTINEL},
            'A supplied value escaped its string literal and executed in the generated template_info.php.'
        );
        $this->assertArrayNotHasKey(
            self::MARKER,
            $GLOBALS,
            'A supplied value reached code position in the generated template_info.php.'
        );
        $this->assertSame(
            $payload,
            $template_name,
            'The generated template_info.php must reproduce the administrator-supplied value byte for byte.'
        );
    }

    #[DataProvider('templateNamePayloadProvider')]
    public function testGeneratedManifestRoundTripsValueAndExecutesNothing(string $payload): void
    {
        $manifest = [
            'pluginName' => $payload,
            'pluginDescription' => 'Child of something. ' . $payload,
            'pluginVersion' => 'v1.0.0',
            'template' => [
                'key' => 'child_theme',
                'baseTemplate' => 'parent_theme',
            ],
        ];

        $file = $this->workDir . '/manifest_probe.php';
        file_put_contents($file, $this->buildManifestSource($manifest));

        $this->assertFileParses($file, $payload);

        $written = require $file;

        $this->assertSame(
            $manifest,
            $written,
            'The generated manifest.php must reproduce the parent manifest with the child values substituted.'
        );
        $this->assertArrayNotHasKey(
            self::MARKER,
            $GLOBALS,
            'A supplied value reached code position in the generated manifest.php.'
        );
    }

    /**
     * Nested arrays, booleans, integers and nulls all survive the round trip, since
     * arrayToPhp() re-emits a parent manifest wholesale.
     */
    public function testGeneratedManifestPreservesScalarTypesAndNesting(): void
    {
        $manifest = [
            'pluginName' => 'Child Theme',
            'pluginVersion' => 'v1.0.0',
            'loadOrder' => 20,
            'removesUnencapsulatedVersion' => false,
            'isEnabled' => true,
            'optional' => null,
            'template' => [
                'key' => 'child_theme',
                'nested' => [
                    'deeper' => 'value',
                ],
            ],
        ];

        $file = $this->workDir . '/manifest_types.php';
        file_put_contents($file, $this->buildManifestSource($manifest));

        $this->assertFileParses($file, 'typed manifest');

        $this->assertSame($manifest, require $file);
    }

    /**
     * Floats are a deliberate, documented restriction rather than an oversight:
     * no manifest key is of type double. This test pins that decision so the
     * failure stays an explicit error instead of silently emitting a lossy literal.
     *
     * @see https://github.com/zencart/zencart/pull/8051 finding M3
     */
    public function testGeneratedManifestRejectsFloatValues(): void
    {
        $child = $this->newTemplateChild();
        $method = new \ReflectionMethod($child, 'arrayToPhp');
        $error = false;

        $warnings = [];
        set_error_handler(
            static function (int $severity, string $message) use (&$warnings): bool {
                $warnings[] = $message;
                return true;
            },
            E_USER_WARNING
        );

        try {
            $result = $method->invokeArgs($child, ['', ['someRatio' => 1.5], 4, &$error]);
        } finally {
            restore_error_handler();
        }

        $this->assertTrue($error, 'A float manifest value must be reported as an error, not emitted.');
        $this->assertSame('', $result, 'No manifest source may be produced once a float is encountered.');
        $this->assertCount(1, $warnings, 'The rejection must be logged so the cause is visible in the logs.');
        $this->assertStringContainsString('double', $warnings[0]);
        $this->assertStringContainsString('someRatio', $warnings[0], 'The log must name the offending manifest key.');
    }

    /**
     * Manifest keys are interpolated into the generated source without passing through
     * the escaper, so a key carrying an apostrophe produces a broken child manifest.
     *
     * The assertion below is the contract once that is fixed; remove the skip then.
     *
     * @see https://github.com/zencart/zencart/pull/8051 finding O3
     */
    public function testGeneratedManifestEscapesKeysAsWellAsValues(): void
    {
        $this->markTestSkipped(
            'Open finding O3: arrayToPhp() interpolates keys raw (TemplateChild.php:418). '
            . 'Remove this skip when the keys are escaped too.'
        );

        // phpcs:disable — reached once O3 is fixed.
        $manifest = ["awkward'key" => 'value'];
        $file = $this->workDir . '/manifest_keys.php';
        file_put_contents($file, $this->buildManifestSource($manifest));

        $this->assertFileParses($file, "awkward'key");
        $this->assertSame($manifest, require $file);
    }

    /**
     * Builds the manifest source exactly as createChildManifest() does, so the test
     * exercises the real emitter rather than a copy of it.
     */
    private function buildManifestSource(array $manifest): string
    {
        $child = $this->newTemplateChild();
        $method = new \ReflectionMethod($child, 'arrayToPhp');
        $error = false;

        $body = $method->invokeArgs($child, ['', $manifest, 4, &$error]);

        $this->assertFalse($error, 'arrayToPhp() reported an error while building the fixture manifest.');

        return "<?php\nreturn [\n" . $body . "];\n";
    }

    private function sanitize(string $value): string
    {
        $child = $this->newTemplateChild();
        $method = new \ReflectionMethod($child, 'sanitizeSingleQuotes');

        return $method->invoke($child, $value);
    }

    /**
     * TemplateChild's constructor needs a PluginManager and a database; none of the
     * generation helpers under test touch either.
     */
    private function newTemplateChild(): TemplateChild
    {
        return (new ReflectionClass(TemplateChild::class))->newInstanceWithoutConstructor();
    }

    /**
     * A generated file that does not parse is itself a defect: no administrator-supplied
     * value should be able to produce unparseable PHP. Linting in a separate process
     * keeps a syntax error from aborting the test run.
     */
    private function assertFileParses(string $file, string $payload): void
    {
        $command = escapeshellarg(PHP_BINARY) . ' -n -l ' . escapeshellarg($file) . ' 2>&1';
        $output = (string)shell_exec($command);

        $this->assertStringContainsString(
            'No syntax errors',
            $output,
            'Generated PHP does not parse for payload ' . var_export($payload, true) . ': ' . trim($output)
        );
    }
}
