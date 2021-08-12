tinymce.PluginManager.add('variables', function(editor) {
    function showDialog() {
        editor.windowManager.open({
            title: "Vložit proměnou",
            name: "variables",
            width: 600,
            url: extHomeUrl + '/assets/variables/dialog.php',
            onSubmit: function() {
            },
        });
    }
    editor.addButton('variables', {
        icon: 'awesome fas fa-dollar-sign',
        tooltip: 'Vložit proměnou',
        onclick: showDialog
    });
});