jQuery(function($) {

    let reloadTimer;

    function reloadBnaIframe() {
        const data = {
            action: 'load_bna_iframe',
            nonce: bna_iframe_data.nonce,
            billing_email: $('#billing_email').val() || '',
            billing_first_name: $('#billing_first_name').val() || '',
            billing_last_name: $('#billing_last_name').val() || '',
            billing_postcode: $('#billing_postcode').val() || '',
            billing_birth_date: $('#billing_birth_date').val() || '',
            billing_phone: $('#billing_phone').val() || '',
            billing_phone_code: $('#billing_phone_code').val() || '',
            billing_street_name: $('#billing_street_name').val() || '',
            billing_street_number: $('#billing_street_number').val() || '',
            billing_city: $('#billing_city').val() || '',
            billing_country: $('#billing_country').val() || '',
            billing_state: $('#billing_state').val() || '',
        };

        $.post(bna_iframe_data.ajax_url, data, function(response) {
            $('#bna-iframe-wrapper').html(response);
        });
    }

    function debounceReload() {
        clearTimeout(reloadTimer);
        reloadTimer = setTimeout(reloadBnaIframe, 600);
    }

    $('#billing_email, #billing_first_name, #billing_last_name, #billing_postcode, #billing_birth_date, #billing_phone, #billing_street_name, #billing_street_number, #billing_city')
        .on('input', debounceReload);

    $('#billing_phone_code, #billing_country, #billing_state').on('change', debounceReload);

    reloadBnaIframe();
});
