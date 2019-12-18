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
    var js_show_deps = [
      'awesome.css',
      'element.css',
      'bootstrap.css',
      'intl_tel_input.css',
      'layout-styles',
      'page-styles',
    ];
  </script>

  @yield('js_deps_checkout')

  <script type="text/javascript">
    js_deps.show(js_show_deps);
  </script>

@endsection


@section('styles')

  @yield('styles_checkout')

@endsection


@section('scripts')

  @yield('scripts_checkout')

@endsection


@section('content')

  @yield('content_checkout')

  @include('layouts.footer', ['isWhite' => true])

@endsection
