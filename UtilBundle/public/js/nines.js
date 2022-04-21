(function ($, window, document) {

    const hostname = window.location.hostname.replace('www.', '');

    function confirm() {
        const $this = $(this);
        $this.click(function () {
            return window.confirm($this.data('confirm'));
        });
    }

    function link() {
        if(this.hostname.replace('www.', '') === hostname) {
            return;
        }
        $(this).attr('target', '_blank');
    }

    function windowBeforeUnload(e) {
        let clean = true;
        $('form').each(function () {
            const $form = $(this);
            if ($form.data('dirty')) {
                clean = false;
            }
        });
        if (!clean) {
            let message = 'You have unsaved changes.';
            e.returnValue = message;
            return message;
        }
    }

    function formDirty() {
        const $form = $(this);
        $form.data('dirty', false);
        $form.on('change', function () {
            $form.data('dirty', true);
        });
        $form.on('submit', function () {
            $(window).unbind('beforeunload');
        });
    }

    function formPopup(e) {
        e.preventDefault();
        const url = $(this).prop('href');
        window.open(url, "_blank", "toolbar=no,scrollbars=yes,resizable=yes,top=60,left=60,width=500,height=600");
    }

    function simpleCollection() {
        $('.collection-simple').collection({
            init_with_n_elements: 1,
            allow_up: false,
            allow_down: false,
            preserve_names: true,
            max: 400,
            add: '<a href="#" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-plus"></span></a>',
            remove: '<a href="#" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-minus"></span></a>',
            add_at_the_end: false,
        });
    }

    function complexCollection() {
        $('.collection-complex').collection({
            init_with_n_elements: 1,
            allow_up: false,
            allow_down: false,
            preserve_names: true,
            max: 400,
            add: '<a href="#" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-plus"></span></a>',
            remove: '<a href="#" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-minus"></span></a>',
            add_at_the_end: true,
            after_add: function(collection, element){
                $(element).find('.select2entity').select2entity();
                $(element).find('.select2-container').css('width', '100%');
                return true;
            },
        });
    }

    function imageModals() {
        $('#imgModal').on('show.bs.modal', function (event) {
            const $button = $(event.relatedTarget);
            // Button that triggered the modal
            const $modal = $(this);

            $modal.find('#modalTitle').text($button.data('title'));
            $modal.find('figcaption').html($button.parent().parent().find('.caption').clone());
            $modal.find("#modalImage").attr('src', $button.data('img'));
        });
    }

    function configureSelect2() {
        // when one select2 element is opening, all others must close
        // or the focus thing fails.
        $(document).on('select2:opening', function(e) {
            $('.select2entity').select2('close');
        });
    }

    $(document).ready(function () {
        $(window).bind('beforeunload', windowBeforeUnload);
        $('form').each(formDirty);
        $("a.popup").click(formPopup);
        $("a").each(link);
        $("*[data-confirm]").each(confirm);
        $('[data-toggle="popover"]').popover(); // add this line to enable boostrap popover
        if (typeof $().collection === 'function') {
            simpleCollection();
            complexCollection();
        }
        imageModals();
        configureSelect2();
    });

})(jQuery, window, document);
