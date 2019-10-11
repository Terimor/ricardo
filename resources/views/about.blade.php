@extends('layouts.app', ['product' => $product])

@section('title', $product->page_title . ' - ' . $loadedPhrases['about_title'])

@section('styles')
    <link rel="stylesheet" href="{{ mix_cdn('assets/css/static.css') }}">
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
