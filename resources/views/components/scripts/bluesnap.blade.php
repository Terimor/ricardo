@if ((Route::is('checkout') || Route::is('checkout_price_set') || Route::is('checkout_vrtl')) && str_replace('www.', '', Request::getHost()) !== 'smartbell.pro')

  @php
    $bluesnap_domain = config('app.env') === 'local' || config('app.env') === 'development'
      ? 'sandbox.bluesnap.com'
      : 'www.bluesnap.com';
  @endphp

  <iframe width='1' height='1' frameborder='0' scrolling='no' src='https://{{ $bluesnap_domain }}/servlet/logo.htm?s={{ $bluesnap_fraud_session_id }}'>
    <img width='1' height='1' src='https://{{ $bluesnap_domain }}/servlet/logo.gif?s={{ $bluesnap_fraud_session_id }}&d={{ $setting['bluesnap_seller_id'] }}'>
  </iframe>

@endif
