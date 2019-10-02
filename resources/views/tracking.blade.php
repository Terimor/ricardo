@extends('layouts.app', ['product' => $product])

@section('title')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/tracking.css') }}">
@endsection

@section('script')
    <script>
      const checkoutData = {
        product: @json($product),
      };
      const loadedPhrases = @json($loadedPhrases);
    </script>

    <script src="{{ asset('js/views/tracking.js') }}" defer></script>
@endsection


@section('content')
    <div id="tracking" class="tracking">
        <div class="tracking-header">
            <div class="container">
                <img src="https://static-backend.saratrkr.com/image_assets/EchoBeat-logo.00" alt="">
            </div>
        </div>
        <div class="tracking-body">
            <div class="container">
                <div class="tracking-form-wrap">
                    <form class="tracking-form">
                        <h2 class="tracking-form-title">Поиск отслеживания заказа</h2>
                        <text-field
                                :validation="$v.name"
                                :validation-message="textNameRequired"
                                theme="variant-1"
                                :label="textName"
                                :rest="{
                                  autocomplete: 'given-name'
                                }"
                                v-model="name"/>

                        <hr>
                        <text-field
                                :validation="$v.email"
                                :validation-message="textEmailRequired"
                                theme="variant-1"
                                :label="textEmail"
                                :rest="{
                                  autocomplete: 'email'
                                }"
                                v-model="email"/>


                    </form>
                </div>

            </div>
        </div>
        <div class="tracking-footer">
            <div class="container"></div>
        </div>
    </div>
@endsection
