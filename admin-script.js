jQuery(document).ready(function($) {
    $('#coupon-generator-form').on('submit', function(e) {
        e.preventDefault();
        var $form = $(this);
        var $submitButton = $form.find('#submit');
        var $result = $('#coupon-generator-result');
        var $log = $('#coupon-generator-log');

        $submitButton.prop('disabled', true).val('Generating...');
        $result.html('');
        $log.html('');

        $.ajax({
            url: cwcg_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'generate_coupons',
                nonce: cwcg_ajax.nonce,
                discount: $form.find('#discount').val(),
                count: $form.find('#count').val(),
                expiry_date: $form.find('#expiry_date').val(),
                usage_limit: $form.find('#usage_limit').val(),
                usage_limit_per_user: $form.find('#usage_limit_per_user').val()
            },
            success: function(response) {
                if (response.success) {
                    $result.html('<div class="notice notice-success"><p>' + response.data.message + '</p></div>');
                    if (response.data.coupons.length > 0) {
                        var couponList = '<h3>Generated Coupons:</h3><ul>';
                        $.each(response.data.coupons, function(index, coupon) {
                            couponList += '<li>' + coupon + '</li>';
                        });
                        couponList += '</ul>';
                        $log.html(couponList);
                    }
                } else {
                    $result.html('<div class="notice notice-error"><p>Error: ' + response.data + '</p></div>');
                }
            },
            error: function() {
                $result.html('<div class="notice notice-error"><p>An error occurred. Please try again.</p></div>');
            },
            complete: function() {
                $submitButton.prop('disabled', false).val('Generate Coupons');
            }
        });
    });
});
