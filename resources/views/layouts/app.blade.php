<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Google Analytics ID -->
    <meta name="ga-id" content="{{ $ga_id }}">

    <title>@yield('title', config('app.name'))</title>

    @if (!empty(optional($product)->favicon_image))
        <link rel="shortcut icon" href="{{ $product->favicon_image }}">
    @endif

    @yield('head')

    @if (!empty($ga_id))
      <!-- Global site tag (gtag.js) - Google Analytics -->
      <script async src="https://www.googletagmanager.com/gtag/js?id={{ $ga_id }}"></script>
      <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','{{ $ga_id }}');</script>
    @endif

    <!-- Scripts -->

    @if ($HasVueApp)
      <script src="https://cdn.jsdelivr.net/npm/js-cookie@2/src/js.cookie.min.js" defer></script>
      <script src="https://js.ebanx.com/ebanx-1.6.0.min.js" defer></script>
      <script src="/js/ebanx.js" defer></script>
      <script src="https://www.paypal.com/sdk/js?currency={{$PayPalCurrency}}&disable-card=visa,mastercard,amex&client-id={{ $setting['instant_payment_paypal_client_id'] }}" defer></script>

      @if (Request::is('checkout'))
        <script type="text/javascript">var IPQ={Callback:()=>{}};</script>
        <script src="https://www.ipqualityscore.com/api/*/{{ $setting['ipqualityscore_api_hash'] }}/learn.js" defer></script>
      @endif

      {{--<script src="https://cdn.checkout.com/sandbox/js/checkout.js"></script>--}}
      {{--<script src="https://sandbox.bluesnap.com/js/cse/v1.0.4/bluesnap.js"></script>--}}
      {{--<script src="https://paypal.com/sdk/js?client-id={{env('PAYPAL_CLIENT_ID','')}}"></script>--}}

      @if (config('app.env') !== 'local' && config('app.env') !== 'development')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/vue/2.6.10/vue.min.js" defer></script>
      @else
        <script src="https://cdnjs.cloudflare.com/ajax/libs/vue/2.6.10/vue.js" defer></script>
      @endif

      <script src="https://cdnjs.cloudflare.com/ajax/libs/element-ui/2.11.1/index.js" defer></script>
      <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/16.0.2/js/intlTelInput.min.js" defer></script>
      <script src="https://cdnjs.cloudflare.com/ajax/libs/date-fns/1.30.1/date_fns.min.js" defer></script>
      <script src="https://cdnjs.cloudflare.com/ajax/libs/js-sha256/0.9.0/sha256.min.js" defer></script>
      <script src="{{ asset('scripts/bioep.min.js') }}" defer></script>
    @endif

    @if (config('app.env') !== 'local' && config('app.env') !== 'development')
    <script type="text/javascript">var SentryDSN='{{ $SentryDsn }}';</script>
      <script src="https://browser.sentry-cdn.com/5.6.3/bundle.min.js" crossorigin="anonymous" defer></script>
      @if ($HasVueApp)
        <script src="https://browser.sentry-cdn.com/5.6.3/vue.min.js" crossorigin="anonymous" defer></script>
      @endif
      <script src="/js/sentry.js" defer></script>
    @endif

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.3.0/css/font-awesome.min.css">

    <!-- Styles -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap-grid.min.css">

    @if ($HasVueApp)
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/element-ui/2.11.1/theme-chalk/index.css">
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/16.0.2/css/intlTelInput.css">
    @endif

    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    @yield('styles')
</head>
<body class="{{ Route::has('promo') ? 'white-bg' : '' }}">
    <div id="app">
        @if (Request::is('splash'))
            @include('layouts.header_splash', ['product' => $product])
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
    @yield('script')
</body>
</html>
