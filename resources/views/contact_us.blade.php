@extends('layouts.app')

@section('title', $page_title)

@section('styles')
    <link rel="stylesheet" href="{{ mix_cdn('assets/css/contact-us.css') }}" media="none" onload="styleOnLoad.call(this, 'css2-hidden')">
@endsection


@section('content')
<div class="contacts">
    <div class="container">
        <div class="contacts__wrapper">
            {!! t('contacts.content') !!}
            @include('layouts.footer', ['isWhite' => true, 'hasHome' => true ])
        </div>
    </div>
</div>
@endsection
