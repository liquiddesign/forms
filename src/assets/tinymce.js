function initTinyMCExconf(selector, conf_override) {

	if (typeof conf_override['variables'] === "undefined") {
		conf_override['variables'] = [];
	}

	if (typeof conf_override['templates'] === "undefined") {
		conf_override['templates'] = [];
	}

	if (typeof conf_override['insertcontent'] === "undefined") {
		conf_override['insertcontent'] = false;
	}

	if (typeof conf_override['save'] === "undefined") {
		conf_override['save'] = false;
	}

	if (typeof conf_override['saveLink'] === "undefined") {
		conf_override['save'] = false;
	}

	if (typeof conf_override['contentCss'] === "undefined") {
		conf_override['contentCss'] = [];
	}

	conf_override['contentCss'].map(function(el) {
		return baseUrl + '/' + el;
	});
	conf_override['contentCss'].push(nodeUrl + '/normalize.css/normalize.css');
	conf_override['contentCss'].push(nodeUrl + '/@fortawesome/fontawesome-free/css/all.css');

	// Default confifuration for tinymce
	let tiny_default_config = {
		selector: selector,
		schema: 'html5',
		verify_html: false,
		theme: "modern",
		menubar: false,
		branding: false,
		entity_encoding : "named", //vyjmenované entity nahrazuj
		entities : "160,nbsp", //tohle jsou ty vyjmenované entity, jinak nám to maze nbsp z kodu
		remove_script_host : true,
		document_base_url : baseUrl+"/",
		object_resizing : 'img',
		code_dialog_width: 800,
		code_dialog_height: 450,
		toolbar_items_size: 'small',
		plugins: [
			"fullscreen lqdnoneditable preventdelete autolink link filemanager image lists charmap print preview hr anchor pagebreak",
			"searchreplace wordcount visualblocks visualchars code ",
			"insertdatetime nonbreaking table contextmenu colorpicker fontawesome5 responsivefilemanager",
			"template paste textcolor insertcontent",
			+ conf_override['insertcontent'] ? 'insertcontent' : '',
			+ conf_override['save'] ? 'save' : ''
		],
		save_onsavecallback: function (editors = tinymce.activeEditor.selection.getNode().id) {
			var data = new FormData();
			data.append("content", editors.getContent());
			data.append("lang", editors.id.slice(-2));
			data.append("editor", editors.id);

			fetch(conf_override['saveLink'], {
				method: "POST",
				body: data
			}).then(function(res){ });

		},
		image_advtab : true,
		relative_urls :false,
		filemanager_crossdomain: true,
		filemanager_title: "Správce souborů",
		external_filemanager_path: extHomeUrl + "/assets/filemanager/",
		external_plugins: {
			"filemanager" : extHomeUrl + "/assets/filemanager/plugin.min.js",
			"responsivefilemanager" : extHomeUrl + "/assets/responsivefilemanager/plugin.min.js",
			"fontawesome5" : extHomeUrl + "/assets/fontawesome5/plugin.min.js",
			"insertcontent" : extHomeUrl + "/assets/insertcontent/plugin.js",
			"lqdnoneditable" : extHomeUrl + "/assets/noneditable/plugin.min.js",
			"preventdelete" : extHomeUrl + "/assets/preventdelete/plugin.min.js",
			"variables" : extHomeUrl + "/assets/variables/plugin.js",
		},
		file_browser_callback_types: 'file image',

		toolbar1: "undo redo | styleselect | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | " +
			"bullist numlist | subscript superscript | forecolor backcolor | table | link unlink anchor removeformat | ",
		toolbar2: "responsivefilemanager image | hr charmap nonbreaking fontawesome5 variables | copy cut paste pastetext insertcontent | visualblocks visualchars | template deleteLayout code save fullscreen",


		language_url: extHomeUrl + '/assets/tinymce-langs/cs.js',
		content_css: conf_override['contentCss'],
		style_formats: [
			{title: 'Nadpis 1', block: 'h1',  attributes : {'class' : ''}},
			{title: 'Nadpis 2', block: 'h2',  attributes : {'class' : ''}},
			{title: 'Nadpis 3', block: 'h3',  attributes : {'class' : ''}},
			{title: 'Nadpis 4', block: 'h4',  attributes : {'class' : ''}},
			{title: 'Nadpis 5', block: 'h5',  attributes : {'class' : ''}}, //vyrazuju z configu, nebudeme pouzivat
			{title: 'Perex', block: 'p',  attributes : {'class' : 'perex'}},
			{title: 'Odstavec [výchozí]', block: 'p',  attributes : {'class' : ''}},
			{title: 'Citace', block: 'p',  attributes : {'class' : 'cite'}},
			{title: 'Citace-medium', block: 'p',  attributes : {'class' : 'cite medium'}},
			{title: 'Citace-great', block: 'p',  attributes : {'class' : 'cite great'}},
			{title: 'Menší písmo', inline: 'small'},
			{title: 'Highlight--green', inline : 'span', classes : 'highlight--green'},
			{title: 'Highlight--red', inline : 'span', classes : 'highlight--red'},
		],
		image_class_list: [
			{title: 'Žádný', value: ''},
			{title: 'Non-responsive', value: 'non-responsive'}
		],
		link_class_list: [
			{title: 'Žádné', value: ''},
			{title: 'LINK', value: 'link'},
			{title: 'Button - primary', value: 'btn'},
			{title: 'Button - secondary', value: 'btn btn-secondary'},
			// {title: 'Shadow hover', value: 'shadow-hover'},
		],
		table_class_list: [
			{title: 'Žádná', value: ''},
			{title: 'Řádky', value: 'lines'},
		],

		rel_list: [
			{title: 'Žádné', value: ''},
			{title: 'Lightbox', value: 'lightbox'},
			{title: 'No-follow', value: 'nofollow'},
		],

		noneditable_leave_contenteditable: true, // Nastavi noneditable elementy
		keep_styles: false, // vyresetuje nastaveny style po stisknuti ENTER
		noneditable_noneditable_class: "mceNonEditable", //comma separated class list
		templates: conf_override['templates'], // load tiny tempaltes from DB,
		forced_root_block : 'p',
		remove_trailing_brs: false,
		paste_text_sticky : true,
		paste_as_text: true,
		valid_elements : "@[style],a[href|target],strong/b,em/i,span,br[*],p,*[*]",
		valid_children: '+p[br],+p[span],+p[script]',
		extended_valid_elements: 'span[class|style],iframe[src|style|width|height|scrolling|marginwidth|marginheight|frameborder|allowfullscreen],script[src|async|charset]',
		setup: function (editor) {
			editor.addButton('deleteLayout', {
				icon : 'awesome fas fa-trash-alt',
				tooltip: "Smazat layout",
				onclick: function () {
					const element = tinymce.activeEditor.selection.getNode();
					const component = element.closest('.tiny-component');
					if (component) {
						const paragraphTag = document.createElement('p');
						const breakTag = document.createElement('br');
						paragraphTag.appendChild(breakTag);
						console.log(paragraphTag);
						component.replaceWith(paragraphTag);
					}
				}
			});
		},
	};

	// Override configuration if needed
	if (conf_override !== undefined){
		for (let propname in conf_override){
			tiny_default_config[propname] = conf_override[propname];
		}
	}

	// Tiny initialization
	tinymce.init(tiny_default_config);
}
