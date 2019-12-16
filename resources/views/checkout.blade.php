@extends('layouts.app', ['product' => $product])

@section('title', $page_title)


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


@section('script')
<script type="text/javascript">
    var bluesnapCredential = {
      'Authorization': 'Basic {{base64_encode(env('BLUESNAP_API_KEY').':'.env('BLUESNAP_API_PASS'))}}',
    }

    var recentlyBoughtNames = @json($recentlyBoughtNames);
    var recentlyBoughtCities = @json($recentlyBoughtCities);

    var checkoutData = {
      langCode: '{{ $langCode }}',
      countryCode: '{{ $countryCode }}',
      countries: @json($countries),
      product: @json($product),
      productImage: '{{$product->logo_image}}',
      paymentMethods: @json($setting['payment_methods']),
    }

    var loadedPhrases = @json($loadedPhrases);
    var loadedImages = @json($loadedImages);
</script>

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
