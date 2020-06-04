@extends('new.pages.checkout')


@section('styles_checkout')
  <link
    href="{{ mix_cdn('assets/css/new/pages/checkout/templates/vc2.css') }}"
    onload="js_deps.ready.call(this, 'page-styles')"
    rel="stylesheet"
    media="none" />
@endsection


@section('scripts_checkout')
  <script
    src="{{ mix_cdn('assets/js/new/pages/checkout/templates/vc2.js') }}"
    onload="js_deps.ready('page-scripts')"
    async></script>
@endsection


@section('content_checkout')
  <div class="vc2" v-cloak>
    @include('new.pages.checkout.templates.vc2.htitle')
    <div class="content">
      @include('new.pages.checkout.templates.vc2.left_column')
      @include('new.pages.checkout.templates.vc2.right_column')
    </div>
    @include('new.pages.checkout.templates.vc2.guarantee')
    @include('new.pages.checkout.blocks.safe_invoice')
  </div>
@endsection
