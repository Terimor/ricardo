@if (!empty($is_checkout) && empty($is_smartbell))

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
