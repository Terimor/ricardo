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
<script type="text/javascript">window.IPQ={Callback:()=>{}}</script><script src="https://www.ipqualityscore.com/api/*/AWUoMbxT7WcrE66bhaGsnqYtXLma2Bn8pSoPUSdbrW5xzMbRd3L82HQu7kQa2xBIKqkY4zCqvOvTvYCChebbZlWzZrpgs95jCYcivz669qLzFEpNlCXNCkB7yiHa1mpc7sb5IpBuTaKg24DbP2HtEVnop71JSXBxFaoxQhRtr5DVly7JRn8ENb9zI7B2XgebH9zadHR0wZlvg5pj7BnT7yfoUZgBFhlGE4kZOdkHzLDExXAHcXc0asI4K670heAb/learn.js"></script><noscript><img src="https://www.ipqualityscore.com/api/*/AWUoMbxT7WcrE66bhaGsnqYtXLma2Bn8pSoPUSdbrW5xzMbRd3L82HQu7kQa2xBIKqkY4zCqvOvTvYCChebbZlWzZrpgs95jCYcivz669qLzFEpNlCXNCkB7yiHa1mpc7sb5IpBuTaKg24DbP2HtEVnop71JSXBxFaoxQhRtr5DVly7JRn8ENb9zI7B2XgebH9zadHR0wZlvg5pj7BnT7yfoUZgBFhlGE4kZOdkHzLDExXAHcXc0asI4K670heAb/pixel.png" /></noscript>
<script src="{{ asset('js/app.js') }}" defer></script>
@endsection

@section('content')

<div id="app">
  @section('title', $product->skus[0]['name'] . ' ' . t('checkout.page_title'))
  @include('components.product_offer')

  <app-component></app-component>
</div>

@include('layouts.footer', ['isWhite' => true])

@endsection
