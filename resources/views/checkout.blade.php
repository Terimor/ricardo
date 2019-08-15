@extends('layouts.app')
<script src="https://cdn.jsdelivr.net/npm/js-cookie@2/src/js.cookie.min.js"></script>
<script src="https://sandbox.bluesnap.com/js/cse/v1.0.4/bluesnap.js"></script>
{{--<script src="https://paypal.com/sdk/js?client-id={{env('PAYPAL_CLIENT_ID','')}}"></script>--}}

<script>
    const bluesnapCredential = {
      'Authorization': 'Basic {{base64_encode(env('BLUESNAP_API_KEY').':'.env('BLUESNAP_API_PASS'))}}',
    }

    const bluesnap = new BlueSnap('{{env('BLUESNAP_CLIENT_SIDE_ENCRYPTION_KEY')}}', true);


    const checkoutData = {
      countryCode: '{{ $location->countryCode }}',
    }
</script>
@section('content')
<app-component></app-component>

@endsection
