@if ((Route::is('checkout') || Route::is('checkout_price_set')) && str_replace('www.', '', Request::getHost()) !== 'smartbell.pro')

  <script
    src="https://cdnjs.cloudflare.com/ajax/libs/js-sha256/0.9.0/sha256.min.js"
    onload="js_deps.ready('sha256')"
    async></script>

@endif
