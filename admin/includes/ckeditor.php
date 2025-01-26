<?php

/**
 * CKEditor 5
 *
 * Custom config can be set up in /editors/ckeditor5/config.js
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2010 Kuroi Web Design
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Nov 06 Modified in v2.1.0 $
 *
 * @var language $lng
 *
 * Ref: https://ckeditor.com/docs/ckeditor5/latest/getting-started/installation/quick-start.html#installing-ckeditor-5-from-cdn
 * Ref: https://github.com/ckeditor/ckeditor5/releases
 */

// To use a newer version, set the number here. See https://github.com/ckeditor/ckeditor5/releases for latest.
const CKEDITOR_VERSION = '43.3.1';

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

// for compatibility with pre-ZC-v2.0.0 where class is PSR-autoloaded
if (!isset($lng)) {
    if (!class_exists('language')) {
        include(DIR_FS_CATALOG . DIR_WS_CLASSES . 'language.php');
    }
    $lng = new language;
}
// Get an array of languages: [1=>'en', 2=>'fr', etc] to match up textarea ID suffix to know which language the editor should use for that field.
if (method_exists($lng, 'get_language_list')) {
    $langArray = $lng->get_language_list();
} else {
    // fallback for compatibility with pre-ZC-v2.0.0
    $langArray = [];
    foreach ($lng->catalog_languages as $lang) {
        $langArray[$lang['id']] = $lang['code'];
    }
}
?>

<link title="CKEditorCSS" rel="stylesheet" href="https://cdn.ckeditor.com/ckeditor5/<?= CKEDITOR_VERSION ?>/ckeditor5.css">
<style>
    /* for Bootstrap compatibility to prevent interfering with tables */
    .ck-content .table {
        width: auto;
    }
</style>
<script title="CKEditor5CDN" type="importmap">
    {
        "imports": {
            "ckeditor5": "https://cdn.ckeditor.com/ckeditor5/<?= CKEDITOR_VERSION ?>/ckeditor5.js",
            "ckeditor5/": "https://cdn.ckeditor.com/ckeditor5/<?= CKEDITOR_VERSION ?>/"
        }
    }
</script>
<script title="CKEditor-custom-config" src="<?= (function_exists('zen_catalog_base_link') ? zen_catalog_base_link() : '/') . DIR_WS_EDITORS . 'ckeditor5/config.js' ?>"></script>
<script title="CKEditor-initialize" type="module">
    const langArray = <?= json_encode($langArray) ?>;
    const defaultLang = '<?= DEFAULT_LANGUAGE ?>';
    const sessionLangId = '<?= $_SESSION['languages_id'] ?>';
    const sessionLangCode = '<?= $_SESSION['languages_code'] ?>';

    // Get list of textarea fields that have the editorHook class and do not have the noEditor class
    const editorElements = document.querySelectorAll('.editorHook:not(.noEditor)');

    import {
        Alignment,
        Autoformat,
        AutoImage,
        AutoLink,
        BlockQuote,
        Bold,
        ClassicEditor,
        Clipboard,
        Code,
        CodeBlock,
        Essentials,
        FindAndReplace,
        Font,
        GeneralHtmlSupport,
        Heading,
        HorizontalLine,
        HtmlEmbed,
        Image,
        ImageCaption,
        ImageInsert,
        ImageResize,
        ImageStyle,
        ImageToolbar,
        Indent,
        IndentBlock,
        Italic,
        LinkImage,
        List,
        MediaEmbed,
        Paragraph,
        PasteFromMarkdownExperimental,
        PasteFromOffice,
        RemoveFormat,
        SelectAll,
        ShowBlocks,
        SourceEditing,
        SpecialCharacters,
        SpecialCharactersEssentials,
        Strikethrough,
        Style,
        Subscript,
        Superscript,
        Table,
        TableCellProperties,
        TableColumnResize,
        TableProperties,
        TableToolbar,
        TextPartLanguage,
        Underline,
        Undo
    } from 'ckeditor5';

    <?php
    // import translations needed
    foreach ($langArray as $langCode) {
        echo "import " . $langCode . "Translation from 'ckeditor5/translations/" . $langCode . ".js';\n";
    }
    ?>
    const uiLanguages = {
        <?php
    foreach ($langArray as $langCode) {
        echo '"' . $langCode . '": ' . $langCode . 'Translation,';
    }
    ?>
    };


    const editorConfig = {
        plugins: [
            Essentials, Font, Bold, Italic, Underline, Strikethrough, Subscript, Superscript, Code,
            Clipboard, PasteFromOffice, Autoformat, PasteFromMarkdownExperimental, FindAndReplace,
            CodeBlock, Heading, Paragraph, Undo, BlockQuote, Indent, IndentBlock, List, SelectAll,
            HorizontalLine,
            Alignment, GeneralHtmlSupport, HtmlEmbed, Style, SourceEditing, MediaEmbed, TextPartLanguage, ShowBlocks,
            SpecialCharacters, SpecialCharactersEssentials, RemoveFormat,
            Table, TableToolbar, TableProperties, TableCellProperties, TableColumnResize,
            Image, AutoImage, ImageInsert, ImageToolbar, ImageCaption, ImageStyle, ImageResize, LinkImage
        ],
        toolbar: {
            items: [
                'undo', 'redo', 'findAndReplace',
                '|', 'link', 'insertImage', 'mediaEmbed', 'insertTable',
                '|', 'heading', 'fontsize', /* 'fontfamily', */ 'specialCharacters',
                '|', 'showBlocks',
                // 'sourceEditing' may or may not be desired. Do not use if admins might paste content from untrusted sources
                'sourceEditing',
                // using htmlEmbed poses a security risk: do not use if Admins might paste content from untrusted sources; A sanitizer should be configured if htmlEmbed is enabled.
                // 'htmlEmbed',
                '-', 'bold', 'italic', 'underline', 'strikethrough',
                // 'superscript', 'subscript',
                'code',
                '|', 'removeFormat',
                '|', 'numberedList', 'bulletedList', 'indent', 'outdent', 'blockQuote', 'alignment', 'horizontalLine',
                '|', 'fontColor', 'fontBackgroundColor',
            ],
            shouldNotGroupWhenFull: true
        },

        image: {
            insert: {
                integrations: ['url'/*, 'upload', 'assetManager'*/],

                // If "type" setting is omitted, the editor defaults to 'block'.
                type: 'auto'
            },
            // image toolbar layout:
            toolbar: [
                'imageStyle:block',
                'imageStyle:side',
                '|',
                'toggleImageCaption',
                'imageTextAlternative',
                '|',
                'linkImage',
                '|',
                'imageStyle:inline',
                'imageStyle:block',
                'imageStyle:wrapText',
            ],
        },

        link: {
            defaultProtocol: 'https://',
            allowedProtocols: [ 'https?', 'tel', 'sms', 'mailto'],
            decorators: {
                openInNewTab: {
                    mode: 'manual',
                    label: 'Open in a new tab',
                    attributes: {
                        target: '_blank',
                        rel: 'noopener noreferrer'
                    }
                }
            }
        },

        table: {
            contentToolbar: [
                'tableColumn', 'tableRow', 'mergeTableCells',
                'tableProperties', 'tableCellProperties',
            ],
        },
    };


// console.log(editorElements);
    // Loop through the target elements and activate the Editor on each.
    for (const editorElement of editorElements) {

        // Determine language to use for this element's content and the UI
        const contentLangCode = langArray[editorElement.name.match(/\d+/) ?? sessionLangId];
        const langConfig = {
            language: {
                // ui: sessionLangCode,
                content: contentLangCode,
            }
        };

        const translationsConfig = {
            translations: [
                uiLanguages[contentLangCode],
                // uiLanguages[sessionLangCode],
            ]
        };

        // In case the override/custom config.js doesn't load or is not present, fallback to empty object.
        let customConfig = {};
        if (typeof myCKEditorConfig !== 'undefined') {
            customConfig = myCKEditorConfig;
        }

        // Combine configs: First use the master config above, then any custom overrides from /editors/ckeditor5/config.js, and then override language for specific fields
        const currentEditorConfig = {...editorConfig, ...translationsConfig, ...customConfig, ...langConfig};
//console.log(currentEditorConfig);

        // Instantiate this editor instance, before looping to the next one on the page.
        ClassicEditor
            .create(editorElement, currentEditorConfig)
            .then(editor => {
                window.editor = editor;
            })
            .catch(error => {
                // console.error(error.stack);
                console.error(error);
            });
    }
</script>

<?php
// - Edit your /editors/ckeditor5/config.js file to control the toolbar buttons, add additional plugins, etc
