@if (isset($product->is_paypal_hidden) && !$product->is_paypal_hidden && (!empty($is_checkout) || !empty($is_upsells)))

  <script
    id="paypal-script"
    src="https://www.paypal.com/sdk/js?currency={{$PayPalCurrency}}&disable-card=visa,mastercard,amex&client-id={{ $setting['instant_payment_paypal_client_id'] }}"
    onload="js_deps.ready('paypal')"
    async></script>

@endif
