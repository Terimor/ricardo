@extends('new.pages.checkout')


@section('fonts_checkout')
  @include('new.fonts.roboto')
  @include('new.fonts.oswald')
@endsection


@section('styles_checkout')
  <link
    href="{{ mix_cdn('assets/css/new/pages/checkout/templates/thor-power.css') }}"
    onload="js_deps.ready.call(this, 'page-styles')"
    rel="stylesheet"
    media="none" />
@endsection


@section('scripts_checkout')
  <script
    src="{{ mix_cdn('assets/js/new/pages/checkout/templates/thor-power.js') }}"
    onload="js_deps.ready('page-scripts')"
    async></script>
@endsection


@section('content_checkout')
  <div class="thor-power">
    thor-power
  </div>
@endsection
