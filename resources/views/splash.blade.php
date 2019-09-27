@extends('layouts.app', ['product' => $product])

@section('title', $product->page_title)

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/static.css') }}">
@endsection


@section('content')
<div class="static">
    <div class="container">
        <div class="static__wrapper">
           
        </div>
    </div>
</div>
@endsection
