@extends('layouts.app', ['product' => $product])

@section('title', $product->page_title)

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/splash.css') }}">
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
                            <p class="bunner-text_xl">50%</p>
                            <p class="bunner-text_lg">{{ t('splash_page.last_chance') }}</p>
                            <button type="button" class="green-button-animated">
                                <span>
                                    {{ t('splash_page.get') }}
                                    {{ $product->product_name }}
                                    {{ t('splash_page.now') }}
                                    {{ t('splash_page.with') }}
                                    50%
                                    {{ t('splash_page.discount') }}
                                </span>
                            </button>
                            <ul class="bunner-list">
                                <li class="bunner-item">
                                    {{ t('splash_page.only') }}
                                    $89.00
                                    {{ t('splash_page.vs') }}
                                    $178.00
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
                        <h2 class="testimonials-title">What Others are Saying... {{ $product->logo_image }} </h2>
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
                    <div class="for-img">
                        <img src="https://enence.com/theme/images/lang/en/safe.png" alt="">
                    </div>
                    <h3>
                        {{ t('splash_page.video_of') }}
                        {{ $product->product_name }}
                        {{ t('splash_page.in_action') }}:</h3>
                    <div class="iframe-wrap">
                        <iframe src="https://www.youtube.com/embed/BNhFytVV7SY?rel=0&amp;controls=1&amp;modestbranding=1&amp;showinfo=0"
                                frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen=""></iframe>
                    </div>
                    <div class="text-center">
                        <a href="#" class="green-button-animated">
                            {{ t('splash_page.add_to_cart') }}
                            {{ t('splash_page.with') }}
                            50%
                            {{ t('splash_page.discount') }}
                        </a>
                    </div>
                    <h3>{{ $product->long_name }}</h3>
                    <div class="for-img">
                        <img src="{{ $product->image[1] }}" alt="">
                    </div>
                    <h3>{{ $product->long_name }}</h3>
                    <div class="for-img">
                        <img src="{{ $product->image[2] }}" alt="">
                    </div>
                </div>
            </div>
        </div>
        <div class="container">
           {{--@include('layouts.footer', ['isWhite' => true, 'hasHome' => true ])--}}
        </div>
    </div>
@endsection
