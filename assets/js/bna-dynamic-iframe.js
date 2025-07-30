jQuery(function ($) {
    let reloadTimer = null;

    const inputFields = [
        '#billing_email',
        '#billing_first_name',
        '#billing_last_name',
        '#billing_postcode',
        '#billing_birth_date',
        '#billing_phone',
        '#billing_street_name',
        '#billing_street_number',
        '#billing_city'
    ];

    const changeFields = [
        '#billing_phone_code',
        '#billing_country',
        '#billing_state'
    ];

    // Simple email regex
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    // Simple phone regex (allows + and digits only)
    const phoneRegex = /^\+?\d{4,}$/;

    function isValidField(field, value) {
        switch (field) {
            case '#billing_email':
                return value === '' || emailRegex.test(value);
            case '#billing_phone':
                return value === '' || phoneRegex.test(value);
            case '#billing_birth_date':
                return value === '' || /^\d{4}-\d{2}-\d{2}$/.test(value); // format YYYY-MM-DD
            default:
                return true;
        }
    }

    function validateForm() {
        let isValid = true;

        inputFields.forEach(selector => {
            const $el = $(selector);
            const value = $el.val();
            if (!isValidField(selector, value)) {
                $el.addClass('bna-invalid');
                isValid = false;
            } else {
                $el.removeClass('bna-invalid');
            }
        });

        return isValid;
    }

    function reloadBnaIframe() {
        if (!validateForm()) {
            console.warn('Validation failed, iframe not reloaded.');
            return;
        }

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
            billing_state: $('#billing_state').val() || ''
        };

        $.post(bna_iframe_data.ajax_url, data, function (response) {
            $('#bna-iframe-wrapper').html(response);
        });
    }

    function debounceReload() {
        clearTimeout(reloadTimer);
        reloadTimer = setTimeout(reloadBnaIframe, 600);
    }

    $(inputFields.join(', ')).on('input', debounceReload);
    $(changeFields.join(', ')).on('change', debounceReload);

    reloadBnaIframe();
});
