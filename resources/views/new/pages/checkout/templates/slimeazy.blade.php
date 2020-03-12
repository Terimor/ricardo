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
    @include('new.pages.checkout.templates.slimeazy.step1')
    @include('new.pages.checkout.templates.slimeazy.step2')
  </div>
@endsection
