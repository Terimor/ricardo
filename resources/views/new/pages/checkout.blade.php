@extends('layouts.app', ['product' => $product])

@section('title', $page_title)


@php
  $deal_available = array_map(
    function($deal) {
      return $deal['quantity'];
    },
    $deals,
  );

  $i18n_phrases = array_reduce(
    [
      'checkout.page_title.wait',
      'checkout.notification.just_bought',
      'checkout.notification.users_active',
      'checkout.payment_form.state',
      'checkout.payment_form.zipcode',
      'checkout.payment_form.installments.full_amount',
      'checkout.payment_form.installments.pay_3',
      'checkout.payment_form.installments.pay_6',
      'checkout.payment_form.card_type.debit',
      'checkout.payment_form.card_type.credit',
      'document_type.ar.cuit',
      'document_type.ar.cuil',
      'document_type.ar.cdi',
      'document_type.ar.dni',
      'document_type.co.nit',
      'document_type.co.cc',
      'document_type.co.ce',
    ],
    function($acc, $phrase) use ($loadedPhrases) {
      $acc[$phrase] = $loadedPhrases[$phrase];
      return $acc;
    },
    [],
  );
@endphp


@section('js_data')
  <script type="text/javascript">
    js_data.lang_code = @json($langCode, JSON_UNESCAPED_UNICODE);
    js_data.country_code = @json($countryCode, JSON_UNESCAPED_UNICODE);

    js_data.ipqualityscore_api_hash = @json($setting['ipqualityscore_api_hash'], JSON_UNESCAPED_UNICODE);

    js_data.i18n.phrases = @json($i18n_phrases, JSON_UNESCAPED_UNICODE);

    js_data.recently_bought_names = @json($recentlyBoughtNames, JSON_UNESCAPED_UNICODE);
    js_data.recently_bought_cities = @json($recentlyBoughtCities, JSON_UNESCAPED_UNICODE);

    js_data.product = @json($product, JSON_UNESCAPED_UNICODE);
    js_data.deal_available = @json($deal_available, JSON_UNESCAPED_UNICODE);
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
