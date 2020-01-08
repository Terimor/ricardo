@extends('layouts.app', ['product' => $product])

@section('title', $page_title)


@section('css_inline')
  @include('new.styles.intl_tel_input')
@endsection


@section('js_data')
  <script type="text/javascript">
    js_data.ipqualityscore_api_hash = @json($setting['ipqualityscore_api_hash']);
    js_data.recently_bought_names = @json($recentlyBoughtNames);
    js_data.recently_bought_cities = @json($recentlyBoughtCities);
    js_data.i18n.phrases = @json($loadedPhrases);
    js_data.i18n.images = @json($loadedImages);
    js_data.lang_code = @json($langCode);
    js_data.country_code = @json($countryCode);
    js_data.countries = @json($countries);
    js_data.payment_methods = @json($setting['payment_methods']);
    js_data.product = @json($product);
    js_data.deals = @json($deals);

    if (/^\/checkout\/.+/.test(location.pathname)) {
      js_query_params.cop_id = location.pathname.split('/')[2];
    }

    window.selectedOffer = 0;
    window.selectedPayment = 0;

    if (js_query_params.preload === '{preload}') {
      window.preloadjs = 3;
    }

    if (js_query_params.show_timer === '{timer}') {
      window.show_timerjs = 1;
    }
  </script>
@endsection


@section('js_prerender')
  @include('new.pages.checkout.prerender.3ds_redirect')
  @include('new.pages.checkout.prerender.reset_product')
  @include('new.pages.checkout.prerender.direct_linking')
  @include('new.pages.checkout.prerender.txid_cookie')
@endsection


@section('js_deps')
  <script type="text/javascript">
    js_deps.show([
      'page-styles',
    ]);
  </script>
@endsection


@section('fonts')
  @yield('fonts_checkout')
@endsection


@section('styles')
  @yield('styles_checkout')
@endsection


@section('scripts')
  @yield('scripts_checkout')
@endsection


@section('header_before')
  @include('new.pages.checkout.blocks.timer_mobile')
@endsection


@section('header')
  @include('new.pages.checkout.blocks.timer_desktop')
@endsection


@section('content')
  @include('new.pages.checkout.blocks.preloader')
  @include('new.pages.checkout.blocks.recently_bought')
  @include('new.pages.checkout.blocks.leave_modal')
  @yield('content_checkout')
@endsection
