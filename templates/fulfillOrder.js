jQuery('#fulfill-item').click(function(event) {
    event.preventDefault();
    var data = {
        'action': 'fulfill-order',
        'order_id': event.target.dataset.orderId,
        'reference': event.target.dataset.orderReference
    };

    // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
    jQuery.post(ajaxurl, data, function(response) {
        jQuery('.wrap').prepend('<div class="notice notice-success is-dismissible"><p>' + response + '</p></div>');
        jQuery('#fulfill-item').prop('disabled', true);
    }).fail(function(error) {
        jQuery('.wrap').prepend('<div class="notice notice-error is-dismissible"><p>' + error.responseJSON.data + '</p></div>');
    });
});

