@extends('layouts.app', ['product' => $product, 'loadVue' => true])

@section('title', t('splash.page_title', ['product' => $product->product_name]))


@section('js_deps')
    <script type="text/javascript">
        js_deps.show([
            'page-styles'
        ]);
    </script>
@endsection


@section('styles')
    <link
        href="{{ mix_cdn('assets/css/new/pages/vrtl/splash.css') }}"
        onload="js_deps.ready.call(this, 'page-styles')"
        rel="stylesheet"
        media="none" />
@endsection


@section('scripts')
  <script
    src="{{ mix_cdn('assets/js/new/pages/vrtl/splash.js') }}"
    onload="js_deps.ready('page-scripts')"
    async></script>
@endsection


@section('content')
    <div id="splash-vrtl" class="splash-virtual-page">
        <div class="splash-virtual-header-notification">
            <span id="splashVirtualImportant">{{ t('splash.vrtl.top_notification_important') }}:</span>
            @php echo t('splash.vrtl.top_notification_text'); @endphp
        </div>

        <div class="splash-virtual-page-main">
            <div class="splash-virtual-wait">{{ t('splash.vrtl.wait') }}!!!</div>

            <div class="text-center">
                <div class="splash-virtual-save">
                    @php echo t('splash.vrtl.save_now', ['amount' => '<span id="spalshVirtualPrice">'.$product->prices['25p']['value_text'].'</span>']) @endphp
                </div>
            </div>

            <div class="splash-virtual-add-msg">
                @php echo t('splash.vrtl.get_entire_product_txt', ['product' => '<span id="splashVirtualProductName">"'.$product['product_name'].'"</span>', 'amount' => '<span class="underlined">'.$product->prices[1]['value_text'].'</span>']) @endphp
            </div>

            <div class="text-center">
                <img src="{{ !empty($product->image) && is_array($product->image) ? $product->image[0] : ''}}" alt="" class="splash-virtual-product-img">

                <div class="splash-virtual-discount-text">{{ t('splash.vrtl.discount_fornext') }}</div>

                <div class="splash-virtual-timer" v-html="countdownValue"></div>

                <div class="splash-virtual-discount">{{ t('splash.vrtl.discount_price', ['amount' => $product->prices[1]['value_text']]) }}</div>

                <a href="/checkout" class="splash-virtual-discount-btn">{{ t('splash.vrtl.claim_discount') }}</a>

                <a href="/checkout" class="splash-virtual-discount-link">{{ t('splash.vrtl.claim_discount_now', ['amount' => $product->prices['25p']['value_text']]) }}</a>

                <div class="splash-virtual-discount-secure-text">{{ t('splash.vrtl.discount_secure_txt') }}</div>
            </div>

            <hr class="my-4">

            <div class="splash-virtual-guarantee-block">
                <div class="guarantee-img-wrap">
                    <img src="{{ $cdn_url }}/assets/images/splash/guarantee.png" alt="" class="guarantee-img">
                </div>

                <div class="guarantee-text">
                    @php echo t('splash.vrtl.guarantee_txt'); @endphp
                </div>
            </div>

            <div class="splash-virtual-what-getting-block">
                <h4 class="getting-block-title">{{ t('splash.vrtl.what_getting_title') }}:</h4>

                <div class="text-center">
                    <img class="img-fluid" src="{{ !empty($product->image) && is_array($product->image) ? $product->image[0] : '' }}" alt="">
                </div>

                <div class="px-md-5">
                    <p class="mt-5">@php echo $product->description; @endphp</p>

                    <p class="mt-4"><img src="{{ $cdn_url }}/assets/images/splash/check-icon.png" alt=""> <b>{{ t('splash.vrtl.product_descr1') }}</b></p>

                    @if($product->splash_description)
                        <p class="mt-5">@php echo $product->splash_description @endphp</p>
                    @endif

                    <p class="mt-4"><img src="{{ $cdn_url }}/assets/images/splash/check-icon.png" alt=""><b>{{ t('splash.vrtl.product_descr2') }}</b></p>

                    <p class="mt-4">@php echo t('splash.vrtl.product_descr_discount') @endphp</p>

                    <p class="mt-4">@php echo t('splash.vrtl.money_back_txt') @endphp</p>
                </div>

                <div class="text-center">
                    <div class="splash-virtual-discount-text mt-4">{{ t('splash.vrtl.discount_fornext') }}</div>

                    <div class="splash-virtual-timer" v-html="countdownValue"></div>

                    <div class="splash-virtual-discount">{{ t('splash.vrtl.discount_price', ['amount' => $product->prices[1]['value_text']]) }}</div>

                    <a href="/checkout" class="splash-virtual-discount-btn">{{ t('splash.vrtl.claim_discount') }}</a>
                    
                    <a href="/checkout" class="splash-virtual-discount-link">{{ t('splash.vrtl.get_instant_access') }}</a>

                    <div class="splash-virtual-discount-secure-text italic">{{ t('splash.vrtl.launch_offer_txt') }}</div>
                </div>
            </div>

            <div class="splash-virtual-materials-notification">
                <div class="materials-notification-img-wrap">
                    <img src="{{ $cdn_url }}/assets/images/splash/file-types-icon.png" alt="" class="materials-notification-img">
                </div>

                <div class="splash-virtual-materials-notification-title">{{ t('splash.vrtl.ship_cost_saves_money') }}</div>
                <div class="splash-virtual-materials-notification-descr">{{ t('splash.vrtl.materials_advntg') }}</div>
            </div>
        </div>

        @include('layouts.footer', ['isWhite' => true, 'hasHome' => true ])
    </div>
@endsection
