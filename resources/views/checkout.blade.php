@extends('layouts.app', ['product' => $product])

@section('title', $product->page_title . ' ' . t('checkout.page_title'))

@section('styles')
  <link rel="stylesheet" href="{{ mix_cdn('assets/js/app.vue.css') }}">
@endsection

@section('script')
<script type="text/javascript">
    var bluesnapCredential = {
      'Authorization': 'Basic {{base64_encode(env('BLUESNAP_API_KEY').':'.env('BLUESNAP_API_PASS'))}}',
    }

    var recentlyBoughtNames = @json($recentlyBoughtNames);
    var recentlyBoughtCities = @json($recentlyBoughtCities);

    var checkoutData = {
      countryCode: '{{ $countryCode }}',
      countries: @json($countries),
      product: @json($product),
      productImage: '{{$product->logo_image}}',
    }

    var loadedPhrases = @json($loadedPhrases);
</script>

<script src="{{ mix_cdn('assets/js/app.js') }}" defer></script>
@endsection

@section('content')

@section('title', $product->skus[0]['name'] . ' ' . t('checkout.page_title'))

<app-component></app-component>

@include('layouts.footer', ['isWhite' => true])

@endsection
