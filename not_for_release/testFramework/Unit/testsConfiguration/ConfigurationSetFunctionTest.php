<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Unit\testsConfiguration;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\zcUnitTestCase;

/**
 * Covers the parsing/dispatch layer that replaced eval()-based rendering of
 * `set_function`/`use_function` values sourced from the `configuration` table:
 * zen_parse_config_set_function(), zen_render_config_set_function(), and the
 * use_function allowlists (bare function, and object/class/method form).
 */
class ConfigurationSetFunctionTest extends zcUnitTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        require_once DIR_FS_CATALOG . 'includes/functions/functions_strings.php';
        require_once DIR_FS_ADMIN . 'includes/functions/html_output.php';
        require_once DIR_FS_ADMIN . 'includes/functions/general.php';
        require_once DIR_FS_CATALOG . 'includes/functions/functions_taxes.php';

        if (!function_exists('zen_record_admin_activity')) {
            eval('function zen_record_admin_activity($message, $severity): void {}');
        }
        if (!defined('TEXT_NONE')) {
            define('TEXT_NONE', 'None');
        }
        if (!defined('CHARSET')) {
            define('CHARSET', 'utf-8');
        }
    }

    // ---------------------------------------------------------------
    // zen_parse_config_set_function()
    // ---------------------------------------------------------------

    #[DataProvider('parseProvider')]
    public function testParseConfigSetFunction(string $input, ?array $expected): void
    {
        $this->assertSame($expected, \zen_parse_config_set_function($input));
    }

    public static function parseProvider(): array
    {
        return [
            'JSON, no params' => [
                '{"function":"zen_cfg_textarea"}',
                ['function' => 'zen_cfg_textarea', 'params' => []],
            ],
            'JSON, with choices' => [
                '{"function":"zen_cfg_select_option","choices":["0","1"]}',
                ['function' => 'zen_cfg_select_option', 'params' => ['choices' => ['0', '1']]],
            ],
            'JSON, malformed' => ['{"function":', null],
            'JSON, missing function key' => ['{"choices":["0"]}', null],
            'JSON, function not a string' => ['{"function":123}', null],
            'JSON, function name has invalid characters' => ['{"function":"system; id"}', null],
            'JSON, params not an array' => ['{"function":"zen_cfg_textarea","params":"nope"}', null],

            'legacy fragment, no args' => [
                'zen_cfg_textarea(',
                ['function' => 'zen_cfg_textarea', 'params' => []],
            ],
            'legacy fragment, short-array choices' => [
                "zen_cfg_select_option(['0', '1'],",
                ['function' => 'zen_cfg_select_option', 'params' => ['choices' => ['0', '1']]],
            ],
            'legacy fragment, array() choices' => [
                "zen_cfg_select_option(array('0', '1'),",
                ['function' => 'zen_cfg_select_option', 'params' => ['choices' => ['0', '1']]],
            ],
            'legacy fragment, nested array() pairs' => [
                "zen_cfg_select_drop_down(array(array('id'=>'0', 'text'=>'Non-Compliant'), array('id'=>'1', 'text'=>'On')),",
                [
                    'function' => 'zen_cfg_select_drop_down',
                    'params' => ['choices' => [
                        ['id' => '0', 'text' => 'Non-Compliant'],
                        ['id' => '1', 'text' => 'On'],
                    ]],
                ],
            ],
            'legacy fragment, nested short-array pairs' => [
                "zen_cfg_select_drop_down([['id'=>'0', 'text'=>'Blank'], ['id'=>'1', 'text'=>'+']],",
                [
                    'function' => 'zen_cfg_select_drop_down',
                    'params' => ['choices' => [
                        ['id' => '0', 'text' => 'Blank'],
                        ['id' => '1', 'text' => '+'],
                    ]],
                ],
            ],

            'empty string' => ['', null],
            'no function-call shape at all' => ['not a function call', null],
            'missing opening paren' => ['zen_cfg_textarea', null],
            // The regressive bypass a naive prefix/allowlist check would miss:
            // valid-looking prefix, but arbitrary trailing PHP after the choices.
            'trailing-content bypass attempt' => ["zen_cfg_textarea(); system('id'); //", null],
            'trailing-content bypass attempt, after valid array' => ["zen_cfg_select_option(['0'],); system('id');", null],
            'unterminated array, missing closing bracket' => ["zen_cfg_select_option(['0', '1'", null],
        ];
    }

    // ---------------------------------------------------------------
    // zen_get_config_set_function_renderers()
    // ---------------------------------------------------------------

    public function testRendererRegistryDoesNotExposeDangerousFunctions(): void
    {
        $renderers = \zen_get_config_set_function_renderers();

        foreach (['system', 'exec', 'passthru', 'shell_exec', 'eval', 'assert', 'proc_open'] as $dangerous) {
            $this->assertArrayNotHasKey($dangerous, $renderers, "Renderer registry must never expose '$dangerous'");
        }

        // Sanity: it does expose the legitimate core renderers.
        $this->assertArrayHasKey('zen_cfg_textarea', $renderers);
        $this->assertArrayHasKey('zen_cfg_select_option', $renderers);
    }

    // ---------------------------------------------------------------
    // zen_render_config_set_function() -- end-to-end
    // ---------------------------------------------------------------

    public function testRenderConfigSetFunctionJsonForm(): void
    {
        $html = \zen_render_config_set_function('{"function":"zen_cfg_textarea"}', 'hello');
        $this->assertStringContainsString('<textarea', $html);
        $this->assertStringContainsString('hello', $html);
    }

    public function testRenderConfigSetFunctionLegacyForm(): void
    {
        $html = \zen_render_config_set_function('zen_cfg_textarea(', 'hello');
        $this->assertStringContainsString('<textarea', $html);
        $this->assertStringContainsString('hello', $html);
    }

    public function testRenderConfigSetFunctionRejectsUnregisteredFunction(): void
    {
        $this->assertNull(\zen_render_config_set_function('{"function":"system"}', 'x'));
    }

    public function testRenderConfigSetFunctionRejectsTrailingContentBypass(): void
    {
        $this->assertNull(\zen_render_config_set_function("zen_cfg_textarea(); system('id'); //", 'x'));
    }

    public function testRenderConfigSetFunctionRejectsGarbage(): void
    {
        $this->assertNull(\zen_render_config_set_function('not a function call', 'x'));
        $this->assertNull(\zen_render_config_set_function('', 'x'));
    }

    // ---------------------------------------------------------------
    // zen_is_valid_config_use_function() -- bare function form
    // ---------------------------------------------------------------

    #[DataProvider('useFunctionProvider')]
    public function testIsValidConfigUseFunction(string $function, bool $expected): void
    {
        $this->assertSame($expected, \zen_is_valid_config_use_function($function));
    }

    public static function useFunctionProvider(): array
    {
        return [
            'known allowlisted function' => ['zen_get_zone_class_title', true],
            'known allowlisted function, tax class' => ['zen_get_tax_class_title', true],
            'not on the allowlist, though it exists' => ['zen_cfg_get_zone_name', true],
            'bare dangerous function' => ['system', false],
            'bare dangerous function, exec' => ['exec', false],
            'unrelated function not on allowlist' => ['strtoupper', false],
            'empty string' => ['', false],
        ];
    }

    // ---------------------------------------------------------------
    // zen_is_valid_config_use_function_class() / _method() -- object form
    // ---------------------------------------------------------------

    #[DataProvider('useFunctionClassProvider')]
    public function testIsValidConfigUseFunctionClass(string $class, bool $expected): void
    {
        $this->assertSame($expected, \zen_is_valid_config_use_function_class($class));
    }

    public static function useFunctionClassProvider(): array
    {
        return [
            'legitimate class' => ['currencies', true],
            'unregistered class' => ['evilclass', false],
            'path traversal attempt' => ['../../../../tmp/evil', false],
            'path traversal, embedded' => ['currencies/../../../tmp/evil', false],
            'null byte injection' => ["currencies\0.php", false],
            'trailing garbage / injection attempt' => ['currencies; system("id")', false],
            'empty string' => ['', false],
        ];
    }

    /**
     * zen_is_valid_config_use_function_method() keys its allowlist off the
     * *actual* runtime class (via get_class()), so an object of any class
     * other than the allowlisted 'currencies' is rejected outright -- this
     * covers that rejection path with a lightweight stand-in, since the real
     * `currencies` class requires a live $db connection to construct.
     */
    public function testIsValidConfigUseFunctionMethodRejectsNonAllowlistedClass(): void
    {
        $notCurrencies = new class {
            public function format($v)
            {
                return "\$$v";
            }
            public function exec($v)
            {
                return "PWNED: $v";
            }
        };
        $this->assertFalse(\zen_is_valid_config_use_function_method($notCurrencies, 'format'));
        $this->assertFalse(\zen_is_valid_config_use_function_method($notCurrencies, 'exec'));
    }

    public function testUseFunctionClassMethodsAllowlistOnlyPermitsFormatOnCurrencies(): void
    {
        // Documents the intended allowlist shape directly: only 'currencies'
        // -> 'format' is ever legitimate, matching every use_function value
        // found in core/bundled modules ('currencies->format').
        $methods = \zen_get_config_use_function_class_methods();
        $this->assertSame(['format'], $methods['currencies'] ?? null);
    }

    // ---------------------------------------------------------------
    // zen_call_config_use_function() -- integration: unsafe calls degrade
    // to returning the untouched parameter, never invoking the function.
    // ---------------------------------------------------------------

    public function testCallConfigUseFunctionBlocksUnregisteredFunction(): void
    {
        $this->assertSame('untouched', \zen_call_config_use_function('strtoupper', 'untouched'));
    }

    public function testCallConfigUseFunctionAllowsRegisteredFunction(): void
    {
        $this->assertSame('None', \zen_call_config_use_function('zen_get_zone_class_title', '0'));
    }
}
