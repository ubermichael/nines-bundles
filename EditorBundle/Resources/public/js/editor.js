(function ($, tinymce, editorUploadPath) {

    function tinyMceSetup() {
        tinymce.on('AddEditor', function (e) {
            let editor = tinymce.get(e.editor.id);
            editor.on("change", function (e) {
                editor.save();
            });
        });
        tinymce.init(getTinyMceConfig(editorUploadPath));
    }

    $(document).ready(function(){
        tinyMceSetup();
    });

})(jQuery, tinymce, editorUploadPath);
