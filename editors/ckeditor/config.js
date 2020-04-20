CKEDITOR.editorConfig = function(config) {
	// For complete reference see:
	// https://docs.ckeditor.com/#!/api/CKEDITOR.config

	// By default, the editor width equals the width of its container element in the page, while its height is set to 200 pixels.
	//config.width = '100%';
	config.height = 400;


	config.uiColor = '#D8D6CD'; //#D8D6CD

	// The toolbar button arrangement, optimized for two toolbar rows.
	config.toolbar = [
		{ name: 'clipboard', items: [ 'Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo' ] },
		{ name: 'editing', items: [ 'Scayt' ] },
		{ name: 'links', items: [ 'Link', 'Unlink' ] },
		{ name: 'insert', items: [ 'Image', 'Table', 'HorizontalRule', 'SpecialChar', 'Iframe', 'Youtube' ] },
		{ name: 'others', items: [ 'Source' ] },
		{ name: 'about', items: [ 'Maximize', '-', 'About' ] },
		'/',
		{ name: 'basicstyles', items: [ 'Bold', 'Italic', 'Underline', 'Strike', '-', 'RemoveFormat' ] },
		{ name: 'paragraph', items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ] },
		{ name: 'colors' , items: ['TextColor', 'BGColor']},
		{ name: 'styles', items: [ 'Styles', 'Format', 'FontSize' ] }
	];

	config.toolbarCanCollapse = true;

	// Remove some buttons provided by the standard plugins, which are not needed in the Standard toolbar.
	config.removeButtons = 'Subscript,Superscript,Font,Flash';

	// Simplify the dialog windows.
	config.removeDialogTabs = 'image:advanced;link:advanced';

	// List of font-sizes to appear in the font combo box
	config.fontSize_sizes =  '8/8px;9/9px;10/10px;11/11px;12/12px;13/13px;14/14px;15/15px;16/16px;17/17px;18/18px;19/19px;20/20px;22/22px;24/24px;26/26px;28/28px;36/36px;48/48px;72/72px';


	// Set the most common block elements.
	config.format_tags = 'p;h1;h2;h3;pre';

	// Setting to allowedContent 'true' stops all content-filtering
	// Disabling content-filtering here in order to allow inserting CSS styles inside CKEditor
	config.allowedContent = true;
    // config.extraAllowedContent: 'h3{clear};h2{line-height};h2 h3{margin-left,margin-top}',

	// AutoParagraph inserts markup when you add new lines. Setting this to false can cause usability problems and invalid html markup in your page.
	//  config.autoParagraph = false;

	// Enable additional plugins
	// If you add more, you may need to also add them to the config.toolbar menu above.
	config.extraPlugins = 'font,justify,colorbutton,iframe';

	config.skin = 'moono'; // kama, moono, moono-lisa
};
