@extends('layouts.app', ['product' => $product])

@section('title', $product->page_title . ' - ' . $loadedPhrases['upsells.title'])

@section('head')
    @if (!empty($product->favicon_image))
        <link rel="shortcut icon" href="{{ $product->favicon_image }}">
    @endif
@endsection

@section('script')
<script>
    const upsellsData = {
      product: @json($product),
      orderCustomer: @json($orderCustomer),
      countryCode: '{{ $countryCode }}'
    }

    window.loadedPhrases = @json($loadedPhrases);
</script>

<script src="{{ asset('js/app.js') }}" defer></script>
@endsection

@section('content')
    <link rel="stylesheet" href="{{ asset('css/uppsells.css') }}">

    <div id="app" class="container upsells">
        <upsells-component></upsells-component>
    </div>
@endsection
