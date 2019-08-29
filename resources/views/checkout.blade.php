@extends('layouts.app')

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
@section('content')
@section('title', $product->skus[0]['name'])

@include('components.product_offer')

<app-component></app-component>

@endsection
