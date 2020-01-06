@if ($HasVueApp && !$product->is_paypal_hidden)
  <script
    id="paypal-script"
    src="https://www.paypal.com/sdk/js?currency={{$PayPalCurrency}}&disable-card=visa,mastercard,amex&client-id={{ $setting['instant_payment_paypal_client_id'] }}"
    onload="js_deps.ready('paypal')"
    async></script>
@endif
