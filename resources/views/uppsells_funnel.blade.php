@extends('layouts.app', ['product' => $product])

@section('title', $product->page_title . ' - ' . $loadedPhrases['upsells.title'])

@section('head')
    @if (!empty($product->favicon_image))
        <link rel="shortcut icon" href="{{ $product->favicon_image }}">
    @endif
@endsection

@section('styles')
  <link rel="stylesheet" href="{{ asset('css/uppsells.css') }}">
  <link rel="stylesheet" href="{{ asset('css/thank-you.css') }}">
  <link rel="stylesheet" href="{{ asset('css/vue-styles/js/views/thank-you.css') }}">
@endsection

@section('script')
<script>
    var upsellsData = {
      product: @json($product),
      orderCustomer: @json($orderCustomer),
      countryCode: '{{ $countryCode }}'
    }

    var loadedPhrases = @json($loadedPhrases);
</script>

<script src="{{ asset('js/app.js') }}" defer></script>
@endsection

@section('content')
    <div class="container upsells">
        <upsells-component></upsells-component>
        @include('layouts.footer', ['isWhite' => true, 'hasHome' => true ])
    </div>
@endsection
