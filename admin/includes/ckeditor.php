<?php

/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2010 Kuroi Web Design
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  Modified 2024-07-01 $
 *
 * @var language $lng
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

// prepare list of languages supported by this store

// backward compatibility
if (!isset($lng)) {
    if (!class_exists('language')) {
        include(DIR_FS_CATALOG . DIR_WS_CLASSES . 'language.php');
    }
    $lng = new language;
}
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

<link title="CKEditorCSS" rel="stylesheet" href="https://cdn.ckeditor.com/ckeditor5/42.0.0/ckeditor5.css">
<style>
    /* for Bootstrap compatibility to prevent interfering with tables */
    .ck-content .table {
        width: auto;
    }
</style>
<script title="CKEditor5CDN" type="importmap">
    {
        "imports": {
            "ckeditor5": "https://cdn.ckeditor.com/ckeditor5/42.0.0/ckeditor5.js",
            "ckeditor5/": "https://cdn.ckeditor.com/ckeditor5/42.0.0/"
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
// console.log(editorElements);

    import {
        Alignment,
        Autoformat,
        AutoImage,
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
        TableProperties,
        TableCellProperties,
        TableToolbar,
        TextPartLanguage,
        Underline,
        Undo
    } from 'ckeditor5';

    <?php
    // import translations needed
    foreach ($langArray as $langCode) {
        echo "import " . $langCode . "Translation from 'ckeditor5/translations/" . $langCode . ".js';";
    }
    ?>

    const editorConfig = {
        plugins: [
            Essentials, Font, Bold, Italic, Underline, Strikethrough, Subscript, Superscript, Code,
            Clipboard, PasteFromOffice, Autoformat, PasteFromMarkdownExperimental, FindAndReplace,
            CodeBlock, Heading, Paragraph, Undo, BlockQuote, Indent, IndentBlock, List, SelectAll,
            Alignment, GeneralHtmlSupport, Style, SourceEditing, MediaEmbed, TextPartLanguage, ShowBlocks,
            SpecialCharacters, SpecialCharactersEssentials, Table, TableToolbar, TableProperties, TableCellProperties,
            Image, AutoImage, ImageInsert, ImageToolbar, ImageCaption, ImageStyle, ImageResize, LinkImage
        ],
        toolbar: {
            items: [
                'undo', 'redo', 'findAndReplace',
                '|', 'link', 'insertImage', 'mediaEmbed', 'insertTable',
                '|', 'heading', 'fontsize', /* 'fontfamily', */ 'specialCharacters',
                '|', 'showBlocks', 'sourceEditing',
                '-', 'bold', 'italic', 'underline', 'strikethrough', 'superscript', 'subscript', 'code',
                '|', 'numberedList', 'bulletedList', 'indent', 'outdent', 'blockQuote', 'alignment',
                '|', 'fontColor', 'fontBackgroundColor',
            ],
            shouldNotGroupWhenFull: true
        },

        translations: [
            <?php
            foreach ($langArray as $langCode) {
                echo $langCode . 'Translation,';
            }
            ?>

        ],

        image: {
            insert: {
                integrations: ['url'/*, 'upload', 'assetManager'*/],

                // If "type" setting is omitted, the editor defaults to 'block'.
                type: 'auto'
            },
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
        }

    };

    const languageConfig = {
        language: {
            ui: sessionLangCode ?? defaultLang ?? 'en',
            content: sessionLangCode ?? 'en'
            // for multiple-language-inputs on one page, this is overridden below when the editor is instantiated
        }
    };

    // Now apply the Editor to each target element
    for (let el of editorElements) {
        let langindex = el.name.match(/\d+/) ?? sessionLangId;
        let langCode = langArray[langindex];

        ClassicEditor
            .create(el,
                // First uses the master config above, then any custom overrides from /editors/ckeditor5/config.js, and then override lang for specific fields
                {...editorConfig, ...languageConfig, ...myCKEditorConfig, ...{language: {content: langCode}}})
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
