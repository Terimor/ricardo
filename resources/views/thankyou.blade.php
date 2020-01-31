@extends('layouts.app', ['product' => $product])

@section('title', $page_title)


@section('meta')
  <meta property="og:title" content="{{ $product->long_name }}" />
  <meta property="og:description" content="I just bought this awesome product. Thought I’d share this with you" />
  <meta property="og:image" content="{{ $product->image[0] }}" />
@endsection


@section('js_data')

  <script type="text/javascript">
    js_data.i18n.phrases = @json($loadedPhrases);
    js_data.country_code = @json($countryCode);
    js_data.order_customer = @json($orderCustomer);
    js_data.payment_method = @json($payment_method);
    js_data.product = @json($product);
    js_data.setting = @json($setting);

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

@endsection


@section('scripts')

  <script
    src="{{ mix_cdn('assets/js/views/thank-you.js') }}"
    defer></script>

@endsection


@section('content')
    <div class="container thank-you" id="thank-you">
        <p class="thank-you__order">{{ t('thankyou.order') }}: {{ $orderCustomer->number }}</p>
        <h2 class="thank-you__name">{{ t('thankyou.thankyou') }} {{ $orderCustomer->customer_first_name }}</h2>
        <div class="border-box thank-you__container">
            <div id="map">
                <iframe
                    class="resp-iframe"
                    width="100%"
                    height="300"
                    id="gmap_canvas"
                    :src="'https://maps.google.com/maps?q=' + getShippingAddress + '&amp;t=&amp;z=17&amp;ie=UTF8&amp;iwloc=&amp;output=embed'"
                    frameborder="0"
                    scrolling="no"
                    marginheight="0"
                    marginwidth="0"
                ></iframe>
            </div>
            <h4>{{ t('thankyou.order.confirmed') }}</h4>
            <p>{{ t('thankyou.order.accepted') }} {{ $orderCustomer->customer_email }}</p>
        </div>
        <div class="border-box thank-you__details">
            <h4>{{ t('thankyou.order.details') }}</h4>

            <div class="thank-you__order">
                <div class="d-flex">
                    <div class="thank-you__order__image">
                        <img src="{{ $product->image[0] }}" alt="">
                        <div class="quantity">@{{ selectedProductData.quantity || 0 }}</div>
                    </div>
                    <div class="d-flex flex-column justify-content-center">
                        <div class="thank-you__order__name">{{ $product->long_name }}</div>
                        <div
                            class="thank-you__order__name"
                            v-if="selectedProductData.isWarrantyChecked"
                        >
                            {{ t('thankyou.order.warranty') }}: @{{ orderCustomer.productsText[0].warranty_price_text }}
                        </div>
                    </div>
                </div>
                <div class="thank-you__order__price">@{{ getEntityPrice(0) }}</div>
            </div>
            <div v-for="(order, index) in subOrder">
                <thank-you-item
                    :key="index"
                    :price="getEntityPrice(index + 1)"
                    :order="order"
                ></thank-you-item>
            </div>

            <hr>

            <p class="paragraph d-flex justify-content-between">
                <span>{{ t('thankyou.subtotal') }}:</span>
                <span>@{{ total }}</span>
            </p>
            <p class="paragraph d-flex justify-content-between">
                <span>{{ t('thankyou.payment.method') }}:</span>
                <span>{{ $payment_method['name'] ?? '' }}</span>
            </p>

            <hr>

            <p class="paragraph d-flex justify-content-between">
                <span>
                    {{ t('thankyou.order.total') }}:
                </span>
                <span class="bold">
                    @{{ total }}
                </span>
            </p>

        </div>
        <div class="border-box thank-you__customer-info">
            <h4>{{ t('thankyou.customer.info') }}</h4>
            <p class="thank-you__shipping">{{ t('thankyou.shipping.address') }}</p>
            <p class="paragraph">{{ $orderCustomer->customer_first_name }} {{ $orderCustomer->customer_last_name }}</p>
            <p class="paragraph">{{ $orderCustomer->shipping_street }}</p>
            <p class="paragraph">{{ $orderCustomer->shipping_city }} {{ strtoupper($orderCustomer->shipping_country)  }}</p>
            <p class="paragraph">{{ $orderCustomer->shipping_zip }}</p>

        </div>
        <div class="border-box thank-you__share-order">
            <h4 class="text-center">{{ t('thankyou.share') }}</h4>
            <p class="text-center">
                {{ t('thankyou.hope') }}
            </p>

            <ul id="social-media-tabs" class="nav nav-tabs">
                <li @click="onClickSocialNetwork('facebook')" id="facebook" class="active">
                    <a href="#facebook" class="facebook-tab-header">
                        <div class="social-icon fb-icon" style="background-image: url({{ $cdn_url }}/assets/images/social/fb-icon.png);"></div>
                        Facebook
                    </a>
                </li>
                <li @click="onClickSocialNetwork('twitter')" id="twitter">
                    <a href="#twitter" class="twitter-tab-header">
                        <div class="social-icon twitter-icon" style="background-image: url({{ $cdn_url }}/assets/images/social/twitter-icon.png);"></div>
                        Twitter
                    </a>
                </li>
            </ul>

            <textarea id="quote" rows="10">{{ t('thankyou.bought') }}</textarea>

            <div class="d-flex justify-content-center">
                <button
                    id="share"
                    class="green-button"
                    @click="share"
                >
                    {{ t('thankyou.order.share.item') }}
                </button>
            </div>
        </div>
        @include('layouts.footer', ['isWhite' => true, 'hasHome' => true ])
    </div>

@endsection
