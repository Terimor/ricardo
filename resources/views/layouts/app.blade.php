<!DOCTYPE html>
<html
  class="hidden"
  lang="{{ $lang_locale }}"
  dir="{{ $lang_direction }}">

  <head>

    <!-- Title -->
    <title>@yield('title', config('app.name'))</title>

    <!-- Meta Tags -->
    @include('components.layout.meta')
    @include('components.layout.favicon')
    @yield('meta')

    <!-- JS Data -->
    @include('components.layout.js_data')
    @include('components.layout.js_product')
    @yield('js_data')

    <!-- JS Deps -->
    @include('components.layout.js_deps')
    @yield('js_deps')

    <!-- Async Fonts -->
    @include('components.fonts.lato')
    @include('components.fonts.awesome4')

    <!-- Async Styles -->
    @include('components.styles.bootstrap')
    @include('components.styles.element')
    @include('components.styles.intl_tel_input')
    @include('components/styles/layout')
    @yield('styles')

    <!-- Async Scripts -->
    @include('components.scripts.gtags')
    @include('components.scripts.analytics')
    @include('components.scripts.sentry')
    @include('components.scripts.intl_tel_input')
    @include('components.scripts.paypal')
    @include('components.scripts.ipqualityscore')
    @include('components.scripts.libphonenumber')
    @include('components.scripts.sha256')
    @include('components.scripts.bioep')
    @include('components.scripts.vue', ['defer' => true])
    @include('components.scripts.element')
    @include('components.scripts.static')
    @yield('scripts')

    <!-- Redirects -->
    @include('components.3ds_redirect')

    {{--Do not remove this empty style tag--}}
    <style></style>

  </head>


  <body class="{{ Route::has('promo') ? 'white-bg' : '' }}">

    <div id="app">
        @include('components.black_friday')
        @include('components.christmas')

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

    @include('components.static_topbar')
    @include('components.freshchat')

    <!-- No Script -->
    @include('components.scripts.gtags_ns')
    @include('components.scripts.ipqualityscore_ns')

    <!-- Pixels -->
    @include('components.scripts.pixels')

  </body>

</html>
