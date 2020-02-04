@if ((Route::is('checkout') || Route::is('checkout_price_set') || Route::is('checkout_vrtl')) && str_replace('www.', '', Request::getHost()) !== 'smartbell.pro')

  @php
    $bluesnap_domain = config('app.env') === 'local' || config('app.env') === 'development'
      ? 'sandbox.bluesnap.com'
      : 'www.bluesnap.com';
  @endphp

  <script
    src="https://{{ $bluesnap_domain }}/web-sdk/4/bluesnap.js"
    onload="js_deps.ready('bluesnap_3ds')"
    async></script>

@endif
