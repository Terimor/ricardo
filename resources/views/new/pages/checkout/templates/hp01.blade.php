@extends('new.pages.checkout')


@section('fonts_checkout')

@endsection


@section('styles_checkout')
  <link
    href="{{ mix_cdn('assets/css/new/pages/checkout/templates/hp01.css') }}"
    onload="js_deps.ready.call(this, 'page-styles')"
    rel="stylesheet"
    media="none" />
@endsection


@section('scripts_checkout')
  <script
    src="{{ mix_cdn('assets/js/new/pages/checkout/templates/hp01.js') }}"
    onload="js_deps.ready('page-scripts')"
    async></script>
@endsection


@section('content_checkout')
  <div class="hp01">
    hp01
  </div>
@endsection
