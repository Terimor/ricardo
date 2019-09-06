@extends('layouts.app')
<script>
    const upsells = {
      countryCode: '{{ $location->countryCode }}'
    }
</script>
<link rel="stylesheet" href="">
@section('content')
    <link rel="stylesheet" href="{{ asset('css/uppsells.css') }}">

    <div class="container upsells">
        <upsells-component></upsells-component>
    </div>

    <script src="{{ asset('js/app.js') }}" defer></script>
@endsection
