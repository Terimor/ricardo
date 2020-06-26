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
    @yield('js_data')

    <!-- JS Deps -->
    @include('components.layout.js_deps')
    @yield('js_deps')

    <!-- Async Fonts -->
    @include('components.fonts.lato')
    @include('components.fonts.awesome5')
    @yield('fonts')

    <!-- Async Styles -->
    @include('components.styles.bootstrap')
    @yield('styles')

    <!-- Async Scripts -->
    @include('components.scripts.gtags')
    @include('components.scripts.analytics')
    @include('components.scripts.sentry')
    @include('components.scripts.vue')
    @yield('scripts')

  </head>


  <body>

    <div
      id="app"
      ref="app"
      :style="[fixed_margin_top]">

      @if(config('app.site_disabled'))
        @include('/closed')
      @else
          <!-- Fixed Region -->
          @include('new.regions.fixed')

          <!-- Header Region -->
          @include('minishop.regions.header')
          <!-- Content Region -->
            @yield('content')
          <!-- Footer Region -->
          @include('minishop.regions.footer')

      @endif

    </div>

    <!-- No Script -->
    @include('components.scripts.gtags_ns')

    <!-- Pixels -->
    @include('components.scripts.pixels')

  </body>

</html>
