@extends('layouts.app', ['product' => $product])

@section('title', $page_title)


@section('meta')
  @if (!empty($product->favicon_image))
    <link rel="shortcut icon" href="{{ $product->favicon_image }}">
  @endif
@endsection


@section('js_data')

  <script type="text/javascript">
    js_data.i18n.phrases = @json($loadedPhrases, JSON_UNESCAPED_UNICODE);
    js_data.country_code = @json($countryCode, JSON_UNESCAPED_UNICODE);
    js_data.order_customer = @json($orderCustomer, JSON_UNESCAPED_UNICODE);
    js_data.product = @json($product, JSON_UNESCAPED_UNICODE);

    var products_success = js_data.order_customer.products
        .filter(function(product) {
            var txn = js_data.order_customer.txns.find(function(item) {
                return item.hash === product.txn_hash;
            });
            return txn && txn.status !== 'failed';
        });

    window.amountjs = Math.round(
        products_success
            .reduce(function(acc, product) { return acc + product.price_usd + product.warranty_price_usd; }, 0)
    * 100) / 100;

    window.localamountjs = Math.round(
        products_success
            .reduce(function(acc, product) { return acc + product.price + product.warranty_price; }, 0)
    * 100) / 100;

    window.upsell_amt = Math.round(
        products_success
            .filter(function(product) { return product.is_upsells; })
            .reduce(function(acc, product) { return acc + product.price_usd + product.warranty_price_usd; }, 0)
    * 100) / 100;

    window.mainsku = js_data.order_customer.products
        .filter(function(product) { return product.is_main })[0].sku_code;

    window.localcurrency = js_data.order_customer.currency;
    window.orderid = js_data.order_customer._id;
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
