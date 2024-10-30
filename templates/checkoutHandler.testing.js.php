var checkoutHandler = function(orderToken) {
    var promise = new Promise(function(resolve, reject) {
        setTimeout(function() {
            resolve(['approved', <?php echo $order->get_id() ?>]);
        }, 3000);
    });

    return promise.then(function([status, id]) {
        jQuery("#order_review > input[name='order_id']").val(<?php echo $order->get_id() ?>)
        jQuery("#order_review > input[name='reference']").val(id)
        jQuery("#order_review > input[name='status']").val(status)
        jQuery("#order_review").submit();
    });
}(123)();
