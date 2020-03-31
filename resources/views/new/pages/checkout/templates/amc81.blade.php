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


@section('header_before')
  @include('new.pages.checkout.templates.amc81.header_before')
@endsection


@section('content_checkout')
  <div class="amc81">
    @include('new.pages.checkout.templates.amc81.header_after')
  </div>
@endsection
