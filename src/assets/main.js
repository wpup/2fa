(function ($) {

    // Hide or show QR barcode if enabled checkbox is checked.
    $('#2fa_enabled').on('change', function () {
        if (!$(this).is(':checked')) {
            $('.2fa_qr').hide();
            return;
        }
        $('.2fa_qr').toggle();
    });

})(window.jQuery);