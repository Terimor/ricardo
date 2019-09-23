@extends('layouts.app', ['product' => $product])

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <a href="/contact-us">Contact us</a>
                <a href="/about">Who we are</a>
                <a href="/order-tracking">Order tracking</a>
                <a href="/checkout">Checkout</a>
                <div class="card-body">

                </div>
            </div>
        </div>
    </div>
</div>
@endsection