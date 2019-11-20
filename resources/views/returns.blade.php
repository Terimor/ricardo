@extends('layouts.app', ['product' => $product])

@section('title', $product->page_title . ' - ' . $loadedPhrases['refunds_title'])

@section('styles')
    <link rel="stylesheet" href="{{ mix_cdn('assets/css/static.css') }}" media="none" onload="styleOnLoad.call(this, 'css2-hidden')">
@endsection


@section('content')
<div class="static">
    <div class="container">
        <div class="static__wrapper">
            <div class="returns-address">
              <div class="label">{{ t('returns.address.label') }}</div>
              <select class="selector">
                <option value="" data-value=""></option>
                <option value="value1" data-value="{{ t('returns.address.value1') }}">{{ t('returns.address.option1') }}</option>
                <option value="value2" data-value="{{ t('returns.address.value2') }}">{{ t('returns.address.option2') }}</option>
                <option value="value3" data-value="{{ t('returns.address.value3') }}">{{ t('returns.address.option3') }}</option>
              </select>
              <div class="address"></div>
            </div>
            {!! t('returns.content') !!}
            @include('layouts.footer', ['isWhite' => true, 'hasHome' => true ])
        </div>
    </div>
</div>
@endsection
