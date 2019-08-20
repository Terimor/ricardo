@extends('layouts.app')
<script src="https://cdn.jsdelivr.net/npm/js-cookie@2/src/js.cookie.min.js"></script>
<script type="text/javascript" src="https://js.ebanx.com/ebanx-1.6.0.min.js"></script>
{{--<script src="https://cdn.checkout.com/sandbox/js/checkout.js"></script>--}}
{{--<script src="https://sandbox.bluesnap.com/js/cse/v1.0.4/bluesnap.js"></script>--}}
{{--<script src="https://paypal.com/sdk/js?client-id={{env('PAYPAL_CLIENT_ID','')}}"></script>--}}

<script type="text/javascript">
    const bluesnapCredential = {
      'Authorization': 'Basic {{base64_encode(env('BLUESNAP_API_KEY').':'.env('BLUESNAP_API_PASS'))}}',
    }

    EBANX.config.setMode('{{env('EBANX_MODE')}}');
    EBANX.config.setPublishableKey('{{env('EBANX_SANDBOX_PUBLIC_INTEGRATION_KEY')}}');
    EBANX.config.setCountry('br');

    const checkoutData = {
      countryCode: '{{ $location->countryCode }}',
      product: @json($product),
      productImage: '{{$product->logo_image}}'
    }
</script>
@section('content')
<app-component></app-component>

@endsection
