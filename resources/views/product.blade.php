<!DOCTYPE html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
<script
    src="https://www.paypal.com/sdk/js?disable-card=visa,mastercard,amex&client-id={{ config('services.paypal.client_id') }}">
</script>

<div id="paypal-button-container">

</div>
<script>
    paypal.Buttons({
        createOrder: function(data, actions) {
            // Set up the transaction
            return actions.order.create({
                purchase_units: [{
                    amount: {
                        value: '0.01'
                    }
                }]
            });

        },
        onApprove: function(data, actions) {
            return actions.order.capture().then(function(details) {
                console.log(data);
                console.log(details);
                console.log('Transaction completed by ' + details.payer.name.given_name);
                // Call your server to save the transaction
                return fetch('/paypal-transaction-complete', {
                    method: 'post',
                    credentials: 'same-origin',
                    headers: {
                        'X-CSRF-TOKEN': document.head.querySelector('meta[name="csrf-token"]').content,
                        'content-type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        orderID: data.orderID,
                    })
                });
            });
        }
    }).render('#paypal-button-container');
</script>
<br>
<br>
<br>
</body>

{{--@php(dd($product))--}}
{{--<div>Product name: {{ $product->product_name }}</div>--}}
{{--<div>Product description: {{ $product->description['en'] }}</div>--}}
{{--<div>Product long_name: {{ $product->long_name['en'] }}</div>--}}
{{--<div>Product logo_image: {{ isset($product->logoImage->urls) ? $product->logoImage->urls : '' }}</div>--}}
{{--<div>Product upsell_hero_image: {{ isset($product->upsellHeroImage->urls) ? $product->upsellHeroImage->urls : '' }}</div>--}}
{{--<div>Product category: {{ $product->category->name }}</div>--}}


{{--@json($product)--}}
