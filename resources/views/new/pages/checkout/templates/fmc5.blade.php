@extends('new.pages.checkout')


@section('fonts_checkout')
  @include('components.fonts.lato')
@endsection


@section('styles_checkout')
  <link
    href="{{ mix_cdn('assets/css/new/pages/checkout/templates/fmc5.css') }}"
    onload="js_deps.ready.call(this, 'page-styles')"
    rel="stylesheet"
    media="none" />
@endsection


@section('scripts_checkout')
  <script
    src="{{ mix_cdn('assets/js/new/pages/checkout/templates/fmc5.js') }}"
    onload="js_deps.ready('page-scripts')"
    async></script>
@endsection


@section('content_checkout')
  <div class="fmc5">
    <div class="gray-back"></div>

    <div class="inside">
      @include('new.pages.checkout.templates.fmc5.steps-line')
      @include('new.pages.checkout.templates.fmc5.content')
      @include('new.pages.checkout.templates.fmc5.reviews')
      @include('new.pages.checkout.templates.fmc5.bottom')
    </div>
  </div>
@endsection
