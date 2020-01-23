@extends('minishop.layout')


@section('title', $page_title . ' - ' . $product->long_name)


@section('js_data')
  <script type="text/javascript">
    js_data.product = @json($product);
  </script>
@endsection


@section('js_deps')

  <script type="text/javascript">
    js_deps.show([
      'lato.css',
      'awesome.css',
      'bootstrap.css',
      'page-styles',
    ]);
  </script>

@endsection


@section('styles')

  <link
    href="{{ mix_cdn('assets/css/minishop/product.css') }}"
    onload="js_deps.ready.call(this, 'page-styles')"
    rel="stylesheet"
    media="none" />

@endsection


@section('scripts')

  <script
    src="{{ mix_cdn('assets/js/minishop/product.js') }}"
    onload="js_deps.ready('page-scripts')"
    async></script>

@endsection


@section('content')

  <section class="content">
    <div class="container">

      @include('minishop.pages.product.title')

      <div class="row">

        <div class="left-column col-12 col-sm-12 col-md-6 col-lg-6 mt-5 mt-0-m">
          <div class="wrapper">
            @include('minishop.pages.product.image')
            @include('minishop.pages.product.slider')
          </div>
        </div>

        <div class="right-column col-12 col-sm-12 col-md-6 col-lg-6 mt-5 mt-25-m">
          @include('minishop.pages.product.quantity')
          @include('minishop.pages.product.price_cart')
          @include('minishop.pages.product.description')
        </div>

      </div>

      @include('minishop.pages.product.return')

    </div>
  </section>

@endsection
