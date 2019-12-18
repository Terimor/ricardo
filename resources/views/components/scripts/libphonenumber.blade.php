@if (Route::is('checkout') || Route::is('checkout_price_set') && str_replace('www.', '', Request::getHost()) !== 'smartbell.pro')

  <script
    src="https://cdnjs.cloudflare.com/ajax/libs/libphonenumber-js/1.7.25/libphonenumber-js.min.js"
    onload="js_deps.ready('libphonenumber')"
    async></script>

@endif
