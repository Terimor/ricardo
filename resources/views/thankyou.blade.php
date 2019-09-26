@extends('layouts.app', ['product' => $product])

@section('title', $product->page_title . ' - ' . $loadedPhrases['thankyou_title'])

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/thank-you.css') }}">
@endsection

@section('head')
    <meta property="og:title" content="{{ $product->long_name }}" />
    <meta property="og:description" content="I just bought this awesome product. Thought I’d share this with you" />
    <meta property="og:image" content="{{ $product->image[0] }}" />
@endsection


@section('script')
<script defer>
    const upsells = {
        countryCode: '{{ $countryCode }}',
        setting: @json($setting),
        product: @json($product),
        orderCustomer: @json($orderCustomer),
    }

    window.loadedPhrases = @json($loadedPhrases);
</script>
<script src="{{ asset('js/views/thank-you.js') }}" defer></script>
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
                    :src=`https://maps.google.com/maps?q=${getShippingAddress}&amp;t=&amp;z=17&amp;ie=UTF8&amp;iwloc=&amp;output=embed`
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
                        <div class="quantity">@{{ selectedProductData.quantity }}</div>
                    </div>
                    <div class="d-flex flex-column justify-content-center">
                        <div class="thank-you__order__name">{{ $product->long_name }}</div>
                        <div
                            class="thank-you__order__name"
                            v-if="selectedProductData.isWarrantyChecked"
                        >
                            {{ t('thankyou.order.warranty') }}: @{{ selectedProductData.prices.warranty_price_text }}
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
                <span>PayPal</span>
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
                        <div class="social-icon fb-icon"></div>
                        Facebook
                    </a>
                </li>
                <li @click="onClickSocialNetwork('twitter')" id="twitter">
                    <a href="#twitter" class="twitter-tab-header">
                        <div class="social-icon twitter-icon"></div>
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
    </div>

@endsection
