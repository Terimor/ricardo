@extends('layouts.app')

@section('script')
    <script src="{{ asset('js/views/promo.js') }}"></script>
@stop

@section('content')
@section('title', $product->skus[0]['name'] . ' Checkout')

<link rel="stylesheet" href="{{ asset('css/promo.css') }}">
<link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">

<div class="promo" id="promo">
    <div class="container">

        <div class="border-dashed">
            <h2 class="heading-battery">
                Limited offer
            </h2>
            <div class="text-content">
                <p class="offer">
                    <span class="bold">
                        Special Offer:
                    </span>
                    EchoBeat - Wireless 3D Sound
                </p>
                <div id="discount-header" class="discount1">
                    <div class="price-mobile">
                        <span id="price-element" class="bold">
                            Price:
                        </span>
                        <span class="double-price bold">
                            ₴3,598
                        </span>
                    </div>
                    <span class="text-red price bold">
                        ₴1,799
                    </span>
                </div>
            </div>
            <h3>
                <a href="#section-6" class="go-to-selector text-red">BUY NOW - 50% OFF &amp; Free Shipping</a>
            </h3>
            <div class="section-subtitle">
                <div>
                    Take a look at these unbeatable offers - Stock is selling out fast
                </div>
            </div>
        </div>

        <h2 id="header-products">Secure Your Discounted Deal Now</h2>

        <div class="row">

            <div class="col-12 col-md-4">
                <div
                    class="card starter"
                    :class="{ 'selected-promotion': selectedPlan === 'starter' }"
                >
                    <div class="heading" style="max-width: 204px;"><strong>STARTER CHOICE</strong></div>
                    <img src="https://static-backend.saratrkr.com/image_assets/EchoBeat_1_piece_1.png" alt="" class="starter-img">
                    <div class="product-info"><strong>1 EchoBeat i7</strong>
                        <p class="multi-prod-sec">+ additional discount on all items</p>
                        <div class="products-price">
                            <p class="discount">
                                <span class="double-price bold">₴3,598</span>
                                <span class="price text-red bold">₴1,799</span>
                            </p>
                        </div>
                        <div class="fifty-discount">
                            <p>GET 50% OFF TODAY + FREE SHIPPING</p>
                        </div>
                        <button
                            type="button"
                            class="green-button-animated"
                            @click="setSelectedPlan('starter')"
                        >
                            ADD TO CART
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div
                    :class="{ 'selected-promotion': selectedPlan === 'most-popular' }"
                    class="card most-popular"
                >
                    <div class="heading most-popular" style="max-width: 204px;"><strong>MOST POPULAR CHOICE</strong></div>
                    <img src="https://static-backend.saratrkr.com/image_assets/EchoBeat_21_piece_1.png" alt="" class="starter-img">
                    <div class="product-info"><strong>1 EchoBeat i7</strong>
                        <p class="multi-prod-sec">+ additional discount on all items</p>
                        <div class="products-price">
                            <p class="discount">
                                <span class="double-price bold">₴3,598</span>
                                <span class="price text-red bold">₴1,799</span>
                            </p>
                        </div>
                        <div class="fifty-discount">
                            <p>GET 50% OFF TODAY + FREE SHIPPING</p>
                        </div>
                        <button
                            type="button"
                            class="green-button-animated"
                            @click="setSelectedPlan('most-popular')"
                        >
                            ADD TO CART
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div
                    class="card most-profitable"
                    :class="{ 'selected-promotion': selectedPlan === 'most-profitable' }"
                >
                    <div class="heading most-profitable" style="max-width: 204px;"><strong>MOST PROFITABLE CHOICE</strong></div>
                    <img src="https://static-backend.saratrkr.com/image_assets/EchoBeat_32_piece_1.png" alt="" class="starter-img">
                    <div class="product-info"><strong>1 EchoBeat i7</strong>
                        <p class="multi-prod-sec">+ additional discount on all items</p>
                        <div class="products-price">
                            <p class="discount">
                                <span class="double-price bold">₴3,598</span>
                                <span class="price text-red bold">₴1,799</span>
                            </p>
                        </div>
                        <div class="fifty-discount">
                            <p>GET 50% OFF TODAY + FREE SHIPPING</p>
                        </div>
                        <button
                            type="button"
                            class="green-button-animated"
                            @click="setSelectedPlan('most-profitable')"
                        >
                            ADD TO CART
                        </button>
                    </div>
                </div>
            </div>

        </div>

        <section class="carousel">

            <h1 class="promo-title-big">Also featured in</h1>

        </section>

        <section class="reviews">

            <h2 class="promo-title-big">
                Happy EchoBeat users
            </h2>

            <div class="review">
                <div class="col-md-3 col-sm-3 col-xs-12 nplr ">
                    <div class="feedback-vmp41"><img class="lazy" src="https://static-backend.saratrkr.com/image_assets/third_1.jpg"></div>
                    <div class="section-text stars"><i class="fa fa-star"></i> <i class="fa fa-star"></i> <i class="fa fa-star"></i> <i class="fa fa-star"></i> <i class="fa fa-star"></i></div>
                    <div class="section-text name">Harriet S.</div>
                </div>
                <div class="col-md-9 col-sm-9 col-xs-12 nplr review-text" id="responsive-center">
                    <div class="section-text title">
                        My best companions!
                    </div>
                    <p class="section-text desc"></p>
                    <div>The color wasn't what I expected but other than that, perfect! Seems to last quite a while and I enjoy not having to untangle cords anymore.</div>
                    <p></p>
                </div>
            </div>

            <div class="review reverse">
                <div class="col-md-3 col-sm-3 col-xs-12 nplr ">
                    <div class="feedback-vmp41"><img class="lazy" src="https://static-backend.saratrkr.com/image_assets/first_1.jpg"></div>
                    <div class="section-text stars"><i class="fa fa-star"></i> <i class="fa fa-star"></i> <i class="fa fa-star"></i> <i class="fa fa-star"></i> <i class="fa fa-star"></i></div>
                    <div class="section-text name">Adrian P.</div>
                </div>
                <div class="col-md-9 col-sm-9 col-xs-12 nplr review-text" id="responsive-center">
                    <div class="section-text title">
                        Better than expected
                    </div>
                    <p class="section-text desc"></p>
                    <div>
                        Love the color, love the style, and comfortable too! The battery lasts for ages and I like that it charges in the case. Well worth the money.
                    </div>
                    <p></p>
                </div>
            </div>

            <div class="review">
                <div class="col-md-3 col-sm-3 col-xs-12 nplr ">
                    <div class="feedback-vmp41"><img class="lazy" src="https://static-backend.saratrkr.com/image_assets/second_1.jpg"></div>
                    <div class="section-text stars"><i class="fa fa-star"></i> <i class="fa fa-star"></i> <i class="fa fa-star"></i> <i class="fa fa-star"></i> <i class="fa fa-star"></i></div>
                    <div class="section-text name">Jack P.</div>
                </div>
                <div class="col-md-9 col-sm-9 col-xs-12 nplr review-text" id="responsive-center">
                    <div class="section-text title">
                        Thoroughly worth the money
                    </div>
                    <p class="section-text desc"></p>
                    <div>
                        I looked at other wireless earphones and these were the cheapest.
                        I didn't think they would be any good but I tried my friends and these are far better! The sound quality is good and so is the carry case. I love them.
                    </div>
                    <p></p>
                </div>
            </div>

        </section>

    </div>

    <section class="scroll-to-top">

        <div class="container">
            <div>
                <h1 class="bold promo-title-big">Revolutionary Sound Quality at an Unbeatable Price</h1>
                <h2 id="people-rate" class="bold people-rate">Many audio and tech companies tried to shut down this cheaper alternative to their overpriced bluetooth headphones. However, at last, finally EchoBeat has made it to the public</h2>
                <a class="green-button-animated" href="#row-products">Click here to claim your special 50% discount - This incredible offer will NOT last</a>
            </div>
        </div>

    </section>

    <footer class="footer">
        <ul class="footer-row">
            <li>
                <a href="#!">Contact us</a>
            </li>
            <li>
                <a href="#!">Terms of business</a>
            </li>
            <li>
                <a href="#!">Privacy</a>
            </li>
            <li>
                <a href="#!">Affiliate program</a>
            </li>
        </ul>
    </footer>
</div>

@endsection
