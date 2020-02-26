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
    <img src="{{ $cdn_url }}/assets/images/checkout/slimeazy/header-img1.png" />
    <img src="{{ $cdn_url }}/assets/images/checkout/slimeazy/header-img2.png" />
    <img src="{{ $cdn_url }}/assets/images/checkout/slimeazy/header-img3.png" />
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
        
      </div>
    </div>
    <div class="section5 section-back">
      <div class="container">
        <div class="row1">
          <a href="/checkout" class="header-logo-link">
            <img src="{{ $main_logo ?? $product->logo_image }}" class="header-logo" alt="" />
          </a>
          <div class="header-images">
            <img src="{{ $cdn_url }}/assets/images/checkout/slimeazy/header-img1.png" />
            <img src="{{ $cdn_url }}/assets/images/checkout/slimeazy/header-img2.png" />
            <img src="{{ $cdn_url }}/assets/images/checkout/slimeazy/header-img3.png" />
          </div>
        </div>
        <div class="row2">
          <div class="left">
            <div class="subrow">
              <div class="subleft">
                <img class="image" src="{{ $cdn_url }}/assets/images/checkout/slimeazy/fottr-mobile-img1.png" />
              </div>
              <div class="subright">
                <div class="line1">Lose Weight</div>
                <div class="line2">& increase energy</div>
                <div class="options">
                  <div class="option">
                    <img src="{{ $cdn_url }}/assets/images/checkout/slimeazy/teck-white.png" />
                    Helps You Lose Weight
                  </div>
                  <div class="option">
                    <img src="{{ $cdn_url }}/assets/images/checkout/slimeazy/teck-white.png" />
                    Helps Increase Your Metabolism
                  </div>
                  <div class="option">
                    <img src="{{ $cdn_url }}/assets/images/checkout/slimeazy/teck-white.png" />
                    Helps Increase Fat Oxidation
                  </div>
                  <div class="option">
                    <img src="{{ $cdn_url }}/assets/images/checkout/slimeazy/teck-white.png" />
                    Helps Increase Energy
                  </div>
                </div>
              </div>
            </div>
            <div class="shipping">
              <img class="image" src="{{ $cdn_url }}/assets/images/checkout/slimeazy/mobil-heding-img3.png" />
              <div class="label">free shipping Today!</div>
            </div>
          </div>
          <div class="right">
            <img class="image" src="{{ $cdn_url }}/assets/images/checkout/slimeazy/foot-leftimg.png" />
            <div class="button">rush my order</div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
