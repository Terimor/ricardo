@if ((Route::is('checkout') || Route::is('checkout_price_set') || Route::is('checkout_vrtl')) && str_replace('www.', '', Request::getHost()) !== 'smartbell.pro')

  <script
    src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/16.0.4/js/intlTelInput.min.js"
    onload="js_deps.ready('intl_tel_input')"
    async></script>

@endif
