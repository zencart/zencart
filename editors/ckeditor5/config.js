let myCKEditorConfig = {
    // For complete reference see:
    // https://ckeditor.com/docs/ckeditor5/latest/api/module_core_editor_editorconfig-EditorConfig.html

    // If you have a paid commercial license for premium features, enter the key here, and uncomment the line:
    // licenseKey: '<YOUR_LICENSE_KEY>',


    //// IMPORTANT NOTE: every section enabled here will replace the "entire" matching section from the master configuration
    ////      ... therefore, if there is something you wish to adjust in a small way, copy that complete section from the
    ////          master ckeditor.php editorConfig defaults and then make your alterations to that.


    // https://ckeditor.com/docs/ckeditor5/latest/getting-started/setup/menubar.html
    menuBar: {
        isVisible: true
    },

    // Custom toolbar configurations can be added below.
    //
    // Beware: defining 'toolbar' will completely replace the already-configured toolbar, so you must define a complete toolbar set.
    // toolbar: {
    // },

    // https://ckeditor.com/docs/ckeditor5/latest/features/font.html
    // fontFamily: {
    //     options: [
    //         'default',
    //         'Ubuntu, Arial, sans-serif',
    //         'Ubuntu Mono, Courier New, Courier, monospace'
    //     ],
    // }
    // ,

    // Limit to certain font size choices, or expand available options
    fontSize: {
        options: [
            8, 9, 10, 11, 12, 'default', 13, 14, 15, 16, 17, 18, 19, 20, 22, 24, 26, 28, 36, 48, 72,
        ],

        // Allow all font sizes including those that are unknown to CKEditor.
        supportAllValues: true
    },


    // https://ckeditor.com/docs/ckeditor5/latest/features/html/general-html-support.html
    // htmlSupport: {
    //     allow: [ /* HTML features to allow. */ ],
    //     disallow: [ /* HTML features to disallow. */ ]
    // }

    // findAndReplace: {
    //     uiType: 'dropdown'
    // },

    // https://ckeditor.com/docs/ckeditor5/latest/features/style.html
    // style: {
    //     definitions: [
            // Styles definitions.
            // ...
            // {
            //     name: 'Article category',
            //     element: 'h3',
            //     classes: [ 'category' ]
            // },
            // {
            //     name: 'Info box',
            //     element: 'p',
            //     classes: [ 'info-box' ]
            // },
        // ]
    // },

    // https://ckeditor.com/docs/ckeditor5/latest/features/link.html
    // link: {
    //     defaultProtocol: 'https://',
    //     allowedProtocols: [ 'https?', 'tel', 'sms', 'mailto'],
    //     decorators: {
    //         openInNewTab: {
    //             mode: 'manual',
    //             label: 'Open in a new tab',
    //             attributes: {
    //                 target: '_blank',
    //                 rel: 'noopener noreferrer'
    //             }
    //         }
    //     }
    // },

    // https://ckeditor.com/docs/ckeditor5/latest/features/tables/tables-styling.html
    // table: {
    //     contentToolbar: [
    //         'tableColumn', 'tableRow', 'mergeTableCells',
    //         'tableProperties', 'tableCellProperties',
    //     ],
    //
    //     tableProperties: {
    //         // The configuration of the TableProperties plugin.
    //         // ...
    //     },
    //
    //     tableCellProperties: {
    //         // The configuration of the TableCellProperties plugin.
    //         // ...
    //     }
    // },

}
