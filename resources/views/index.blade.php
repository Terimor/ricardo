@extends('layouts.app', ['product' => $product])

@section('title', $product->page_title)


@section('js_data')

  <script type="text/javascript">
    js_data.product = @json($product);
  </script>

@endsection


@section('js_deps')

  <script type="text/javascript">
    js_deps.show([
      'bootstrap.css',
      'layout-styles',
      'page-styles',
    ]);
  </script>

@endsection


@section('styles')

  <link
    href="{{ mix_cdn('assets/css/index.css') }}"
    onload="js_deps.ready.call(this, 'page-styles')"
    rel="stylesheet"
    media="none" />

@endsection


@section('content')
<div class="container">
    <div class="index">
        <div class="row justify-content-center align-items-center index__row">
            <div class="col-12 col-md-6">
                <div class="d-flex flex-column align-items-md-start align-items-center">
                    <h1 class="index__title">
                        {{ $product->product_name }}
                    </h1>
                    <div class="index__description">
                        {!! $product->description !!}
                    </div>
                    <a
                        class="index__button"
                        href="/checkout"
                    >
                        {{ t('index.get_it_now') }}
                    </a>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <img class="index__image" src="{{ $product->image[0] }}" alt="">
            </div>
        </div>
        <div class="row justify-content-center align-items-center index__row">
            @if(!empty($product->image[1]))
            <div class="col-12 col-md-6">
                <img class="index__image" src="{{ $product->image[1] }}" alt="">
            </div>
            @endif
            <div class="col-12 col-md-6">
                <h2 class="index__title mt-0">
                    {{ $product->home_name }}
                </h2>
                <div class="index__description">
                    {!! $product->home_description !!}
                </div>
            </div>
        </div>
    </div>
    @include('layouts.footer', ['isWhite' => true])
</div>
@endsection
