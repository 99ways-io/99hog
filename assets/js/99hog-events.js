jQuery(document).ready(function($) {
    if (typeof posthog === 'undefined') {
        return;
    }

    // Track begin_checkout
    if ($('body').hasClass('woocommerce-checkout') && typeof ninetynine_hog_checkout_data !== 'undefined') {
        posthog.capture('begin_checkout', {
            currency: ninetynine_hog_checkout_data.currency,
            value: ninetynine_hog_checkout_data.value,
            items: ninetynine_hog_checkout_data.items
        });
    }

    // Track add_shipping_info
    $(document.body).on('updated_checkout', function() {
        var shipping_method = $('input[name^="shipping_method"]:checked').val();
        if (shipping_method && typeof ninetynine_hog_checkout_data !== 'undefined') {
            posthog.capture('add_shipping_info', {
                currency: ninetynine_hog_checkout_data.currency,
                value: ninetynine_hog_checkout_data.value,
                items: ninetynine_hog_checkout_data.items,
                shipping: shipping_method
            });
        }
    });

    // Track add_payment_info
    $(document.body).on('payment_method_selected', function() {
        var payment_method = $('input[name="payment_method"]:checked').val();
        if (payment_method && typeof ninetynine_hog_checkout_data !== 'undefined') {
            posthog.capture('add_payment_info', {
                currency: ninetynine_hog_checkout_data.currency,
                value: ninetynine_hog_checkout_data.value,
                items: ninetynine_hog_checkout_data.items,
                payment_type: payment_method
            });
        }
    });
});
