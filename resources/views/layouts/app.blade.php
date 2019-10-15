<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Google Analytics ID -->
    <meta name="ga-id" content="{{ $ga_id }}">

    <title>@yield('title', config('app.name'))</title>

    @if (!empty(optional($product)->favicon_image))
        <link rel="shortcut icon" href="{{ $product->favicon_image }}">
    @endif

    @yield('head')

    <script type="text/javascript">window.cdnUrl='{{ $cdnUrl }}';</script>

    @if (!empty($ga_id))
      <!-- Global site tag (gtag.js) - Google Analytics -->
      <script async src="https://www.googletagmanager.com/gtag/js?id={{ $ga_id }}"></script>
      <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','{{ $ga_id }}');</script>
    @endif

    <!-- Scripts -->

    @if (config('app.env') !== 'local' && config('app.env') !== 'development')
      <script type="text/javascript">window.SentryDSN='{{ $SentryDsn }}';</script>
      <script src="https://browser.sentry-cdn.com/5.6.3/bundle.min.js" crossorigin="anonymous" async></script>
      @if ($HasVueApp)
        <script src="https://browser.sentry-cdn.com/5.6.3/vue.min.js" crossorigin="anonymous" async></script>
      @endif
    @endif

    @if ($HasVueApp)
      <script src="https://www.paypal.com/sdk/js?currency={{$PayPalCurrency}}&disable-card=visa,mastercard,amex&client-id={{ $setting['instant_payment_paypal_client_id'] }}" async></script>

      @if (isset($countryCode) && $countryCode === 'br')
        <script src="https://js.ebanx.com/ebanx-1.6.0.min.js" async></script>
      @endif

      @if (Request::is('checkout'))
        <script type="text/javascript">window.IPQ={Callback:()=>{}};</script>
        <script src="https://www.ipqualityscore.com/api/*/{{ $setting['ipqualityscore_api_hash'] }}/learn.js" async></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/libphonenumber-js/1.7.25/libphonenumber-js.min.js" async></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/js-sha256/0.9.0/sha256.min.js" async></script>
      @endif

      @if (Request::get('exit'))
        <script src="{{ mix_cdn('assets/scripts/bioep.min.js') }}" async></script>
      @endif

      @if (config('app.env') !== 'local' && config('app.env') !== 'development')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/vue/2.6.10/vue.min.js" defer></script>
      @else
        <script src="https://cdnjs.cloudflare.com/ajax/libs/vue/2.6.10/vue.js" defer></script>
      @endif

      <script src="https://cdnjs.cloudflare.com/ajax/libs/element-ui/2.11.1/index.js" defer></script>
      <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/16.0.2/js/intlTelInput.min.js" defer></script>
    @else
      <script src="{{ mix_cdn('/assets/js/static.js') }}" async></script>
    @endif
    @yield('script')

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet" media="none" onload="if(media!='all')media='all'">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.3.0/css/font-awesome.min.css" media="none" onload="if(media!='all')media='all'">

    <!-- Styles -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap-grid.min.css" media="none" onload="if(media!='all')media='all'">

    @if ($HasVueApp)
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/element-ui/2.11.1/theme-chalk/index.css" media="none" onload="if(media!='all')media='all'">
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/16.0.2/css/intlTelInput.css" media="none" onload="if(media!='all')media='all'">
    @endif

    <link href="{{ mix_cdn('assets/css/app.css') }}" rel="stylesheet" media="none" onload="if(media!='all')media='all'">
    @yield('styles')

    @if (!empty($htmlToApp['pixels']))
        @foreach($htmlToApp['pixels'] as $pixel)
        {{ !empty($pixel['code']) ? $pixel['code'] : '' }}
        @endforeach
    @endif

    @if (!empty($htmlToApp['gtags']))
        @foreach($htmlToApp['gtags'] as $gtag)
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ !empty($gtag['code']) ? $gtag['code'] : '' }}"></script>
        <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','{{ !empty($gtag['code']) ? $gtag['code'] : '' }}');</script>
        @endforeach
    @endif

    {{--Do not remove this empty style tag--}}
    <style></style>

</head>
<body class="{{ Route::has('promo') ? 'white-bg' : '' }}">
    <div id="app">
        @if (Request::is('splash'))
            @include('layouts.header_splash', ['product' => $product])
        @elseif (Request::is('orderTracking'))
        @else
            @include('layouts.header', ['product' => $product])
        @endif

        <main class="pt-4">
            @yield('content')
        </main>
    </div>

    @if (Request::is('checkout'))
      <noscript><img src="https://www.ipqualityscore.com/api/*/{{ $setting['ipqualityscore_api_hash'] }}/pixel.png" /></noscript>
    @endif
</body>
</html>
