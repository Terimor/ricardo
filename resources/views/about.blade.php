@extends('layouts.app')

@section('title', $product->skus[0]['name'] . ' - ' . 'Who we are')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/static.css') }}">
@endsection


@section('content')
<div class="static">
    <div class="container">
        <div class="static__wrapper">
            <h1>
                <strong>Who we are</strong>
            </h1>
            <br>
            <br>
            @include('layouts.footer', ['isWhite' => true, 'hasHome' => true ])
        </div>
    </div>
</div>
@endsection