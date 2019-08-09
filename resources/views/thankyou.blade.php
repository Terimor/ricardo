@extends('layouts.app')
<script>
    const upsells = {
      countryCode: '{{ $location->countryCode }}'
    }
</script>
<link rel="stylesheet" href="">
@section('content')
    <link rel="stylesheet" href="{{ asset('css/thank-you.css') }}">

    <div class="container thank-you">
        <p class="thank-you__order">Order: #TO-201908-718e1647-d1a0-4a15-a8b2-7efd8f6ab565</p>
        <h2 class="thank-you__name">Thank you Man</h2>
        <div class="border-box thank-you__container">
            <div id="map">
                <iframe class="resp-iframe" width="100%" height="300" id="gmap_canvas" src="https://maps.google.com/maps?q=Street%20S-number%20zipcode-4444%20City%20BR&amp;t=&amp;z=17&amp;ie=UTF8&amp;iwloc=&amp;output=embed" frameborder="0" scrolling="no" marginheight="0" marginwidth="0"></iframe>
            </div>
            <h4>Your order is confirmed</h4>
            <p>We’ve accepted your order, and we’re getting it ready. We’ll update on order status via email. A confirmation was sent to mail@gmail.com</p>
        </div>
        <div class="border-box thank-you__details">
            <h4>Details of your order</h4>

            <div class="thank-you__order">
                <div class="thank-you__order__image">
                    <img src="/images/Superior_Audio_Quality.png" alt="">
                    <div class="quantity">5</div>
                </div>
                <div class="thank-you__order__name">DogDentist</div>
            </div>

            <div class="thank-you__order">
                <div class="thank-you__order__image">
                    <img src="/images/Superior_Audio_Quality.png" alt="">
                    <div class="quantity">5</div>
                </div>
                <div class="thank-you__order__name">DogDentist</div>
            </div>

            <hr>

            <p class="paragraph d-flex justify-content-between"><span>Subtotal:</span><span>R$572.93</span></p>
            <p class="paragraph d-flex justify-content-between"><span>Payment method:</span><span>Credit Card</span></p>

            <hr>

            <p class="paragraph d-flex justify-content-between"><span>Order Total:</span><span class="bold">R$625.37</span></p>

        </div>
        <div class="border-box thank-you__customer-info">
            <h4>Customer Info</h4>
            <p class="thank-you__shipping">Shipping Address</p>
            <p class="paragraph">Marcus Crassus</p>
            <p class="paragraph">New Street 799</p>
            <p class="paragraph">York MO</p>
            <p class="paragraph">739788-ii</p>


        </div>
        <div class="border-box thank-you__share-order">
            <h4 class="text-center">Share your order</h4>
            <p class="text-center">We hope you enjoyed shopping with us! Let your friends know about it and make our day!</p>

            <ul id="social-media-tabs" class="nav nav-tabs">
                <li class="active">
                    <a href="#facebook" class="facebook-tab-header">
                        <div class="social-icon fb-icon"></div>
                        Facebook
                    </a>
                </li>
                <li>
                    <a href="#twitter" class="twitter-tab-header">
                        <div class="social-icon twitter-icon"></div>
                        Twitter
                    </a>
                </li>
            </ul>

            <textarea rows="10">I just bought this awesome product. Thought I’d share this with you</textarea>
            <div class="d-flex justify-content-center"><button class="green-button">Share this Item!</button></div>
        </div>
    </div>

@endsection
