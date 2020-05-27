@extends('layouts.app', ['product' => $product])

@section('title', $page_title)


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
            {{ t('splash.vrtl.top_notification_text') }}
        </div>

        <div class="splash-virtual-page-main">
            <div class="splash-virtual-wait">{{ t('splash.vrtl.wait') }}!!!</div>

            <div class="text-center">
                <div class="splash-virtual-save">
                    @php echo t('splash.vrtl.save_now', ['amount' => '<span id="spalshVirtualPrice">$'.$product->prices['25p']['value'].'</span>']) @endphp
                </div>
            </div>

            <div class="splash-virtual-add-msg">
                @php echo t('splash.vrtl.get_entire_product_txt', ['product' => '<span id="splashVirtualProductName">"'.$product['product_name'].'"</span>', 'amount' => '<span class="underlined">$'.$product->prices[1]['value'].'</span>']) @endphp
            </div>

            <div class="text-center">
                <img src="{{ !empty($product->image) && is_array($product->image) ? $product->image[0] : ''}}" alt="" class="splash-virtual-product-img">

                <div class="splash-virtual-discount-text">{{ t('splash.vrtl.discount_fornext') }}</div>

                <div class="splash-virtual-timer">7:36</div>

                <div class="splash-virtual-discount">{{ t('splash.vrtl.discount_price', ['amount' => '$'.$product->prices[1]['value']]) }}</div>

                <a href="/checkout" class="splash-virtual-discount-btn">{{ t('splash.vrtl.claim_discount') }}</a>

                <div class="splash-virtual-discount-link">{{ t('splash.vrtl.claim_discount_now', ['amount' => '$'.$product->prices['25p']['value']]) }}</div>

                <div class="splash-virtual-discount-secure-text">{{ t('splash.vrtl.discount_secure_txt') }}</div>
            </div>

            <hr class="my-4">

            <div class="splash-virtual-guarantee-block">
                <div class="guarantee-img-wrap">
                    <img src="{{ $cdn_url }}/assets/images/splash/guarantee.png" alt="" class="guarantee-img">
                </div>

                <div class="guarantee-text">
                    <p>{{ t('splash.vrtl.guarantee_txt1') }}</p>
                    <p>{{ t('splash.vrtl.guarantee_txt2') }}</p>
                    <p>{{ t('splash.vrtl.guarantee_txt3') }}</p>
                    <p>{{ t('splash.vrtl.guarantee_txt4') }}</p>
                </div>
            </div>

            <div class="splash-virtual-what-getting-block">
                <h4 class="getting-block-title">{{ t('splash.vrtl.what_getting_title') }}:</h4>

                <div class="text-center">
                    <img class="img-fluid" src="{{ !empty($product->image) && is_array($product->image) ? $product->image[0] : '' }}" alt="">
                </div>

                <div class="px-5">
                    <p class="mt-5">@php echo $product->description; @endphp</p>

                    <p class="mt-4"><img src="{{ $cdn_url }}/assets/images/splash/check-icon.png" alt=""> <b>{{ t('splash.vrtl.product_descr1') }}</b></p>
                    
                    @if($product->splash_description)
                    <p class="mt-5">{{ $product->splash_description }}</p>
                    @endif

                    <p class="mt-4"><img src="{{ $cdn_url }}/assets/images/splash/check-icon.png" alt="">{{ t('splash.vrtl.product_descr2') }}</p>

                    <p class="mt-4">{{ t('splash.vrtl.product_descr_discount') }}</p>

                    <p class="mt-4">{{ t('splash.vrtl.money_back_txt') }}</p>
                </div>

                <div class="text-center">
                    <div class="splash-virtual-discount-text mt-4">{{ t('splash.vrtl.discount_fornext') }}</div>

                    <div class="splash-virtual-timer">7:36</div>

                    <div class="splash-virtual-discount">{{ t('splash.vrtl.discount_price', ['amount' => '$'.$product->prices[1]['value']]) }}</div>

                    <a href="/checkout" class="splash-virtual-discount-btn">{{ t('splash.vrtl.claim_discount') }}</a>

                    <div class="splash-virtual-discount-secure-text"><div class="italic">{{ t('splash.vrtl.launch_offer_txt') }}</div></div>
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

        <div class="splash-virtual-page-footer">
            <img src="{{ $cdn_url }}/assets/images/splash/clickbank.png" alt="" id="splashVirtualFooterClickbank">

            <div class="splash-virtual-page-footer-content">
                <div class="splash-virtual-secure-imgs">
                    <div class="secure-img-wrap"><img src="{{ $cdn_url }}/assets/images/splash/ssl-checkout.png" alt=""></div>
                    <div class="secure-img-wrap"><img src="{{ $cdn_url }}/assets/images/splash/dmca-protected.png" alt=""></div>
                    <div class="secure-img-wrap"><img src="{{ $cdn_url }}/assets/images/splash/ssl-security.png" alt=""></div>
                </div>

                <div class="splash-virtual-footer-text">
                    <p>{{ t('splash.vrtl.clickbank_descr') }}</p>

                    <p>{{ t('splash.vrtl.testimonials_notification') }}</p>
                </div>

                <div class="splash-virtual-footer-copyright">{{ $product->product_name }}</div>
            </div>
        </div>
    </div>
@endsection
