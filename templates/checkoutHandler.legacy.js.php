jQuery(document).ready(function () {
    var checkoutHandler = function (orderToken) {
        var completionHandler = function (status, id) {
            jQuery.ajax({
                url: "<?php echo $webhook_url; ?>",
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                data: 'json=' + JSON.stringify({
                    "order_id": <?php echo $order->id; ?>,
                    "status": status
                })
            }).done(function (result) {
                jQuery("#order_review > input[name='order_id']").val(<?php echo $order->id ?>)
                jQuery("#order_review > input[name='reference']").val(id)
                jQuery("#order_review > input[name='status']").val(status)
                jQuery("#order_review").submit();
            });
        };

        return LendingWorksCheckout(orderToken, window.location.href, completionHandler);
    }('<?php echo get_post_meta($order->id, WC_Lending_Works\Lib\Payment_Gateway::ORDER_TOKEN_METADATA_KEY)[0] ?>')();
});

