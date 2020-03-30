@extends('new.pages.checkout')


@section('fonts_checkout')
  @include('new.fonts.roboto')
@endsection


@section('styles_checkout')
  <link
    href="{{ mix_cdn('assets/css/new/pages/checkout/templates/amc81.css') }}"
    onload="js_deps.ready.call(this, 'page-styles')"
    rel="stylesheet"
    media="none" />
@endsection


@section('scripts_checkout')
  <script
    src="{{ mix_cdn('assets/js/new/pages/checkout/templates/amc81.js') }}"
    onload="js_deps.ready('page-scripts')"
    async></script>
@endsection


@section('header_checkout')
  @if (!empty($product->home_name))
    <div class="home-name">{{ $product->home_name }}</div>
  @endif
@endsection


@section('content_checkout')
  <div class="amc81">
    AMC81
  </div>
@endsection
