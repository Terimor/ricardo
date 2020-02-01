@extends('new.pages.vrtl.checkout')


@section('fonts_checkout')
  @include('components.fonts.lato')
  @include('new.fonts.oswald')
@endsection


@section('styles_checkout')
  <link
    href="{{ mix_cdn('assets/css/new/pages/vrtl/checkout/templates/vc1.css') }}"
    onload="js_deps.ready.call(this, 'page-styles')"
    rel="stylesheet"
    media="none" />
@endsection


@section('scripts_checkout')
  <script
    src="{{ mix_cdn('assets/js/new/pages/vrtl/checkout/templates/vc1.js') }}"
    onload="js_deps.ready('page-scripts')"
    async></script>
@endsection


@section('content_checkout')
  <div class="vc1">
    @include('new.pages.vrtl.checkout.templates.vc1.header')
    <div class="content">
      <div class="left-column">
        @include('new.pages.vrtl.checkout.templates.vc1.steps-line')
        @include('new.pages.vrtl.checkout.templates.vc1.yes-deliver')
        @include('new.pages.vrtl.checkout.templates.vc1.product')
        @include('new.pages.vrtl.checkout.templates.vc1.guarantee')
        @include('new.pages.vrtl.checkout.templates.vc1.upsells')
      </div>
      <div class="right-column">
        @include('new.pages.vrtl.checkout.templates.vc1.providers')
        @include('new.pages.vrtl.checkout.templates.vc1.shipping')
        @include('new.pages.vrtl.checkout.templates.vc1.summary')
      </div>
    </div>
    @include('new.pages.vrtl.checkout.templates.vc1.upsells')
  </div>
@endsection
