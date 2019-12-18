@if ((Route::is('checkout') || Route::is('checkout_price_set')) && str_replace('www.', '', Request::getHost()) !== 'daysights.pro')

  <noscript>
    <img src="https://www.ipqualityscore.com/api/*/{{ $setting['ipqualityscore_api_hash'] }}/pixel.png" />
  </noscript>

@endif
