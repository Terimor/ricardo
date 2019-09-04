@extends('layouts.app')

@section('script')
<script type="text/javascript">
    const bluesnapCredential = {
      'Authorization': 'Basic {{base64_encode(env('BLUESNAP_API_KEY').':'.env('BLUESNAP_API_PASS'))}}',
    }

    const checkoutData = {
      countryCode: '{{ $location->countryCode }}',
      product: @json($product),
      productImage: '{{$product->logo_image}}',
    }
</script>
<script src="{{ asset('js/app.js') }}" defer></script>
@endsection

@section('content')
@section('title', $product->skus[0]['name'] . ' Checkout')

@include('components.product_offer')

<app-component></app-component>

@endsection
