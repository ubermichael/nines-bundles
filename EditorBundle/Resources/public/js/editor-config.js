function getTinyMceConfig(editorUploadPath) {

    return {
        branding: false,
        selector: '.tinymce',
        plugins: 'advlist anchor charmap code help hr image imagetools link ' +
            'lists paste preview searchreplace table wordcount',
        relative_urls: false,
        convert_urls: false,
        height: 320,
        menubar: 'edit insert view format table tools help',

        toolbar: [
            'undo redo | styleselect | pastetext | bold italic | alignleft aligncenter alignright alignjustify | table',
            'bullist numlist | outdent indent | link | charmap | code'],

        browser_spellcheck: true,

        image_caption: true,
        images_upload_url: editorUploadPath,
        images_upload_credentials: true,
        image_advtab: true,
        image_title: true,

        resize: true,
        paste_as_text: true,
        paste_block_drop: true,

        style_formats_merge: true,
        style_formats: [{
                title: 'Image Left',
                selector: 'img, figure',
                styles: {
                    'float': 'left',
                    'margin': '0 10px 0 10px',
                },
            },
            {
                title: 'Image Center',
                selector: 'img, figure',
                styles: {
                    position: 'relative',
                    transform: 'translateX(-50%)',
                    left: '50%',
                },
            },
            {
                title: 'Image Right',
                selector: 'img, figure',
                styles: {
                    'float': 'right',
                    'margin': '0 10px 0 10px',
                },
            },
        ],
    };

}
