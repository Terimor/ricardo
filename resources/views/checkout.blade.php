@extends('layouts.app')

<script type="text/javascript">
    const bluesnapCredential = {
      'Authorization': 'Basic {{base64_encode(env('BLUESNAP_API_KEY').':'.env('BLUESNAP_API_PASS'))}}',
    }

    try {
      EBANX.config.setMode('{{env('EBANX_MODE')}}');
      EBANX.config.setPublishableKey('{{env('EBANX_SANDBOX_PUBLIC_INTEGRATION_KEY')}}');
      EBANX.config.setCountry('br');
    } catch (e) {
      console.error('Error with initialize EBANX')
      console.error(e)
    }

    const checkoutData = {
      countryCode: '{{ $location->countryCode }}',
      product: @json($product),
      productImage: '{{$product->logo_image}}',
      upsell_hero_image: '{{$product->upsell_hero_image}}',
    }
</script>
@section('content')

@includeWhen($isShowProductOffer, 'components.product_offer')

<app-component></app-component>

@endsection
