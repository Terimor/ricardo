@extends('layouts.app', ['product' => $product])

@section('title', $page_title)


@php
  $deal_available = array_map(function($deal) { return $deal['quantity']; }, $deals);
@endphp


@section('js_data')
  <script type="text/javascript">
    js_data.customer = @json($customer);
    js_data.lang_code = @json($langCode, JSON_UNESCAPED_UNICODE);
    js_data.country_code = @json($countryCode, JSON_UNESCAPED_UNICODE);

    js_data.ipqualityscore_api_hash = @json($setting['ipqualityscore_api_hash'], JSON_UNESCAPED_UNICODE);

    js_data.i18n.phrases = @json($loadedPhrases, JSON_UNESCAPED_UNICODE);
    js_data.i18n.images = @json($loadedImages, JSON_UNESCAPED_UNICODE);

    js_data.recently_bought_names = @json($recentlyBoughtNames, JSON_UNESCAPED_UNICODE);
    js_data.recently_bought_cities = @json($recentlyBoughtCities, JSON_UNESCAPED_UNICODE);

    js_data.product = @json($product, JSON_UNESCAPED_UNICODE);
    js_data.deal_available = @json($deal_available, JSON_UNESCAPED_UNICODE);

    js_data.countries = @json($countries, JSON_UNESCAPED_UNICODE);
    js_data.payment_methods = @json($setting['payment_methods'], JSON_UNESCAPED_UNICODE);

    window.selectedOffer = 0;
    window.selectedPayment = 0;

    if (/^\/checkout\/.+/.test(location.pathname)) {
      js_query_params.cop_id = location.pathname.split('/')[2];
    }

    if (js_query_params.preload === '{preload}') {
      window.preloadjs = 3;
    }

    if (js_query_params.show_timer === '{timer}') {
      window.show_timerjs = 1;
    }
  </script>
@endsection


@section('js_prerender')
  @include('prerender.checkout.3ds_failure')
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


@section('fixed')
  @include('new.pages.checkout.blocks.timer_mobile')
@endsection


@section('header')
  @yield('header_checkout')
  @include('new.pages.checkout.blocks.timer_desktop')
@endsection


@section('content')
  @include('new.pages.checkout.blocks.recently_bought')
  @include('new.pages.checkout.blocks.leave_modal')
  @yield('content_checkout')
  @include('layouts.footer', ['isWhite' => true])
@endsection
