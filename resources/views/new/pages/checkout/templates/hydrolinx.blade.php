@extends('new.pages.checkout')


@section('fonts_checkout')
  @include('new.fonts.roboto')
  @include('new.fonts.oswald')
@endsection


@section('styles_checkout')
  <link
    href="{{ mix_cdn('assets/css/new/pages/checkout/templates/hydrolinx.css') }}"
    onload="js_deps.ready.call(this, 'page-styles')"
    rel="stylesheet"
    media="none" />
@endsection


@section('scripts_checkout')
  <script
    src="{{ mix_cdn('assets/js/new/pages/checkout/templates/hydrolinx.js') }}"
    onload="js_deps.ready('page-scripts')"
    async></script>
@endsection


@section('content_checkout')
  <div class="hydrolinx">
    hydrolinx
  </div>
@endsection
