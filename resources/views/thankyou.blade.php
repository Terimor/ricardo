@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/thank-you.css') }}">
@endsection

@section('script')
<script defer>
    const upsells = {
        countryCode: '{{ $location->countryCode }}',
        setting: @json($setting),
        product: @json($product),
        orderCustomer: @json($orderCustomer),
    }
</script>
<script src="{{ asset('js/views/thank-you.js') }}" defer></script>
@endsection

@section('content')
    <div class="container thank-you" id="thank-you">
        <p class="thank-you__order">Order: {{ $orderCustomer->_id }}</p>
        <h2 class="thank-you__name">Thank you {{ $orderCustomer->customer_first_name }}</h2>
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
            <h4>Your order is confirmed</h4>
            <p>We’ve accepted your order, and we’re getting it ready. We’ll update on order status via email. A confirmation was sent to {{ $orderCustomer->customer_email }}</p>
        </div>
        <div class="border-box thank-you__details">
            <h4>Details of your order</h4>

            <div class="thank-you__order">
                <div class="d-flex">
                    <div class="thank-you__order__image">
                        <img :src="selectedProductData.prices.image" alt="">
                        <div class="quantity">@{{ selectedProductData.quantity }}</div>
                    </div>
                    <div class="thank-you__order__name">{{ $product->long_name }}</div>
                </div>
                <div class="thank-you__order__price">@{{ selectedProductData.prices.value_text }}</div>
            </div>
            <div
                class="thank-you__order"
                v-for="order in subOrder"
            >
                <div class="d-flex">
                    <div class="thank-you__order__image">
                        <img :src="order.imageUrl" alt="">
                        <div class="quantity">@{{ order.quantity }}</div>
                    </div>
                    <div class="thank-you__order__name">@{{ order.name }}</div>
                </div>
                <div class="thank-you__order__price">@{{ order.priceFormatted }}</div>
            </div>

            <hr>

            <p class="paragraph d-flex justify-content-between"><span>Subtotal:</span><span>@{{ total || totalPrice }}</span></p>
            <p class="paragraph d-flex justify-content-between"><span>Payment method:</span><span>PayPal</span></p>

            <hr>

            <p class="paragraph d-flex justify-content-between"><span>Order Total:</span><span class="bold">@{{ total || totalPrice }}</span></p>

        </div>
        <div class="border-box thank-you__customer-info">
            <h4>Customer Info</h4>
            <p class="thank-you__shipping">Shipping Address</p>
            <p class="paragraph">{{ $orderCustomer->customer_first_name }} {{ $orderCustomer->customer_last_name }}</p>
            <p class="paragraph">{{ $orderCustomer->shipping_street }}</p>
            <p class="paragraph">{{ $orderCustomer->shipping_city }} {{ strtoupper($orderCustomer->shipping_country)  }}</p>
            <p class="paragraph">{{ $orderCustomer->shipping_zip }}</p>

        </div>
        <div class="border-box thank-you__share-order">
            <h4 class="text-center">Share your order</h4>
            <p class="text-center">
                We hope you enjoyed shopping with us! Let your friends know about it and make our day!
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

            <textarea id="quote" rows="10">I just bought this awesome product. Thought I’d share this with you</textarea>
            <div class="d-flex justify-content-center">
                <button
                    id="share"
                    class="green-button"
                    @click="share"
                >
                    Share this Item!
                </button>
            </div>
        </div>
    </div>

@endsection
