@extends('layouts.app', ['product' => $product])

@section('title', $page_title)


@section('js_data')

  <script type="text/javascript">
    js_data.customer = @json($customer, JSON_UNESCAPED_UNICODE);
    js_data.company_address = @json($company_address, JSON_UNESCAPED_UNICODE);
    js_data.company_descriptor_prefix = @json($company_descriptor_prefix, JSON_UNESCAPED_UNICODE);
    js_data.ipqualityscore_api_hash = @json($setting['ipqualityscore_api_hash'], JSON_UNESCAPED_UNICODE);
    js_data.recently_bought_names = @json($recentlyBoughtNames, JSON_UNESCAPED_UNICODE);
    js_data.recently_bought_cities = @json($recentlyBoughtCities, JSON_UNESCAPED_UNICODE);
    js_data.i18n.phrases = @json($loadedPhrases, JSON_UNESCAPED_UNICODE);
    js_data.i18n.images = @json($loadedImages, JSON_UNESCAPED_UNICODE);
    js_data.lang_code = @json($langCode, JSON_UNESCAPED_UNICODE);
    js_data.country_code = @json($countryCode, JSON_UNESCAPED_UNICODE);
    js_data.countries = @json($countries, JSON_UNESCAPED_UNICODE);
    js_data.payment_methods = @json($setting['payment_methods'], JSON_UNESCAPED_UNICODE);
    js_data.product = @json($product, JSON_UNESCAPED_UNICODE);
  </script>

@endsection


@section('js_prerender')
  @include('prerender.checkout.3ds_failure')
@endsection


@section('js_deps')

  <script type="text/javascript">
    js_deps.show([
      'awesome.css',
      'element.css',
      'bootstrap.css',
      'layout-styles',
      'page-styles',
    ]);
  </script>

@endsection


@section('fonts')

  @if (request()->get('tpl') === 'fmc5')
    @include('components.fonts.lato')
  @endif

  @include('components.fonts.pathway_gothic')

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


@section('fixed')
  @if((Route::is('checkout') || Route::is('checkout_price_set')) && (Request::get('show_timer') === '{timer}' || Request::get('show_timer') === '1'))
    <timer-component></timer-component>
  @endif
@endsection


@section('content')

@section('title', isset($product->skus[0]) ? $product->skus[0]['name'] . ' ' . t('checkout.page_title') : '')

<app-component></app-component>

@include('layouts.footer', ['isWhite' => true])

@endsection
