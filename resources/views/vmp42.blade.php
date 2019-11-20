@extends('layouts.app', ['product' => $product])

@section('title', $product->page_title . ' ' . t('checkout.page_title'))

<link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet" media="none" onload="styleOnLoad.call(this)">

@section('styles')
    <link rel="stylesheet" href="{{ mix_cdn('assets/css/promo.css') }}" media="none" onload="styleOnLoad.call(this)">
    <link rel="stylesheet" href="{{ mix_cdn('assets/js/views/promo.vue.css') }}" media="none" onload="styleOnLoad.call(this, 'css2-hidden')">
@endsection

@section('script')
    <script>
        var checkoutData = {
            langCode: '{{ $langCode }}',
            countryCode: '{{ $countryCode }}',
            product: @json($product),
            countries: @json($countries),
            productImage: '{{$product->logo_image}}',
            paymentMethods: @json($setting['payment_methods']),
        };

        var recentlyBoughtNames = @json($recentlyBoughtNames);
        var recentlyBoughtCities = @json($recentlyBoughtCities)

        var loadedPhrases = @json($loadedPhrases);
    </script>

    <script src="{{ mix_cdn('assets/js/views/promo.js') }}" defer></script>
@endsection

@section('content')

    <div class="promo" id="promo">
        <preloader-3
            v-if="showPreloader"
            :country-code="form.country"
            :show-preloader.sync="showPreloader">
        </preloader-3>

        <template v-if="!showPreloader">
            <notice></notice>


            <!-- promo__jumbotron -->
            <div class="fade-wrapper" :class="{'fade-wrapper_empty': !isShownJumbotron}">
                <div v-if="isShownJumbotron" class="container">
                    <div class="promo__jumbotron"
                         @click="scrollTo('.j-header-products')">
                        <h2 class="promo__heading-battery">{{ t('checkout.promo.title') }}</h2>
                        <div class="text-content">
                            <p class="promo__offer">
                                <span class="bold">{{ t('checkout.header_banner.prefix') }}:</span>
                                {{ $product->long_name }}
                            </p>
                            <div class="promo__discount" v-cloak>
                                <div>
                                    <span class="bold">{{ t('checkout.header_banner.price') }}:</span>
                                    <span class="promo__price--double bold">
                                        @{{countOfInstallments}} @{{ promoOldPrice }}
                                    </span>
                                </div>
                                <span class="promo__price promo__text-red bold">
                                    @{{countOfInstallments}} @{{ promoPriceText }}
                                </span>
                            </div>
                        </div>
                        <h3>
                            <span class="promo__go-to-selector promo__text-red" v-cloak>
                                @{{ textPromoDiscount }}
                            </span>
                        </h3>
                        <div class="promo__subtitle">
                            <div>{{ t('checkout.promo.subtitle') }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- promo__jumbotron end -->

            <!-- promo__wrapper -->
            <div class="promo__wrapper" :style="{height: carouselFormHeight}">

                <div class="promo__step promo__products">
                    <div class="container">
                        <div class="row promo__products-row" v-cloak>

                            <!-- promo__installments -->
                            <div class="container">
                                <div class="promo__installments">
                                    <Installments
                                      popperClass="emc1-popover-variant"
                                      :extra-fields="extraFields"
                                      :form="form" />
                                </div>
                            </div>
                            <!-- promo__installments end -->
            
                            <div class="col-12">
                                <h2 class="promo__title j-header-products">{{ t('checkout.secure_deal') }}</h2>
                            </div>
                            <div class="col-12 col-md-4 promo__card-wrapper" v-for="item in purchase">
                                <div class="promo__card"
                                     :class="{
                                         'selected-promotion': selectedPlan === item.totalQuantity,
                                         'most-popular': item.isBestseller,
                                         'most-profitable': item.isPopular,
                                         'starter': item.discountName === '',
                                     }"
                                     @click="setSelectedPlan(item.totalQuantity)">


                                    <div class="promo__product-info">
                                        <div class="promo__heading"
                                             :class="{
                                                'most-popular': item.isBestseller,
                                                'most-profitable': item.isPopular,
                                                'starter': item.discountName === '',
                                             }"
                                             style="max-width: 204px;">
                                            <strong>@{{ item.discountName || textDiscountStarter }}</strong>
                                        </div>
                                        <div class="promo__product-content">
                                            <img
                                                 :alt="item.textComposite"
                                                 class="promo__discount-image"
                                                 :src="productImages[item.totalQuantity] || item.image"
                                            >
                                            <div class="promo__product-info-wrapper">
                                                <strong class="promo__discount-text">@{{ item.textComposite }}</strong>
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
                                    <green-button :class="['promo__add-button', {'disabled': selectedPlan === item.totalQuantity}]">
                                        <span v-if="selectedPlan === item.totalQuantity">{{ t('checkout.selected') }}</span>
                                        <span v-else>{{ t('checkout.add_to_cart') }}</span>
                                    </green-button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="isShowVariant" class="promo__step promo__choose-product">

                    <div class="j-variant-section"
                         :class="{
                            'promo__select-variant-wrapper': hasTimer !== null
                         }"
                    >
                        <div v-if="selectedPlan" class="promo__select-variant">
                            <div class="container">{{ t('checkout.select_variant') }}</div>
                        </div>
                    </div>

                    <div class="container">
                        <div class="promo__choose-product-item">
                            <div v-for="variantItem in variantList"
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
                        <button @click="prevStep()" class="promo__back-btn"> < {{ t('checkout.payment_form.back_to_selection') }}</button>
                    </div>
                </div>

                <section class="promo__step j-complete-order">
                    <div class="promo__complete-order"
                         v-if="form.variant"
                    >
                        <h2 class="promo__title">{{ t('checkout.complete_order') }}</h2>
                        <div class="promo__step-title">{{ t('checkout.step') }} 1: {{ t('checkout.pay_securely') }}</div>
                        <div class="promo__paypal-button-wrapper">
                            <paypal-button
                                v-show="form.installments === 1"
                                :style="{ 'max-width': '400px' }"
                                :create-order="paypalCreateOrder"
                                :on-approve="paypalOnApprove"
                                :$vdeal="$v.form.deal"
                                @click="paypalSubmit"
                            >{{ t('checkout.paypal.risk_free') }}</paypal-button>
                            <p v-if="paypalPaymentError" id="paypal-payment-error" class="error-container" v-html="paypalPaymentError"></p>
                        </div>
                        <div class="promo__alternative-payment">
                            {{ t('checkout.pay_securely_also') }}
                        </div>
                        <div class="promo__row-payments">
                            <payment-provider-radio-list
                                class="promo__credit-card-switcher"
                                v-model="form.paymentProvider"
                                @input="activateForm"
                            />
                        </div>

                        <button @click="prevStep()" class="promo__back-btn"> < {{ t('checkout.payment_form.back_to_selection') }}</button>

                        <div class="main__deal promo__form-wrapper payment-form j-payment-form">
                            <payment-form
                                v-if="form && form.paymentProvider && isFormShown"
                                first-title="{{ t('checkout.step') }} 2: {{ t('checkout.contact_information') }}"
                                second-title="{{ t('checkout.step') }} 3: {{ t('checkout.delivery_address') }}"
                                third-title="{{ t('checkout.step') }} 4: {{ t('checkout.payment_details') }}"
                                :state-list="stateList"
                                :$v="$v"
                                :installments="form.installments"
                                :payment-form="form"
                                :has-warranty="true"
                                :country-code="form.country"
                                :country-list="countriesList"
                                :quantity-of-installments="countOfInstallments"
                                :warranty-price-text="warrantyPriceText"
                                :extra-fields="extraFields"
                            />
                        </div>
                    </div>
                </section>

            </div>
            <!-- promo__wrapper end -->


            <template v-if="!selectedPlan">
                <section class="carousel-section">
                    <div class="container">
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
                            <img class="promo__carousel-img"
                                 src="https://static-backend.saratrkr.com/image_assets/technatic_1.png" alt="">
                            <img class="promo__carousel-img"
                                 src="https://static-backend.saratrkr.com/image_assets/Best_Product.png" alt="">
                            <img class="promo__carousel-img"
                                 src="https://static-backend.saratrkr.com/image_assets/Gadgetify_1.png" alt="">
                            <img class="promo__carousel-img"
                                 src="https://static-backend.saratrkr.com/image_assets/NewYourToday_3.png" alt="">
                        </carousel>
                    </div>
                </section>

                <!--<section class="promo__reviews">
                    <div class="container">
                        <h2 class="promo__title">
                            {{ t('checkout.happy_users', ['product' => $product->product_name]) }}
                        </h2>
                        <div class="promo__review" v-for="review in mockData.reviews">
                            <div class="col-md-3 col-sm-3 col-xs-12 review-head">
                                <div class="promo__review-feedback">
                                    <img class="lazy"
                                         :src="review.user.userImg">
                                </div>
                                <div class="section-text stars">
                                    <i class="fa fa-star"
                                       v-for="rateStar in review.rate"></i>
                                </div>
                                <div class="section-text name">@{{ review.user.userName }}</div>
                            </div>
                            <div class="col-md-9 col-sm-9 col-xs-12 review-text">
                                <div class="section-text promo__review-title">
                                    @{{ review.title }}
                                </div>
                                <div>
                                    @{{ review.text }}
                                </div>
                            </div>
                        </div>
                    </div>
                </section>-->
            </template>


            <section v-if="!selectedPlan" class="promo__scroll-to-top">
                <div class="container">
                    <div class="promo__people-rate-block">
                        <h1 class="bold promo__title">
                            {{ t('checkout.vmp41.footer_title') }}
                        </h1>
                        <h2 class="bold promo__people-rate">
                            {{ t('checkout.vmp41.footer_subtitle.first') }}
                            {{ isset($product->skus[0]) ? $product->skus[0]['name'] : '' }}
                            {{ t('checkout.vmp41.footer_subtitle.second') }}
                        </h2>
                        <green-button @click="scrollTo('.j-header-products')">
                            {{ t('checkout.people_rate.button') }}
                        </green-button>
                    </div>
                </div>
            </section>
        </template>

        <leave-modal
            v-if="+queryParams.exit === 1"
            :show-preloader="showPreloader"/>
    </div>

    <div class="sticky-footer">
        @include('layouts.footer')
    </div>

    @include('layouts.footer')

@endsection
