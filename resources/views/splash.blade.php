@extends('layouts.app', ['product' => $product])

@section('title', $product->page_title)

@section('styles')
    <link rel="stylesheet" href="{{ mix_cdn('assets/css/splash.css') }}">
@endsection

@section('script')
    <script defer>
      const checkoutData = {
        product: @json($product)
      };
    </script>
@endsection

@section('content')
    <div class="splash">
        <div class="bunner">
            <div class="container">
                <div class="bunner-inner">
                    <div class="bunner-left">
                        <div class="bunner-img for-img">
                            <img src="{{ $product->image[0] }}" alt="">
                        </div>
                    </div>
                    <div class="bunner-right">
                        <div class="bunner-descr">
                            <div class="bunner-logo for-img">
                                <img src="{{ $product->logo_image }}" alt="{{ $product->product_name }}">
                            </div>
                            <p class="bunner-text_md">{{ t('splash_page.discount') }}</p>
                            <p class="bunner-text_xl">{{ $product->prices['1']['discount_percent'] }}%</p>
                            <p class="bunner-text_lg">{{ t('splash_page.last_chance') }}</p>
                            <a href="/checkout" class="red-btn">
                                <span>
                                    {{ t('splash_page.get') }}
                                    {{ $product->product_name }}
                                    {{ t('splash_page.now') }}
                                    {{ t('splash_page.with') }}
                                    {{ $product->prices['1']['discount_percent'] }}%
                                    {{ t('splash_page.discount') }} >>
                                </span>
                            </a>
                            <ul class="bunner-list">
                                <li class="bunner-item">
                                    {{ t('splash_page.only') }}
                                    {{ $product->prices['1']['value_text'] }}
                                    {{ t('splash_page.vs') }}
                                    {{ $product->prices['1']['old_value_text'] }}
                                    ({{ t('splash_page.retail') }}) -
                                    {{ t('splash_page.available_online-only') }}
                                </li>
                                <li class="bunner-item">
                                    {{ t('splash_page.while_supplies_last') }}
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <div class="testimonials">
                        <h2 class="testimonials-title">What Others are Saying...</h2>
                        <ul class="testimonials-list">
                            <li class="testimonial">
                                <div class="testimonial-left">
                                    <div class="testimonial-avatar for-img">
                                        <img src="https://enence.com/theme/instant-translator/landing3/user.jpg" alt="avatar icon">
                                    </div>
                                </div>
                                <div class="testimonial-right">
                                    <div class="testimonial-header">
                                        <span class="testimonial-author">Claude</span>
                                        <span class="testimonial-date">Sep 27, 2019</span>
                                        <span class="testimonial-rate for-img">
                                            <img src="https://enence.com/theme/instant-translator/landing3/5star.png" alt="rate stars">
                                        </span>
                                    </div>
                                    <div class="testimonial-body">
                                        <p>It was even smaller than I expected. Fits in my hand. The translation is
                                            accurate and convenient! You can also use it to learn foreign language if
                                            you like. Basically I love its the portable design.</p>
                                        <div class="testimonial-images">
                                            <div class="testimonial-img"><img src="" alt=""></div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li class="testimonial">
                                <div class="testimonial-left">
                                    <div class="testimonial-avatar for-img">
                                        <img src="https://enence.com/theme/instant-translator/landing3/user.jpg" alt="avatar icon">
                                    </div>
                                </div>
                                <div class="testimonial-right">
                                    <div class="testimonial-header">
                                        <span class="testimonial-author">Claude</span>
                                        <span class="testimonial-date">Sep 27, 2019</span>
                                        <span class="testimonial-rate for-img">
                                            <img src="https://enence.com/theme/instant-translator/landing3/5star.png" alt="rate stars">
                                        </span>
                                    </div>
                                    <div class="testimonial-body">
                                        <p>It was even smaller than I expected. Fits in my hand. The translation is
                                            accurate and convenient! You can also use it to learn foreign language if
                                            you like. Basically I love its the portable design.</p>
                                        <div class="testimonial-images">
                                            <div class="testimonial-img"><img src="" alt=""></div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li class="testimonial">
                                <div class="testimonial-left">
                                    <div class="testimonial-avatar for-img">
                                        <img src="https://enence.com/theme/instant-translator/landing3/user.jpg" alt="avatar icon">
                                    </div>
                                </div>
                                <div class="testimonial-right">
                                    <div class="testimonial-header">
                                        <span class="testimonial-author">Claude</span>
                                        <span class="testimonial-date">Sep 27, 2019</span>
                                        <span class="testimonial-rate for-img">
                                            <img src="https://enence.com/theme/instant-translator/landing3/5star.png" alt="rate stars">
                                        </span>
                                    </div>
                                    <div class="testimonial-body">
                                        <p>It was even smaller than I expected. Fits in my hand. The translation is
                                            accurate and convenient! You can also use it to learn foreign language if
                                            you like. Basically I love its the portable design.</p>
                                        <div class="testimonial-images">
                                            <div class="testimonial-img"><img src="" alt=""></div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="product-media">
                        <div class="for-img">
                            <img src="https://enence.com/theme/images/lang/en/safe.png" alt="">
                        </div>
                        <h3>
                            {{ t('splash_page.video_of') }}
                            {{ $product->long_name }}
                            {{ t('splash_page.in_action') }}:</h3>
                        <div class="iframe-wrap">
                            <iframe src=""
                                    frameborder="0"
                                    allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen=""></iframe>
                            <div class="iframe-stretch"></div>
                        </div>
                        <div class="text-center">
                            <a href="/checkout" class="green-btn">
                                {{ t('splash_page.add_to_cart') }}
                                {{ t('splash_page.with') }}
                                {{ $product->prices['1']['discount_percent'] }}%
                                {{ t('splash_page.discount') }}
                            </a>
                        </div>
                        <h3>{!! $product->description !!}</h3>
                        @if (!empty($product->image[1]))
                            <div class="for-img">
                                <img src="{{ $product->image[1] }}" alt="{{ $product->product_name }}">
                            </div>
                        @endif

                        <h3>{{ $product->splash_description }}</h3>
                        @if (!empty($product->image[2]))
                            <div class="for-img">
                                <img src="{{ $product->image[2] }}" alt="{{ $product->product_name }}">
                            </div>
                        @endif

                        <div class="text-center">
                            <a href="/checkout" class="btn-iconed green-btn">
                                {{ t('splash_page.add_to_cart') }}
                                {{ t('splash_page.with') }}
                                {{ $product->prices['1']['discount_percent'] }}%
                                {{ t('splash_page.discount') }} >>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <footer class="splash-footer">
            <div class="splash-footer-title">
                <div class="container">{{ t('splash_page.limited_time_promo') }}: {{ $product->prices['1']['discount_percent'] }}% {{ t('splash_page.off') }} {{ $product->product_name }}!</div>
            </div>
            <div class="splash-footer-container">
                <a class="red-btn" href="#">{{ t('splash_page.claim_your') }} {{ $product->prices['1']['discount_percent'] }}% {{ t('splash_page.discount_code_now') }}!</a>
            </div>
            <div class="splash-footer-subtitle">
                <div class="container">{{ t('splash_page.secure_your') }} {{ $product->product_name }} {{ t('splash_page.now_before_this_promotion_ends') }}</div>
            </div>
            <div class="splash-footer-copy">
                &copy; 2019 {{ t('splash_page.all_rights_reserved') }}.
                @include('layouts.footer', ['isWhite' => true])
            </div>
        </footer>
    </div>
@endsection
