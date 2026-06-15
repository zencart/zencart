<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

return (new Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@auto' => true,
        //'@auto:risky' => true,
        '@PER-CS' => true, // PER includes PSR-12
        '@PHP8x5Migration' => true, // includes requirements for prior PHP versions
        'concat_space' => ['spacing' => 'one'],
        'cast_spaces' => ['space' => 'none'],
        'no_alternative_syntax' => true, // avoid using endif style
        'operator_linebreak' => [
            'only_booleans' => true,
        ],
        'blank_line_after_opening_tag' => false,

        //'blank_line_after_opening_tag' => true,
        //'declare_strict_types' => [
        //    'strategy' => 'add_when_missing',
        //],

        'include' => true,
        'echo_tag_syntax' => [
            'format' => 'short',
            'shorten_simple_statements_only' => true,
        ],

        'single_line_comment_spacing' => false,
        'single_line_comment_style' => false,

        'statement_indentation' => true,
        //'statement_indentation' => [
        //    'stick_comment_to_next_continuous_control_statement' => false,
        //],

    ])
    ->setFinder(
        (new Finder())
            ->in(['includes', 'admin', 'zc_install', 'zc_plugins', 'not_for_release'])
            ->exclude([
                'classes/vendors',
            ])
            ->notName([
                'configure.php',

                // Legacy files to ignore
                'paypalwpp.php',
                'paypaldp.php',
                'paypal.php',
                'lang.paypalwpp.php',
                'lang.paypaldp.php',
                'lang.paypal.php',
                'compoundtaxes.php', // legacy test file artifact retained for reference

                'PayPalShippingCarriers.php', // NOWDOC shouldn't be reformatted.

                // dev/distro files to ignore
                'dist-*.php',
                'dev-*.php',

                'class.sfYaml*.php',
            ])
            ->notPath([
                'includes/classes/products.php',


                // PHP-CS-Fixer 3.95.x crashes (TypeError in StatementIndentationFixer)
                // on these switch/HTML-mixed templates. Exclude until fixed upstream.
                'templates/responsive_classic/templates/tpl_index_categories.php',
                'templates/responsive_classic/templates/tpl_shopping_cart_default.php',
                'templates/template_default/templates/tpl_shopping_cart_default.php',
                'templates/template_default/templates/tpl_address_book_process_default.php',
                'templates/template_default/templates/tpl_index_categories.php',
                'templates/template_default/templates/tpl_index_default.php',
                'ResponsiveClassicPlugin/v1.0.0/catalog/includes/templates/responsive_classic_plugin/templates/tpl_index_categories.php',
                'ResponsiveClassicPlugin/v1.0.0/catalog/includes/templates/responsive_classic_plugin/templates/tpl_shopping_cart_default.php',
            ])
            ->ignoreDotFiles(true)
    );
