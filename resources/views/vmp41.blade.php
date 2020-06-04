@extends('layouts.app', ['product' => $product])

@section('title', $product->page_title . ' ' . t('checkout.page_title'))


@section('js_data')

  <script type="text/javascript">
    js_data.customer = @json($customer, JSON_UNESCAPED_UNICODE);
    js_data.ipqualityscore_api_hash = @json($setting['ipqualityscore_api_hash'], JSON_UNESCAPED_UNICODE);
    js_data.recently_bought_names = @json($recentlyBoughtNames, JSON_UNESCAPED_UNICODE);
    js_data.recently_bought_cities = @json($recentlyBoughtCities, JSON_UNESCAPED_UNICODE);
    js_data.i18n.phrases = @json($loadedPhrases, JSON_UNESCAPED_UNICODE);
    js_data.lang_code = @json($langCode, JSON_UNESCAPED_UNICODE);
    js_data.country_code = @json($countryCode, JSON_UNESCAPED_UNICODE);
    js_data.countries = @json($countries, JSON_UNESCAPED_UNICODE);
    js_data.payment_methods = @json($setting['payment_methods'], JSON_UNESCAPED_UNICODE);
    js_data.product = @json($product, JSON_UNESCAPED_UNICODE);
  </script>

@endsection


@section('js_deps')

  <script type="text/javascript">
    js_deps.show([
      'awesome.css',
      'element.css',
      'bootstrap.css',
      'intl_tel_input.css',
      'layout-styles',
      'page-styles',
      'page-styles2',
    ]);
  </script>

@endsection


@section('styles')

  <link
    href="{{ mix_cdn('assets/js/views/promo.vue.css') }}"
    onload="js_deps.ready.call(this, 'page-styles')"
    rel="stylesheet"
    media="none" />

  <link
    href="{{ mix_cdn('assets/css/promo.css') }}"
    onload="js_deps.ready.call(this, 'page-styles2')"
    rel="stylesheet"
    media="none" />

@endsection


@section('scripts')

  <script
    src="{{ mix_cdn('assets/js/views/promo.js') }}"
    defer></script>

@endsection


@section('fixed')
  @if((Route::is('checkout') || Route::is('checkout_price_set')) && (Request::get('show_timer') === '{timer}' || Request::get('show_timer') === '1'))
    <timer-component></timer-component>
  @endif
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
        @include('components.black_friday')
        @include('components.christmas')
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
                                @{{countOfInstallments}} @{{ promoOldPrice }}
                            </span>
                        </div>
                        <span class="promo__price promo__text-red bold">
                            @{{countOfInstallments}} @{{ promoPriceText }}
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
                <installments
                  popperClass="emc1-popover-variant"
                  :extra-fields="extraFields"
                  :form="form">
                </installments>
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
                            'selected-promotion': selectedPlan === item.totalQuantity,
                            'most-popular': item.isBestseller,
                            'most-profitable': item.isPopular,
                            'starter': item.discountName === '',
                        }"
                        @click="setSelectedPlan(item.totalQuantity)"
                    >
                        <div
                            class="promo__heading"
                            :class="{
                                'most-popular': item.isBestseller,
                                'most-profitable': item.isPopular,
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
                                :alt="item.textComposite"
                                class="lazy promo__discount-image"
                                :data-src="productImages[item.totalQuantity] || item.image"
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
                        <green-button :class="['promo__add-button', {'disabled': selectedPlan === item.totalQuantity}]">
                            <span v-if="selectedPlan === item.totalQuantity">{{ t('checkout.selected') }}</span>
                            <span v-else>{{ t('checkout.add_to_cart') }}</span>
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
                        <img class="lazy promo__carousel-img" data-src="https://static-backend.saratrkr.com/image_assets/technatic_1.png" alt="">
                        <img class="lazy promo__carousel-img" data-src="https://static-backend.saratrkr.com/image_assets/Best_Product.png" alt="">
                        <img class="lazy promo__carousel-img" data-src="https://static-backend.saratrkr.com/image_assets/Gadgetify_1.png" alt="">
                        <img class="lazy promo__carousel-img" data-src="https://static-backend.saratrkr.com/image_assets/NewYourToday_3.png" alt="">
                    </carousel>
                </section>

                <section class="promo__reviews">
                    <div class="container">
                        <h2 class="promo__title">
                            {{ t('checkout.happy_users', ['product' => $product->product_name]) }}
                        </h2>
                        @foreach ($product->reviews as $review)
                            <div class="promo__review">
                                <div class="col-md-3 col-sm-3 col-xs-12 review-head">
                                    <div class="promo__review-image">
                                        <div class="wrapper">
                                            <img class="lazy"
                                                 data-src="{{ $review['image'] ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="section-text stars">
                                        <i class="fa fa-star"
                                           v-for="rateStar in {{ $review['rate'] ?? 5 }}"></i>
                                    </div>
                                    <div class="section-text name">{{ $review['name'] ?? '' }}</div>
                                </div>
                                <div class="col-md-9 col-sm-9 col-xs-12 review-text">{{ $review['text'] ?? '' }}</div>
                            </div>
                        @endforeach
                    </div>
                </section>

            </template>
        </div>
        <div
            v-if="isShowVariant"
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
        <template v-if="selectedPlan && isShowVariant">
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
                                            class="lazy promo__variant-icon"
                                            :data-src="variantItem.imageUrl"
                                            :alt="variantItem.label" />
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
                        v-if="$root.paypalEnabled"
                        v-show="form.installments === 1"
                        :style="{ 'max-width': '400px' }"
                        :create-order="paypalCreateOrder"
                        :on-approve="paypalOnApprove"
                        :$vvariant="$v.form.variant"
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
                        @input="activateForm">
                    </payment-provider-radio-list>
                </div>
                <template v-if="$root.hasAPM">
                    <div class="promo__alternative-payment">
                        {{ t('checkout.pay_securely_apm') }}
                    </div>
                    <div class="promo__row-payments">
                        <payment-providers-apm
                            class="promo__credit-card-switcher"
                            v-model="form.paymentProvider"
                            @input="activateForm" />
                    </div>
                </template>
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
                        :country-list="countriesList"
                        :quantity-of-installments="countOfInstallments"
                        :warranty-price-text="warrantyPriceText"
                        :is-hygiene="isHygiene"
                        :extra-fields="extraFields"
                        :state-extra-field="stateExtraField"
                        :payment-method-u-r-l="paymentMethodURL"
                        @set-payment-method-by-cardnumber="setPaymentMethodByCardNumber">
                    </payment-form>
                </div>
            </div>
        </section>
        <section v-if="isShownFooter" class="promo__scroll-to-top">
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
        v-if="+queryParams.exit !== 0"
        :show-preloader="showPreloader">
    </leave-modal>
</div>

@include('layouts.footer')

@endsection
