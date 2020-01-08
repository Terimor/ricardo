@extends('pages.checkout')


@section('styles_checkout')
  <link
    href="{{ mix_cdn('assets/css/pages/checkout/vmp44.css') }}"
    onload="js_deps.ready.call(this, 'page-styles')"
    rel="stylesheet"
    media="none" />
@endsection


@section('scripts_checkout')
  <script
    src="{{ mix_cdn('assets/js/pages/checkout/vmp44.js') }}"
    onload="js_deps.ready('page-scripts')"
    async></script>
@endsection


@section('content_checkout')

@endsection
