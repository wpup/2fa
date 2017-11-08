(function ($) {

    // Hide or show QR barcode if enabled checkbox is checked.
    $('#two_fa_enabled').on('change', function () {
        if (!$(this).is(':checked')) {
            $('.two-fa-hidden').hide();
            return;
        }
        $('.two-fa-hidden').toggle();
    });

})(window.jQuery);
