tinymce.init({
	selector: "[data-editable_type=text]",
	language: "ru",
	inline: true,
	plugins: [
		"advlist autolink lists link image charmap anchor",//preview  print
		"visualblocks code ",//fullscreen
		"media table contextmenu paste moxiemanager textcolor"//
	],
	toolbar2: "bold italic underline | forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist  | table | removeformat | save",
	toolbar1: "undo redo | styleselect fontselect fontsizeselect | link unlink anchor image media code",
	menubar: false,
	style_formats: [
		{title: "Заголовок", block: "h2"},
		{title: "Example", block: "div", classes: "example"},
	],
	//content_css : "/templates/css/tinymce.css",
	relative_urls: false,
	setup: function(editor) {
		save(editor);
	}
});
tinymce.init({
	selector: "[data-editable_type=str]",
	inline: true,
	toolbar: "undo redo | save",
	menubar: false,
	setup: function(editor) {
		save(editor);
	}
});

function save(editor) {
	editor.addButton('save', {
		text: 'SAVE',
		icon: false,
		onclick: function() {
			var edit = $('.mce-edit-focus').data('editable_module'),
				content = tinymce.activeEditor.getContent();
			$('.mce-edit-focus').blur();
			$.post(
				'/ajax.php?file=editable',
				{'content':content,'edit':edit},
				function(data){
					if (data!=1) alert(data);
				}
			).fail(function() {
				alert('Нет соединения!');
			});
		}
	});
}