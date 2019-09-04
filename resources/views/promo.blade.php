@extends('layouts.app')

@section('title', $product->skus[0]['name'] . ' Checkout')

<link rel="stylesheet" href="{{ asset('css/promo.css') }}">
<link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">

@section('script')
    <script defer>
        const checkoutData = {
            countryCode: 'BR',
            product: @json($product),
        };
        const product = @json($product)

        console.log(product)
    </script>

    <script src="{{ asset('js/views/promo.js') }}" defer></script>
@endsection

@section('content')

<div class="promo" id="promo">
    <div class="container">

        <div class="promo__jumbotron" @click="scrollTo('.j-header-products')">
            <h2 class="promo__heading-battery">
                Limited offer
            </h2>
            <div class="text-content">
                <p class="promo__offer">
                    <span class="bold">
                        Special Offer:
                    </span>
                    {{ $product->long_name }}
                </p>
                <div class="promo__discount">
                    <div>
                        <span class="bold">
                            Price:
                        </span>
                        <span class="promo__price--double bold">
                            @{{quantityOfInstallments}} @{{ warrantyOldPrice }}
                        </span>
                    </div>
                    <span class="promo__price text-red bold">
                        @{{quantityOfInstallments}} @{{warrantyPriceText}}
                    </span>
                </div>
            </div>
            <h3>
                <a href="#section-6" class="go-to-selector text-red">BUY NOW - @{{ discount }}% OFF &amp; Free Shipping</a>
            </h3>
            <div class="promo__subtitle">
                <div>
                    Take a look at these unbeatable offers - Stock is selling out fast
                </div>
            </div>
        </div>

        <div class="promo__installments">
            <select-field
                theme="variant-1"
                :rest="{
                    placeholder: 'Installments'
                }"
                :list="$options.installmentsList"
                v-model="installments"
                @input="getImplValue"
            />
        </div>

        <h2 class="header-products j-header-products">Secure Your Discounted Deal Now</h2>

        <div
            class="row"
            v-cloak
        >

            <div
                class="col-12 col-md-4 card-wrapper"
                v-for="item in purchase"
            >
                <div
                    class="card"
                    :class="{
                        'selected-promotion': selectedPlan === item.discountName,
                        'most-popular': item.discountName === 'BESTSELLER',
                        'most-profitable': item.discountName === 'BEST DEAL',
                        'starter': item.discountName === '',
                    }"
                >
                <div class="promo__product-info">
                    <div
                        class="heading"
                        :class="{
                            'most-popular': item.discountName === 'BESTSELLER',
                            'most-profitable': item.discountName === 'BEST DEAL',
                            'starter': item.discountName === '',
                        }"
                        style="max-width: 204px;"
                    >
                        <strong>
                            @{{ item.discountName || 'STARTER CHOICE' }}
                        </strong>
                    </div>
                    <strong class="promo__discount-text">@{{ item.text }}</strong>
                    <div class="products-price">
                        <p class="discount">
                            <span class="promo__price--double bold">@{{ item.price }}</span>
                            <span class="price text-red bold">@{{ item.newPrice }}</span>
                        </p>
                    </div>
                    <div class="fifty-discount">
                        <p>@{{ item.discountText }}</p>
                    </div>
                    </div>
                    <green-button
                        class="promo__add-button"
                        @click="setSelectedPlan(item.discountName)"
                    >
                        ADD TO CART
                    </green-button>
                </div>
            </div>

            <!-- <div class="col-12 col-md-4">
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
            </div> -->

        </div>

        <template v-if="!selectedPlan">

            <section class="carousel-section">

                <h1 class="promo__title">Also featured in</h1>

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

                <h2 class="promo__title">
                    Happy EchoBeat users
                </h2>

                <div class="review">
                    <div class="col-md-3 col-sm-3 col-xs-12 nplr ">
                        <div class="feedback"><img class="lazy" src="https://static-backend.saratrkr.com/image_assets/third_1.jpg"></div>
                        <div class="section-text stars"><i class="fa fa-star"></i> <i class="fa fa-star"></i> <i class="fa fa-star"></i> <i class="fa fa-star"></i> <i class="fa fa-star"></i></div>
                        <div class="section-text name">Harriet S.</div>
                    </div>
                    <div class="col-md-9 col-sm-9 col-xs-12 nplr review-text">
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
                        <div class="feedback"><img class="lazy" src="https://static-backend.saratrkr.com/image_assets/first_1.jpg"></div>
                        <div class="section-text stars"><i class="fa fa-star"></i> <i class="fa fa-star"></i> <i class="fa fa-star"></i> <i class="fa fa-star"></i> <i class="fa fa-star"></i></div>
                        <div class="section-text name">Adrian P.</div>
                    </div>
                    <div class="col-md-9 col-sm-9 col-xs-12 nplr review-text">
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
                        <div class="feedback"><img class="lazy" src="https://static-backend.saratrkr.com/image_assets/second_1.jpg"></div>
                        <div class="section-text stars"><i class="fa fa-star"></i> <i class="fa fa-star"></i> <i class="fa fa-star"></i> <i class="fa fa-star"></i> <i class="fa fa-star"></i></div>
                        <div class="section-text name">Jack P.</div>
                    </div>
                    <div class="col-md-9 col-sm-9 col-xs-12 nplr review-text">
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

    <div class="col-choose-product j-variant-section">
        <div class="choose-product-item">
            <div
                v-for="variantItem in variantList"
                class="variant-item"
                @click="setSelectedVariant(variantItem.value)"
                :class="{ 'selected-variant': variant === variantItem.value }"
            >
                <div class="choose-product-item-color">
                    <div class="color">
                        <div class="choose-color">
                            <div class="promo__variant-icon-wrapper"
                                :class="{ 'selected-variant': variant === variantItem.value }"
                            >
                                <img
                                    class="promo__variant-icon"
                                    :src="variantItem.imageUrl"
                                    alt="">
                            </div>
                        </div>
                        </div>
                    </div>
                    <div class="variant-name">@{{ variantItem.label }}</div>
                </div>
            </div>
        </div>
    </div>

    <template v-show="variant">
        <section class="promo__complete-order j-complete-order">
            <h2 class="promo__title">
                Complete order
            </h2>

            <div class="promo__step-title">Step 1: Pay securely with:</div>

            <!-- <paypal-button
                :create-order="paypalCreateOrder"
                :on-approve="paypalOnApprove"
                :$v="true"
            >Buy Now Risk Free PAYPAL</paypal-button> -->

            <div>Or, you can also pay securely with:</div>

            <div class="promo__row-credit-cards">
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

            <div class="main__deal promo__form-wrapper payment-form j-payment-form">
                <payment-form
                    v-if="form && form.paymentType"
                    first-title="Step 2: Contact Information"
                    second-title="Step 3: Delivery Address"
                    third-title="Step 4: Payment Details"
                    :state-list="stateList"
                    :$v="$v"
                    :installments="form.installments"
                    :payment-form="form"
                    :has-warranty="true"
                    :country-code="checkoutData.countryCode"
                    :is-brazil="checkoutData.countryCode === 'BR'"
                    :country-list="mockData.countryList"
                    :quantity-of-installments="quantityOfInstallments"
                    :warranty-price-text="warrantyPriceText"
                />
            </div>

        </section>
    </template>

    <section class="promo__scroll-to-top">

        <div class="container">
            <div class="promo__people-rate-block">
                <h1 class="bold promo__title">Revolutionary Sound Quality at an Unbeatable Price</h1>
                <h2 class="bold promo__people-rate">Many audio and tech companies tried to shut down this cheaper alternative to their overpriced bluetooth headphones. However, at last, finally EchoBeat has made it to the public</h2>
                <green-button
                    @click="scrollTo('.j-header-products')"
                >
                    Click here to claim your special 50% discount - This incredible offer will NOT last
                </green-button>
            </div>
        </div>

    </section>

    <footer class="promo__footer">
        <ul class="promo__footer-row">
            <li>
                <a href="#!" class="promo__footer-link">Contact us</a>
            </li>
            <li>
                <a href="#!" class="promo__footer-link">Terms of business</a>
            </li>
            <li>
                <a href="#!" class="promo__footer-link">Privacy</a>
            </li>
            <li>
                <a href="#!" class="promo__footer-link">Affiliate program</a>
            </li>
        </ul>
    </footer>
</div>

@endsection
