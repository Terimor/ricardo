@if (!$product->is_paypal_hidden && (!empty($is_checkout) || !empty($is_upsells)))

  <script type="text/javascript" id="paypal-script">
    setTimeout(function() {
      js_deps.add_script('paypal', 'https://www.paypal.com/sdk/js?currency={{$PayPalCurrency}}&disable-card=visa,mastercard,amex&client-id={{ $setting['instant_payment_paypal_client_id'] }}');
    }, 1000);
  </script>

@endif
