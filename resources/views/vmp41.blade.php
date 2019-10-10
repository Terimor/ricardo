@extends('layouts.app', ['product' => $product])

@section('title', $product->page_title . ' ' . t('checkout.page_title'))

<link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">

@section('styles')
  <link rel="stylesheet" href="{{ asset('css/promo.css') }}">
  <link rel="stylesheet" href="{{ asset('css/vue-styles/js/views/promo.css') }}">
@endsection

@section('script')
    <script>
        var checkoutData = {
            countryCode: '{{ $countryCode }}',
            product: @json($product),
            countries: @json($countries),
            productImage: '{{$product->logo_image}}',
        };

        var recentlyBoughtNames = @json($recentlyBoughtNames);
        var recentlyBoughtCities = @json($recentlyBoughtCities)

        var loadedPhrases = @json($loadedPhrases);
    </script>

    <script src="{{ asset('js/views/promo.js') }}" defer></script>
@endsection

@section('content')

<div class="promo" id="promo" v-if="!hidePage">
    <preloader-3
        v-if="+queryParams.preload === 3"
        :country-code="checkoutData.countryCode"
        :show-preloader.sync="showPreloader">
    </preloader-3>

    <template v-if="+queryParams.preload !== 3 || !showPreloader">
        <notice></notice>
        <div class="container">
            <div
                class="promo__jumbotron"
                @click="scrollTo('.j-header-products')"
            >
                <h2 class="promo__heading-battery">
                    {{ t('checkout.promo.title') }}
                </h2>
                <div class="text-content">
                    <p class="promo__offer">
                        <span class="bold">
                            {{ t('checkout.header_banner.prefix') }}:
                        </span>
                        {{ $product->long_name }}
                    </p>
                    <div
                        class="promo__discount"
                        v-cloak
                    >
                        <div>
                            <span class="bold">
                                {{ t('checkout.header_banner.price') }}:
                            </span>
                            <span class="promo__price--double bold">
                                @{{countOfInstallments}} @{{ warrantyOldPrice }}
                            </span>
                        </div>
                        <span class="promo__price promo__text-red bold">
                            @{{countOfInstallments}} @{{ warrantyPriceText }}
                        </span>
                    </div>
                </div>
                <h3>
                    <span
                        class="promo__go-to-selector promo__text-red"
                        v-cloak
                    >
                        @{{ textPromoDiscount }}
                    </span>
                </h3>
                <div class="promo__subtitle">
                    <div>
                        {{ t('checkout.promo.subtitle') }}
                    </div>
                </div>
            </div>
            <div class="promo__installments">
                <select-field
                    v-if="withInstallments"
                    theme="variant-1"
                    :rest="{
                        placeholder: 'Installments'
                    }"
                    :list="$options.installmentsList"
                    v-model="installments"
                    @input="getImplValue"
                />
            </div>
            <h2 class="promo__title j-header-products">{{ t('checkout.secure_deal') }}</h2>
            <div
                class="row promo__products-row"
                v-cloak
            >
                <div
                    class="col-12 col-md-4 promo__card-wrapper"
                    v-for="item in purchase"
                >
                    <div
                        class="promo__card"
                        :class="{
                            'selected-promotion': selectedPlan === item.discountName,
                            'most-popular': item.isBestseller,
                            'most-profitable': item.isPopular,
                            'starter': item.discountName === '',
                        }"
                        @click="setSelectedPlan(item.discountName || 'STARTER', item.totalQuantity)"
                    >
                    <div class="promo__product-info">
                        <div
                            class="promo__heading"
                            :class="{
                                'most-popular': item.discountName === 'BESTSELLER',
                                'most-profitable': item.discountName === 'BEST DEAL',
                                'starter': item.discountName === '',
                            }"
                            style="max-width: 204px;"
                        >
                            <strong>
                                @{{ item.discountName || textDiscountStarter }}
                            </strong>
                        </div>
                        <div class="promo__product-content">
                            <img
                                :src="item.image"
                                :alt="item.text"
                                class="promo__discount-image"
                            >
                            <div class="promo__product-info-wrapper">
                                <strong class="promo__discount-text">@{{ item.text }}</strong>
                                <div class="products-price">
                                    <p class="promo__discount">
                                        <span class="promo__price--double bold">@{{countOfInstallments}} @{{ item.price }}</span>
                                        <span class="promo__price promo__text-red bold">@{{countOfInstallments}} @{{ item.newPrice }}</span>
                                    </p>
                                </div>
                                <div class="promo__fifty-discount">
                                    <p v-html="item.discountText"></p>
                                </div>
                            </div>
                        </div>
                        </div>
                        <green-button class="promo__add-button">
                            {{ t('checkout.add_to_cart') }}
                        </green-button>
                    </div>

                </div>
            </div>
            <template v-if="!selectedPlan">
                <section class="carousel-section">
                    <h1 class="promo__title">{{ t('checkout.also_featured_in') }}</h1>
                    <carousel
                        class="promo__carousel"
                        :items="4"
                        :nav="false"
                        :responsive="{
                            0:{
                                items: 1,
                                nav: false,
                                dots: false
                            },
                            480:{
                                items: 4,
                            }
                        }"
                    >
                        <img class="promo__carousel-img" src="https://static-backend.saratrkr.com/image_assets/technatic_1.png" alt="">
                        <img class="promo__carousel-img" src="https://static-backend.saratrkr.com/image_assets/Best_Product.png" alt="">
                        <img class="promo__carousel-img" src="https://static-backend.saratrkr.com/image_assets/Gadgetify_1.png" alt="">
                        <img class="promo__carousel-img" src="https://static-backend.saratrkr.com/image_assets/NewYourToday_3.png" alt="">
                    </carousel>
                </section>
                <section class="promo__reviews">
                    <h2 class="promo__title">
                        {{ t('checkout.happy_users', ['product' => $product->skus[0]['name']]) }}
                    </h2>
                    <div class="promo__review">
                        <div class="col-md-3 col-sm-3 col-xs-12">
                            <div class="promo__review-feedback">
                                <img class="lazy" src="https://static-backend.saratrkr.com/image_assets/third_1.jpg">
                            </div>
                            <div class="section-text stars">
                                @for ($i = 1; $i <= 5; $i++)
                                    <i class="fa fa-star"></i>
                                @endfor
                            </div>
                            <div class="section-text name">Harriet S.</div>
                        </div>
                        <div class="col-md-9 col-sm-9 col-xs-12 review-text">
                            <div class="section-text promo__review-title">
                                My best companions!
                            </div>
                            <div>
                                The color wasn't what I expected but other than that, perfect! Seems to last quite a while and I enjoy not having to untangle cords anymore.
                            </div>
                        </div>
                    </div>
                    <div class="promo__review reverse">
                        <div class="col-md-3 col-sm-3 col-xs-12">
                            <div class="promo__review-feedback">
                                <img class="lazy" src="https://static-backend.saratrkr.com/image_assets/first_1.jpg">
                            </div>
                            <div class="section-text stars">
                                @for ($i = 1; $i <= 5; $i++)
                                    <i class="fa fa-star"></i>
                                @endfor
                            </div>
                            <div class="section-text name">Adrian P.</div>
                        </div>
                        <div class="col-md-9 col-sm-9 col-xs-12 review-text">
                            <div class="section-text promo__review-title">
                                Better than expected
                            </div>
                            <div>
                                Love the color, love the style, and comfortable too! The battery lasts for ages and I like that it charges in the case. Well worth the money.
                            </div>
                        </div>
                    </div>
                    <div class="promo__review">
                        <div class="col-md-3 col-sm-3 col-xs-12">
                            <div class="promo__review-feedback">
                                <img class="lazy" src="https://static-backend.saratrkr.com/image_assets/second_1.jpg">
                            </div>
                            <div class="section-text stars">
                                @for ($i = 1; $i <= 5; $i++)
                                    <i class="fa fa-star"></i>
                                @endfor
                            </div>
                            <div class="section-text name">Jack P.</div>
                        </div>
                        <div class="col-md-9 col-sm-9 col-xs-12 review-text">
                            <div class="section-text promo__review-title">
                                Thoroughly worth the money
                            </div>
                            <div>
                                I looked at other wireless earphones and these were the cheapest.
                                I didn't think they would be any good but I tried my friends and these are far better! The sound quality is good and so is the carry case. I love them.
                            </div>
                        </div>
                    </div>
                </section>
            </template>
        </div>
        <div
            v-if="!isShowVariant"
            class="j-variant-section"
            :class="{
                'promo__select-variant-wrapper': hasTimer !== null
            }">
            <div
                v-if="selectedPlan"
                class="promo__select-variant"
            >
                {{ t('checkout.select_variant') }}
            </div>
        </div>
        <template v-if="selectedPlan && !isShowVariant">
            <div class="promo__choose-product">
                <div class="promo__choose-product-item">
                    <div
                        v-for="variantItem in variantList"
                        class="promo__variant-item"
                        @click="setSelectedVariant(variantItem.value)"
                        :class="{ 'selected-variant': form.variant === variantItem.value }"
                    >
                        <div class="promo__choose-product-item-color">
                            <div class="color">
                                <div class="choose-color">
                                    <div
                                        class="promo__variant-icon-wrapper"
                                        :class="{ 'selected-variant': form.variant === variantItem.value }"
                                    >
                                        <img
                                            class="promo__variant-icon"
                                            :src="variantItem.imageUrl"
                                            :alt="variantItem.label">
                                    </div>
                                </div>
                                </div>
                            </div>
                            <div class="variant-name">@{{ variantItem.label }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
        <section class="j-complete-order">
            <div
                class="promo__complete-order"
                v-if="form.variant"
            >
                <h2 class="promo__title">
                    {{ t('checkout.complete_order') }}
                </h2>
                <div class="promo__step-title">{{ t('checkout.step') }} 1: {{ t('checkout.pay_securely') }}</div>
                <div class="promo__paypal-button-wrapper">
                    <paypal-button
                        :style="{ width: '400px' }"
                        :create-order="paypalCreateOrder"
                        :on-approve="paypalOnApprove"
                        :$v="$v.form.deal"
                        @click="paypalSubmit"
                    >{{ t('checkout.paypal.risk_free') }}</paypal-button>
                </div>
                <div class="promo__alternative-payment">
                    {{ t('checkout.pay_securely_also') }}
                </div>
                <div class="promo__row-payments">
                    <payment-type-radio-list
                        class="promo__credit-card-switcher"
                        v-model="form.paymentType"
                        :country="form.country"
                        @input="activateForm"
                    />
                </div>
                <div class="main__deal promo__form-wrapper payment-form j-payment-form">
                    <payment-form
                        v-if="form && form.paymentType && isFormShown"
                        first-title="{{ t('checkout.step') }} 4: {{ t('checkout.contact_information') }}"
                        second-title="{{ t('checkout.step') }} 5: {{ t('checkout.delivery_address') }}"
                        third-title="{{ t('checkout.step') }} 6: {{ t('checkout.payment_details') }}"
                        :state-list="stateList"
                        :$v="$v"
                        :installments="form.installments"
                        :payment-form="form"
                        :has-warranty="true"
                        :country-code="checkoutData.countryCode"
                        :is-brazil="checkoutData.countryCode === 'br'"
                        :country-list="countriesList"
                        :quantity-of-installments="countOfInstallments"
                        :warranty-price-text="warrantyPriceText"
                    />
                </div>
            </div>
        </section>
        <section class="promo__scroll-to-top">
            <div class="container">
                <div class="promo__people-rate-block">
                    <h1 class="bold promo__title">
                        Revolutionary Sound Quality at an Unbeatable Price
                    </h1>
                    <h2 class="bold promo__people-rate">
                        Many audio and tech companies tried to shut down this cheaper alternative to their overpriced bluetooth headphones. However, at last, finally {{ $product->skus[0]['name'] }} has made it to the public
                    </h2>
                    <green-button @click="scrollTo('.j-header-products')">
                        {{ t('checkout.people_rate.button') }}
                    </green-button>
                </div>
            </div>
        </section>
    </template>

    <leave-modal v-if="+queryParams.exit === 1" />
</div>

@include('layouts.footer')

@endsection
