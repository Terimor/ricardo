@extends('layouts.app', ['product' => $product])


@section('script')
<script type="text/javascript">
    const checkoutData = {
      product: @json($product),
    }
</script>
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
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
                        Get it now
                    </a>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <img class="index__image" src="{{ $product->image[1] }}" alt="">
            </div>
        </div>
        <div class="row justify-content-center align-items-center index__row">
            <div class="col-12 col-md-6">
                <img class="index__image" src="{{ $product->image[1] }}" alt="">
            </div>
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
</div>
@endsection
