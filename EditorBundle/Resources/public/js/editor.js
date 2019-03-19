(function ($, tinymce, editorUploadPath) {

    const mceSettings = {
        branding: false,
        selector: '.tinymce',
        plugins: 'image imagetools link lists paste wordcount',
        relative_urls: false,
        convert_urls: false,
        height: 480,
        menubar: 'edit insert view format tools',

        toolbar: "undo redo | styleselect | paste | bold italic | alignleft"
            + "aligncenter alignright alignjustify | "
            + "bullist numlist outdent indent | link image",

        image_caption: true,
        images_upload_url: editorUploadPath,
        images_upload_credentials: true,
        image_advtab: true,
        image_title: true,

        style_formats_merge: true,
        style_formats: [{
            title: 'Image Left', selector: 'img, figure', styles: {
                'float': 'left',
                'margin': '0 10px 0 10px'
            }
        }, {
            title: 'Image Right', selector: 'img, figure', styles: {
                'float': 'right',
                'margin': '0 10px 0 10px'
            }
        }]
    };


    function tinyMceSetup() {
        tinymce.on('AddEditor', function (e) {
            let editor = tinymce.get(e.editor.id);
            editor.on("change", function (e) {
                editor.save();
            });
        });
        tinymce.init(mceSettings);
    }

    $(document).ready(function(){
        tinyMceSetup();
    });

})(jQuery, tinymce, editorUploadPath);
