@extends('layouts.app', ['product' => $product])

@section('title', $page_title)


@section('js_data')

  <script type="text/javascript">
    js_data.recently_bought_names = @json($recentlyBoughtNames);
    js_data.recently_bought_cities = @json($recentlyBoughtCities);
    js_data.i18n.phrases = @json($loadedPhrases);
    js_data.i18n.images = @json($loadedImages);
    js_data.lang_code = @json($langCode);
    js_data.country_code = @json($countryCode);
    js_data.countries = @json($countries);
    js_data.payment_methods = @json($setting['payment_methods']);
    js_data.product = @json($product);
  </script>

@endsection


@section('js_deps')

  <script type="text/javascript">
    js_deps.show([
      'awesome.css',
      'element.css',
      'bootstrap.css',
      'intl_tel_input.css',
      'layout-styles',
      'page-styles',
    ]);
  </script>

@endsection


@section('styles')

  <link
    href="{{ mix_cdn('assets/js/app.vue.css') }}"
    onload="js_deps.ready.call(this, 'page-styles')"
    rel="stylesheet"
    media="none" />

@endsection


@section('scripts')

  <script
    src="{{ mix_cdn('assets/js/app.js') }}"
    onload="js_deps.ready('page-scripts')"
    async></script>

@endsection


@section('content')

@section('title', isset($product->skus[0]) ? $product->skus[0]['name'] . ' ' . t('checkout.page_title') : '')

<app-component></app-component>

@include('layouts.footer', ['isWhite' => true])

@endsection
