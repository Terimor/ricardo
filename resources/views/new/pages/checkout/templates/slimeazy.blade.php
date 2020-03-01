@extends('new.pages.checkout')


@section('fonts_checkout')
  @include('new.fonts.roboto')
  @include('new.fonts.oswald')
@endsection


@section('styles_checkout')
  <link
    href="{{ mix_cdn('assets/css/new/pages/checkout/templates/slimeazy.css') }}"
    onload="js_deps.ready.call(this, 'page-styles')"
    rel="stylesheet"
    media="none" />
@endsection


@section('scripts_checkout')
  <script
    src="{{ mix_cdn('assets/js/new/pages/checkout/templates/slimeazy.js') }}"
    onload="js_deps.ready('page-scripts')"
    async></script>
@endsection


@section('header_checkout')
  <div class="header-images">
    <img class="lazy" data-src="{{ $cdn_url }}/assets/images/checkout/slimeazy/header-img1.png" />
    <img class="lazy" data-src="{{ $cdn_url }}/assets/images/checkout/slimeazy/header-img2.png" />
    <img class="lazy" data-src="{{ $cdn_url }}/assets/images/checkout/slimeazy/header-img3.png" />
  </div>
@endsection


@section('content_checkout')
  <div class="slimeazy">
    <div class="section1 section-back">
      <div class="container">
        
      </div>
    </div>
    <div class="section2 section-cover" style="background: url({{ $cdn_url }}/assets/images/checkout/slimeazy/about-bg.jpg) top center no-repeat">
      <div class="container">
        
      </div>
    </div>
    <div class="section3 section-back">
      <div class="container">
        
      </div>
    </div>
    <div class="section4 section-cover" style="background: url({{ $cdn_url }}/assets/images/checkout/slimeazy/about-bg.jpg) top center no-repeat">
      <div class="container">
        <div class="title">
          <div class="text">What Are People Saying About {{ $product->product_name }}</div>
          <div class="underline"></div>
        </div>
        <div class="slider">
          <img
            class="left lazy"
            data-src="{{ $cdn_url }}/assets/images/checkout/slimeazy/left-arrow.png"
            @click="section4_slider_left" />
          <img
            class="right lazy"
            data-src="{{ $cdn_url }}/assets/images/checkout/slimeazy/right-arrow.png"
            @click="section4_slider_right" />
          <div class="wrapper">
            <div class="inside" :style="section4_slider_style">
              @for ($i = 0; $i < 6; $i++)
              <div class="slide" :style="section4_slider_slide_style">
                <div class="image"><img class="lazy" data-src="{{ $cdn_url }}/assets/images/checkout/slimeazy/slider-img1.png" /></div>
                <div class="text">I thank Libido Daily Plus for letting me know what sex is. I lost couple of girls in my life as I could not satisfy them in my life. The thing has changes right now. My girlfriend visits me every single day so that we can have sex.</div>
                <div class="name">Roger</div>
              </div>
              @endfor
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="section5 section-back">
      <div class="container">
        <div class="row1">
          <a href="/checkout" class="header-logo-link">
            <img data-src="{{ $main_logo ?? $product->logo_image }}" class="header-logo lazy" alt="" />
          </a>
          <div class="header-images">
            <img class="lazy" data-src="{{ $cdn_url }}/assets/images/checkout/slimeazy/header-img1.png" />
            <img class="lazy" data-src="{{ $cdn_url }}/assets/images/checkout/slimeazy/header-img2.png" />
            <img class="lazy" data-src="{{ $cdn_url }}/assets/images/checkout/slimeazy/header-img3.png" />
          </div>
        </div>
        <div class="row2">
          <div class="left">
            <div class="subrow">
              <div class="subleft">
                <img class="image lazy" data-src="{{ $cdn_url }}/assets/images/checkout/slimeazy/fottr-mobile-img1.png" />
              </div>
              <div class="subright">
                <div class="line1">Lose Weight</div>
                <div class="line2">& increase energy</div>
                <div class="options">
                  <div class="option">
                    <img class="lazy" data-src="{{ $cdn_url }}/assets/images/checkout/slimeazy/teck-white.png" />
                    Helps You Lose Weight
                  </div>
                  <div class="option">
                    <img class="lazy" data-src="{{ $cdn_url }}/assets/images/checkout/slimeazy/teck-white.png" />
                    Helps Increase Your Metabolism
                  </div>
                  <div class="option">
                    <img class="lazy" data-src="{{ $cdn_url }}/assets/images/checkout/slimeazy/teck-white.png" />
                    Helps Increase Fat Oxidation
                  </div>
                  <div class="option">
                    <img class="lazy" data-src="{{ $cdn_url }}/assets/images/checkout/slimeazy/teck-white.png" />
                    Helps Increase Energy
                  </div>
                </div>
              </div>
            </div>
            <div class="shipping">
              <img class="image lazy" data-src="{{ $cdn_url }}/assets/images/checkout/slimeazy/mobil-heding-img3.png" />
              <div class="label">free shipping Today!</div>
            </div>
          </div>
          <div class="right">
            <img class="image lazy" data-src="{{ $cdn_url }}/assets/images/checkout/slimeazy/foot-leftimg.png" />
            <div class="button">rush my order</div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
