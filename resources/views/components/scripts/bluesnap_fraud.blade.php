@if (!empty($is_checkout) && empty($is_smartbell))

  @php
    $bluesnap_domain = config('app.env') === 'local' || config('app.env') === 'development'
      ? 'sandbox.bluesnap.com'
      : 'www.bluesnap.com';
  @endphp

  <iframe width='1' height='1' frameborder='0' scrolling='no' src='https://{{ $bluesnap_domain }}/servlet/logo.htm?s={{ $setting['bluesnap_fraud_session_id'] }}'>
    <img width='1' height='1' src='https://{{ $bluesnap_domain }}/servlet/logo.gif?s={{ $setting['bluesnap_fraud_session_id'] }}&d={{ $setting['bluesnap_seller_id'] }}'>
  </iframe>

@endif
