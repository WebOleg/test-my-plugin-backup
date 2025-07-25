jQuery(function($) {
    let reloadTimer;

    function reloadBnaIframe() {
        const data = {
            action: 'load_bna_iframe',
            nonce: bna_iframe_data.nonce,
            billing_email: $('#billing_email').val(),
            billing_first_name: $('#billing_first_name').val(),
            billing_last_name: $('#billing_last_name').val(),
            billing_postcode: $('#billing_postcode').val(),
            billing_birth_date: $('#billing_birth_date').val()
        };

        $.post(bna_iframe_data.ajax_url, data, function(response) {
            $('#bna-iframe-wrapper').html(response);
        });
    }

    function debounceReload() {
        clearTimeout(reloadTimer);
        reloadTimer = setTimeout(reloadBnaIframe, 600);
    }

    $('#billing_email, #billing_first_name, #billing_last_name, #billing_postcode, #billing_birth_date')
        .on('input', debounceReload);

    reloadBnaIframe();
});
