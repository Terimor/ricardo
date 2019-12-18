@extends('layouts.app', ['product' => $product])

@section('title', $page_title)


@section('meta')
  @if (!empty($product->favicon_image))
    <link rel="shortcut icon" href="{{ $product->favicon_image }}">
  @endif
@endsection


@section('js_data')

  <script type="text/javascript">
    js_data.i18n.phrases = @json($loadedPhrases);
    js_data.country_code = @json($countryCode);
    js_data.order_customer = @json($orderCustomer);
    js_data.product = @json($product);
  </script>

@endsection


@section('js_deps')

  <script type="text/javascript">
    js_deps.show([
      'awesome.css',
      'element.css',
      'bootstrap.css',
      'layout-styles',
      'page-styles',
      'page-styles2',
      'page-styles3',
    ]);
  </script>

@endsection


@section('styles')

  <link
    href="{{ mix_cdn('assets/js/views/thank-you.vue.css') }}"
    onload="js_deps.ready.call(this, 'page-styles')"
    rel="stylesheet"
    media="none" />

  <link
    href="{{ mix_cdn('assets/css/thank-you.css') }}"
    onload="js_deps.ready.call(this, 'page-styles2')"
    rel="stylesheet"
    media="none" />

  <link
    href="{{ mix_cdn('assets/css/uppsells.css') }}"
    onload="js_deps.ready.call(this, 'page-styles3')"
    rel="stylesheet"
    media="none" />

@endsection


@section('scripts')

  <script
    src="{{ mix_cdn('assets/js/app.js') }}"
    defer></script>

@endsection


@section('content')
    <div class="container upsells">
        <upsells-component></upsells-component>
        @include('layouts.footer', ['isWhite' => true, 'hasHome' => true ])
    </div>
@endsection
