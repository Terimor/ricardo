@extends('layouts.app')

@section('title', $product->page_title . ' ' . t('checkout.page_title'))

@section('script')
<script type="text/javascript">
    const bluesnapCredential = {
      'Authorization': 'Basic {{base64_encode(env('BLUESNAP_API_KEY').':'.env('BLUESNAP_API_PASS'))}}',
    }

    const recentlyBoughtNames = @json($recentlyBoughtNames);
    const recentlyBoughtCities = @json($recentlyBoughtCities);

    const checkoutData = {
      countryCode: '{{ $countryCode }}',
      countries: @json($countries),
      product: @json($product),
      productImage: '{{$product->logo_image}}',
    }

    window.loadedPhrases = @json($loadedPhrases);
</script>

<script type="text/javascript">var IPQ = { Callback: () => {} };</script>
<script src="https://www.ipqualityscore.com/api/*/{{ $setting['ipqualityscore_api_hash'] }}/learn.js"></script>
<noscript><img src="https://www.ipqualityscore.com/api/*/{{ $setting['ipqualityscore_api_hash'] }}/pixel.png" /></noscript>

<script src="{{ asset('js/app.js') }}" defer></script>
@endsection

@section('content')

<div id="app">
  @include('components.product_offer')

  <app-component></app-component>
</div>

@include('layouts.footer', ['isWhite' => true])

@endsection
