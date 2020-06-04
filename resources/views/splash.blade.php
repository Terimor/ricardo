@extends('layouts.app', ['product' => $product])

@section('title', $page_title)


@section('js_data')

  <script type="text/javascript">
    js_data.product = @json($product, JSON_UNESCAPED_UNICODE);
  </script>

@endsection


@section('js_deps')

  <script type="text/javascript">
    js_deps.show([
      'bootstrap.css',
      'layout-styles',
      'page-styles',
    ]);
  </script>

@endsection


@section('styles')

  <link
    href="{{ mix_cdn('assets/css/splash.css') }}"
    onload="js_deps.ready.call(this, 'page-styles')"
    rel="stylesheet"
    media="none" />

@endsection


@section('content')
    <div class="splash">
        <div class="bunner">
            <div class="container">
                <div class="bunner-inner">
                    <div class="bunner-left">
                        <div class="bunner-img for-img">
                            @php $image = Request::get('image') ? (int)Request::get('image') - 1 : 0; @endphp
                            <img src="{{ $product->image[$image] ?? $product->image[0] }}" alt="">
                        </div>
                    </div>
                    <div class="bunner-right">
                        <div class="bunner-descr">
                            <div class="bunner-logo for-img">
                                <img src="{{ $product->logo_image }}" alt="{{ $product->product_name }}">
                            </div>
                            <ul class="bunner-text">
                                {!! t('splash_page.bunner_text', ['amount' => $product->prices['1']['discount_percent'] . '%']) !!}
                            </ul>
                            <div class="mobile-bunner-img for-img mt-3">
                                <img src="{{ $product->image[0] }}" alt="">
                            </div>
                            <a href="/checkout" class="red-btn">
                                <span>
                                    {!! t('splash_page.red_button_top', ['product' => $product->product_name, 'amount' => $product->prices['1']['discount_percent']. '%']) !!} >>
                                </span>
                            </a>
                            <ul class="bunner-list">
                                {!! t('splash_page.bunner_list', ['count' =>$product->prices['1']['value_text'], 'amount' => $product->prices['1']['old_value_text']]) !!}
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
                        <h2 class="testimonials-title">{{ t('splash.testimonials.title') }}</h2>
                        <ul class="testimonials-list">
                            @foreach ($product->reviews as $review)
                                <li class="testimonial">
                                    <div class="testimonial-left">
                                        <div class="testimonial-avatar">
                                            <div class="wrapper">
                                                <img src="{{ $review['image'] ?? '' }}" alt="avatar icon">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="testimonial-right">
                                        <div class="testimonial-header">
                                            <span class="testimonial-author">{{ $review['name'] ?? '' }}</span>
                                            <span class="testimonial-date">{{ $review['date'] ?? '' }}</span>
                                            <span class="testimonial-rate for-img">
                                                @for ($i = 0; $i < ($review['rate'] ?? 5); $i++)
                                                    <i class="fa fa-star"></i>
                                                @endfor
                                            </span>
                                        </div>
                                        <div class="testimonial-body">
                                            <p>{{ $review['text'] ?? '' }}</p>
                                            <div class="testimonial-images">
                                                <div class="testimonial-img"><img src="" alt=""></div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="splash-description">
                        <h3>{{ $product->splash_description }}</h3>
                        @if (!empty($product->image[2]))
                            <div class="for-img">
                                <img src="{{ $product->image[2] }}" alt="{{ $product->product_name }}">
                            </div>
                        @endif
                    </div>

                </div>
                <div class="col-md-6 mt-md-5">
                    <div class="product-media">
                        <div class="for-img">
                            <img src="https://enence.com/theme/images/lang/en/safe.png" alt="">
                        </div>
                        @if (!empty($product->vimeo_id))
                        <h3>
                            {!! t('splash_page.video_action', ['product' => $product->long_name]) !!}
                            <div class="iframe-wrap">
                                <iframe src="https://player.vimeo.com/video/{{ $product->vimeo_id }}"
                                        frameborder="0"
                                        allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture"
                                        allowfullscreen=""></iframe>
                                <div class="iframe-stretch"></div>
                            </div>
                        @endif
                        <div class="text-center">
                            <a href="/checkout" class="green-btn">
                                {!! t('splash_page.green_button', ['amount' => $product->prices['1']['discount_percent'] . '%']) !!}
                            </a>
                        </div>
                        <h3>{!! $product->description !!}</h3>
                        @if (!empty($product->image[1]))
                            <div class="for-img">
                                <img src="{{ $product->image[1] }}" alt="{{ $product->product_name }}">
                            </div>
                        @endif

                        <div class="text-center">
                            <a href="/checkout" class="btn-iconed green-btn">
                                <div class="wrapper">
                                    <img src="https://enence.com/theme/instant-translator/landing3/ex4.png" />
                                    <span>
                                        {!! t('splash_page.green_button', ['amount' => $product->prices['1']['discount_percent'] . '%']) !!} >>
                                    </span>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <footer class="splash-footer">
            <div class="splash-footer-title">
                <div class="container">
                    {!! t('splash_page.footer_text', ['amount' => $product->prices['1']['discount_percent'] . '%', 'product' => $product->product_name]) !!}
                </div>
            </div>
            <div class="splash-footer-container">
                <a class="red-btn" href="#">
                    {!! t('splash_page.red_button_bottom', ['amount' => $product->prices['1']['discount_percent'] . '%']) !!}
                </a>
            </div>
            <div class="splash-footer-subtitle">
                <div class="container">
                    {!! t('splash_page.footer_text2', ['product' => $product->product_name]) !!}
                </div>
            </div>
            <div class="splash-footer-copy">
                &copy; 2019 {{ t('splash_page.all_rights_reserved') }}.
                @include('layouts.footer', ['isWhite' => true])
            </div>
        </footer>
    </div>
@endsection
