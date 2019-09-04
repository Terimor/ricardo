@extends('layouts.app')

@section('title', $product->skus[0]['name'] . ' Checkout')

<link rel="stylesheet" href="{{ asset('css/promo.css') }}">
<link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">

@section('script')
    <script defer>
        const checkoutData = {
            countryCode: 'BR',
        }
    </script>
    <script src="{{ asset('js/views/promo.js') }}" defer></script>
@endsection

@section('content')

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
                        <green-button @click="setSelectedPlan('starter')">
                            ADD TO CART
                        </green-button>
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
                        <green-button @click="setSelectedPlan('most-popular')">
                            ADD TO CART
                        </green-button>
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
                        <green-button @click="setSelectedPlan('most-profitable')">
                            ADD TO CART
                        </green-button>
                    </div>
                </div>
            </div>

        </div>

        <template v-if="!selectedPlan">

            <section class="carousel-section">

                <h1 class="promo-title-big">Also featured in</h1>

                <carousel
                    class="carousel"
                    :items="4"
                    :nav="false"
                >
                    <img class="carousel-img" src="https://static-backend.saratrkr.com/image_assets/technatic_1.png" alt="">
                    <img class="carousel-img" src="https://static-backend.saratrkr.com/image_assets/Best_Product.png" alt="">
                    <img class="carousel-img" src="https://static-backend.saratrkr.com/image_assets/Gadgetify_1.png" alt="">
                    <img class="carousel-img" src="https://static-backend.saratrkr.com/image_assets/NewYourToday_3.png" alt="">
                </carousel>

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

        </template>

    </div>

    <div class="select-variant" v-if="selectedPlan">
        Please select your variant
    </div>

    <div id="variant-section" class="col-choose-product">
        <div class="choose-product-item">
            <div
                class="variant-item"
                @click="setSelectedVariant(1)"
                :class="{ 'selected-variant': selectedVariant === 1 }"
            >
                <div class="choose-product-item-color">
                    <div class="color">
                        <div class="choose-color">
                            <div class="red-item" :class="{ 'selected-variant': selectedVariant === 1 }" style="background-image: url(&quot;https://static-backend.saratrkr.com/image_assets/Echobeat-product.pro_-_Logo_Plus_Icons&quot;); background-size: 100%;"></div>
                        </div>
                    </div>
                    <div class="variant-name">EchoBeat7 White</div>
                </div>
            </div>
            <div
                class="variant-item"
                @click="setSelectedVariant(2)"
                :class="{ 'selected-variant': selectedVariant === 2 }"
            >
                <div class="choose-product-item-color">
                    <div class="color">
                        <div class="choose-color">
                            <div class="red-item" :class="{ 'selected-variant': selectedVariant === 2 }" style="background-image: url(&quot;https://static-backend.saratrkr.com/image_assets/EchoBeat_Black_product.png&quot;); background-size: 100%;"></div>
                        </div>
                    </div>
                    <div class="variant-name">EchoBeat7 Black</div>
                </div>
            </div>
            <div
                class="variant-item"
                @click="setSelectedVariant(3)"
                :class="{ 'selected-variant': selectedVariant === 3 }"
            >
                <div class="choose-product-item-color">
                    <div class="color">
                        <div class="choose-color">
                            <div class="red-item" :class="{ 'selected-variant': selectedVariant === 3 }" style="background-image: url(&quot;https://static-backend.saratrkr.com/image_assets/EchoBeat_GOLD_product.png&quot;); background-size: 100%;"></div>
                        </div>
                    </div>
                    <div class="variant-name">EchoBeat7 Gold</div>
                </div>
            </div>
            <div
                class="variant-item"
                @click="setSelectedVariant(4)"
                :class="{ 'selected-variant': selectedVariant === 4 }"
            >
                <div class="choose-product-item-color">
                    <div class="color">
                        <div class="choose-color">
                            <div class="red-item" :class="{ 'selected-variant': selectedVariant === 4 }" style="background-image: url(&quot;https://static-backend.saratrkr.com/image_assets/EchoBeat_RED_product.png&quot;); background-size: 100%;"></div>
                        </div>
                    </div>
                    <div class="variant-name">EchoBeat7 Red</div>
                </div>
            </div>
            <div
                class="variant-item"
                @click="setSelectedVariant(5)"
                :class="{ 'selected-variant': selectedVariant === 5 }"
            >
                <div class="choose-product-item-color">
                    <div class="color">
                        <div class="choose-color">
                            <div class="red-item" :class="{ 'selected-variant': selectedVariant === 5 }" style="background-image: url(&quot;https://static-backend.saratrkr.com/image_assets/EchoBeat_Pink_product.png&quot;); background-size: 100%;"></div>
                        </div>
                    </div>
                    <div class="variant-name">EchoBeat7 Pink</div>
                </div>
            </div>
        </div>
    </div>

    <section class="complete-order">

        <h2 class="promo-title-big">
            Complete order
        </h2>

        <div class="step-title">Step 1: Pay securely with:</div>

        <div id="paypal-button-container">

        <div>Or, you can also pay securely with:</div>

        <div class="row-credit-cards-flex">
            <radio-button-group
                :with-custom-labels="true"
                v-model="form.paymentType"
            >
                <div class="card-types">
                    <pay-method-item
                        v-for="item in cardNames"
                        :key="item.value"
                        :input="{
                            value: item.value,
                            imgUrl: item.imgUrl,
                        }"
                        :value="form.paymentType"
                    />
                </div>
            </radio-button-group>
        </div>

        <pre>
            @{{ cardNames }}
        </pre>

        <div class="form-fields">
            <payment-form
                v-if="form && form.paymentType"
                firstTitle="Step 4: Contact Information"
                secondTitle="Step 5: Delivery Address"
                thirdTitle="Step 6: Payment Details"
                :stateList="stateList"
                :$v="$v"
                :installments="form.installments"
                :paymentForm="form"
                :countryCode="checkoutData.countryCode"
                :isBrazil="checkoutData.countryCode === 'BR'"
                :countryList="mockData.countryList"
            />
        </div>

        <div class="step-title">Step 3: Delivery Address</div>
        <div class="step-title">Step 4: Payment Details</div>

    </section>

    <section class="scroll-to-top">

        <div class="container">
            <div class="people-rate-block">
                <h1 class="bold promo-title-big">Revolutionary Sound Quality at an Unbeatable Price</h1>
                <h2 id="people-rate" class="bold people-rate">Many audio and tech companies tried to shut down this cheaper alternative to their overpriced bluetooth headphones. However, at last, finally EchoBeat has made it to the public</h2>
                <green-button>
                    Click here to claim your special 50% discount - This incredible offer will NOT last
                </green-button>
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
