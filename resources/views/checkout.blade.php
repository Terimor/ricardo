@extends('layouts.app')

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
<script src="{{ asset('js/app.js') }}" defer></script>
@endsection

@section('content')
@section('title', $product->skus[0]['name'] . ' ' . t('checkout.page_title'))

@include('components.product_offer')

<app-component></app-component>

@include('layouts.footer', ['isWhite' => true])

@endsection
