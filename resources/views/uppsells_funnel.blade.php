@extends('layouts.app')

@section('script')
<script>
    const upsellsData = {
      product: @json($product),
      orderCustomer: @json($orderCustomer),
      countryCode: '{{ $countryCode }}'
    }

    window.loadedPhrases = @json($loadedPhrases);
</script>

<script src="{{ asset('js/app.js') }}" defer></script>
@endsection

@section('content')
    <link rel="stylesheet" href="{{ asset('css/uppsells.css') }}">

    <div id="app" class="container upsells">
        <upsells-component></upsells-component>
    </div>
@endsection
