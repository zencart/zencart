let myTinyMceConfig = {
    // For complete reference see:
    // https://www.tiny.cloud/docs/tinymce/latest/available-toolbar-buttons/
    // https://www.tiny.cloud/docs/tinymce/latest/editor-important-options/

    // Note: The license_key must be set in Admin->Configuration->My Store
    // If a license_key is set in this file, then it will override the store's Admin setting, resulting in potentially unexpected outcomes (feature availability issues, etc). But it may be set here if the desire is to bypass the Admin configuration.

    // custom configuration settings:

    min_height: 300,
    max_height: 750,
    resize: 'both',
    autoresize_bottom_margin: 50,
    autoresize_overflow_padding: 0,

    skin: 'oxide', // 'bootstrap' is a nice choice for subscribers with a valid license key
    icons: 'default', // 'small' icons is a nice choice with a paid license

    plugins: [
        'accordion', 'advlist', 'anchor', 'autolink', 'autoresize', 'autosave',
        'charmap', 'code', 'directionality', 'emoticons', 'fullscreen', 'help',
        'image', 'importcss', 'insertdatetime', 'link', 'lists', 'media',
        'nonbreaking', 'pagebreak', 'preview', 'quickbars', 'save',
        'searchreplace', 'table', 'visualblocks', 'visualchars', 'wordcount',

        //// premium plugins, must have an active premium account, and configure the API license key (not available in GPL mode):
        // 'a11ychecker', 'advcode', 'advtable', 'autocorrect', 'tinymcespellchecker',
        // 'casechange', 'checklist', 'editimage', 'export', 'footnotes',
        // 'formatpainter', 'inlinecss', 'markdown', 'mediaembed',
        // 'pageembed', 'powerpaste', 'tableofcontents', 'typography',
        // 'ai', 'importword', 'exportword', 'exportpdf',
    ],
    toolbar: [
        // not all these options will display if the required plugins aren't loaded above and enabled in your API Key's account
        'undo redo searchreplace formatpainter | link image media editimage pageembed table | forecolor backcolor | code preview fullscreen',
        'bold italic underline removeformat | fontsize lineheight blocks fontfamily', // ltr rtl superscript subscript lineheight fontsizeinput
        'align numlist bullist indent outdent blockquote checklist hr charmap emoticons | spellcheckdialog a11ycheck | aidialog aishortcuts ',
    ],

    //menubar: 'file edit view insert format tools table help',

    relative_urls : false,
    remove_script_host : true,

    plugin_insertdate_dateFormat : "%Y-%m-%d",
    plugin_insertdate_timeFormat : "%H:%M:%S",
    extended_valid_elements : "hr[class|width|size|noshade]",
    file_browser_callback : "fileBrowserCallBack",
    custom_undo_redo_levels : 10,
    paste_use_dialog : false,

    // premium
    mediaembed_max_width: 650,
    mediaembed_inline_styles: true,

    // premium with AI addon
    // For AI configuration instructions see: https://www.tiny.cloud/docs/tinymce/latest/ai/
    // The ai_request setting below must be updated to point to an AI service (proxy) per the docs linked above.
    ai_request: (request, respondWith) => respondWith.string(() => Promise.reject("See docs to implement AI Assistant")),

}
