@extends('layouts.app', ['product' => $product])

@section('title', $page_title)


@section('js_data')
  <script type="text/javascript">
    js_data.upsells = @json($product->upsells, JSON_UNESCAPED_UNICODE);

    js_data.i18n.phrases = @json($loadedPhrases);
    js_data.country_code = @json($countryCode);
    js_data.order_customer = @json($orderCustomer);
    js_data.product = @json($product);

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
      'page-styles',
    ]);
  </script>
@endsection


@section('fonts')
  @include('new.fonts.roboto')
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
    href="{{ mix_cdn('assets/css/new/pages/vrtl/upsells.css') }}"
    onload="js_deps.ready.call(this, 'page-styles')"
    rel="stylesheet"
    media="none" />
@endsection


@section('scripts')
  <script
    src="{{ mix_cdn('assets/js/app.js') }}"
    defer></script>
@endsection


@section('content')
  <div class="upsells">
    <upsells-virtual></upsells-virtual>
    @include('layouts.footer', ['isWhite' => true, 'hasHome' => true ])
  </div>
@endsection
