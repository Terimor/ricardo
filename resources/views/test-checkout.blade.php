<!DOCTYPE html><!-- PAYPAL required  -->
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1"> <!-- PAYPAL required  -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge" /><!-- PAYPAL required  -->
    <meta name="csrf-token" content="{{ csrf_token() }}"><!-- BACKEND required  -->
</head>
<body>
<script src="https://www.paypal.com/sdk/js?disable-card=visa,mastercard,amex&client-id={{ $paypal_client }}"></script> <!-- PAYPAL required  -->

<div>Product</div>
<pre id="json">{{ json_encode($product, JSON_PRETTY_PRINT) }}</pre>


<div>Test checkout</div>
<div id="paypal-button-container">

    <form>
        <div>
            <label for="sku_code">Sku code</label>
            <input type="text" name="sku_code" id="sku_code">
        </div>
        <div>
            <label for="sku_quantity">Sku quantity</label>
            <input type="text" name="sku_quantity" id="sku_quantity">
        </div>

        <div>
            <label for="is_warrantry_checked">is_warrantry_checked</label>
            <input type="checkbox" name="is_warrantry_checked" id="is_warrantry_checked" value="1">
        </div>
        <div>
            <label for="order_id">order_id</label>
            <input type="text" name="order_id" id="order_id">
        </div>

    </form>

</div>
<script>
    paypal.Buttons({
        createOrder: function() {
            return fetch('/paypal-create-order', {
                method: 'post',
                credentials: 'same-origin',
                headers: {
                    'X-CSRF-TOKEN': document.head.querySelector('meta[name="csrf-token"]').content,
                    'content-type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                /**
                 * Api params
                 */
                body: JSON.stringify({
                    sku_code: document.getElementById('sku_code').value,
                    sku_quantity: document.getElementById('sku_quantity').value,
                    is_warrantry_checked: document.getElementById('is_warrantry_checked').checked,
                    order_id: document.getElementById('order_id').value,
                    page_checkout: document.location.href,
                    offer: new URL(document.location.href).searchParams.get('offer'),
                    affiliate: new URL(document.location.href).searchParams.get('affiliate'),
                })
            }).then(function(res) {
                return res.json();
            }).then(function(data) {
                return data.id;
            });
        },
        onApprove: function(data) {
            return fetch('/paypal-verify-order', {
                credentials: "same-origin",
                method: 'post',
                headers: {
                    'X-CSRF-TOKEN': document.head.querySelector('meta[name="csrf-token"]').content,
                    'content-type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    orderID: data.orderID
                })
            }).then(function(res) {
                return res.json();
            }).then(function(details) {
                console.log(details);
            });
        }
    }).render('#paypal-button-container');
</script>
</body>
