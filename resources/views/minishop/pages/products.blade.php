@extends('minishop.layout')


@section('title', $website_name)


@section('js_deps')

  <script type="text/javascript">
    js_deps.show(['page-styles']);
  </script>

@endsection


@section('styles')

  <link
    href="{{ mix_cdn('assets/css/minishop/products.css') }}"
    onload="js_deps.ready.call(this, 'page-styles')"
    rel="stylesheet"
    media="none" />

@endsection


@section('scripts')

  <script
    src="{{ mix_cdn('assets/js/minishop/products.js') }}"
    onload="js_deps.ready('page-scripts')"
    async></script>

@endsection


@section('content')

  <section class="content">
    <div class="container">

      <div class="row">
        <div class="col-12 col-sm-12 col-md-12 col-lg-12">
          <h1 class="mb-5 mb-25-m font-weight-bold text-center">
            {{ t('minishop.products.page_title') }}
          </h1>
        </div>
      </div>

      <div class="row">
        @foreach ($products as $product)
          <div class="col-12 col-sm-12 col-md-6 col-lg-3">
            <div
              class="product-box"
              @click.stop="goto_checkout('{{ $product->skus[0]['code'] ?? '' }}')">

              <div class="images-holder d-flex position-relative align-items-center justify-content-center px-4">
                <img
                  class="d-block img-1 mw-100 mh-100"
                  src="{{ $product->image[0] ?? '' }}"
                  alt="" />
              </div>

              <h3 class="text-center font-weight-bold my-3">{{ $product->product_name ?? '' }}</h3>

              <div
                class="add-to-cart">
                <i class="fas fa-cart-plus"></i>{{ t('minishop.products.add_to_cart') }}
              </div>

              <p class="text-center font-weight-bold">
                <span class="old-price">{{ $product->prices[1]['old_value_text'] ?? '' }}</span>{{ $product->prices[1]['value_text'] ?? '' }}
              </p>

            </div>
          </div>
        @endforeach
      </div>

    </div>
  </section>

@endsection
