<!DOCTYPE html>
<html
  class="hidden"
  lang="{{ $lang_locale }}"
  dir="{{ $lang_direction }}">

  <head>

    <!-- Title -->
    <title>@yield('title', config('app.name'))</title>

    <!-- Meta Tags -->
    @include('minishop.layout.meta')
    @include('minishop.layout.favicon')
    @yield('meta')

    <!-- JS Data -->
    @include('minishop.layout.js_data')
    @include('minishop.layout.js_product')
    @yield('js_data')

    <!-- JS Deps -->
    @include('minishop.layout.js_deps')
    @yield('js_deps')

    <!-- Async Fonts -->
    @include('minishop.fonts.lato')
    @include('minishop.fonts.awesome')
    @yield('fonts')

    <!-- Async Styles -->
    @include('minishop.styles.bootstrap')
    @yield('styles')

    <!-- Async Scripts -->
    @include('minishop.scripts.gtags')
    @include('minishop.scripts.analytics')
    @include('minishop.scripts.sentry')
    @include('minishop.scripts.vue')
    @include('minishop.scripts.freshchat')
    @yield('scripts')

  </head>


  <body>

    <div
      id="app"
      ref="app">

      <!-- Header Region -->
      @include('minishop.regions.header')

      <!-- Content Region -->
      @yield('content')

      <!-- Footer Region -->
      @include('minishop.regions.footer')

      <!-- Fixed Region -->
      @include('minishop.regions.fixed.freshchat')
      @include('minishop.regions.fixed.support')

    </div>

    <!-- No Script -->
    @include('minishop.scripts.gtags_ns')

    <!-- Pixels -->
    @include('minishop.scripts.pixels')

  </body>

</html>
