(function ($) {
    $(document).ready(function () {
        $('.loft1325-card').on('click', '.loft1325-secondary', function () {
            // Placeholder for future interactions.
        });

        $('.loft1325-sync-form').on('submit', function () {
            var $form = $(this);
            var $status = $form.find('.loft1325-sync-status');

            $form.addClass('is-loading');
            $status.text('Synchronisation en cours… Cela peut prendre jusqu\'à 1 minute.');
        });
    });
})(jQuery);
