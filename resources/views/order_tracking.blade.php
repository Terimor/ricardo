@extends('layouts.app', ['product' => $product])

@section('title', $product->page_title )

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/order-tracking.css') }}">
@endsection

@section('script')
    <script>
      const checkoutData = {
        product: @json($product),
      };
      const loadedPhrases = @json($loadedPhrases);
    </script>

    <script src="{{ asset('js/views/order-tracking.js') }}" defer></script>
@endsection


@section('content')
    <div id="orderTracking" class="orderTracking">
        <div class="orderTracking-header">
            <div class="container">
                <img src="{{ $product->logo_image }}" alt="{{ $product->product_name }}">
            </div>
        </div>
        <div class="orderTracking-body">
            <div class="container">
                <div class="orderTracking-form-wrap">
                    <form class="orderTracking-form" @submit.prevent="trackOrder">
                        <h2 class="orderTracking-form-title">@{{ textTrackingTitle }}</h2>
                        <div>
                            <text-field
                                    :validation="$v.formData.name"
                                    :validation-message="textNameRequired"
                                    :label="textName"
                                    :rest="{
                                  autocomplete: 'given-name'
                                }"
                                    v-model="formData.name" />
                        </div>
                        <div>
                            <text-field
                                    :validation="$v.formData.email"
                                    :validation-message="textEmailRequired"
                                    :label="textEmail"
                                    :rest="{
                                      autocomplete: 'email'
                                    }"
                                    v-model="formData.email" />
                        </div>
                        <div>
                            <button
                                    :disabled="!isValid"
                                    class="orderTracking-btn orderTracking-btn_sm">@{{ textFormButton }}</button>
                        </div>
                    </form>
                    <div class="orderTracking-action">
                        <a href="/checkout"
                           class="btn">@{{ textToMenuButton }}</a>
                    </div>
                </div>

            </div>
        </div>
        <div class="orderTracking-footer">
            <div class="container">
                @include('layouts.footer', ['isWhite' => true])
            </div>
        </div>


        <modal v-if="showModal"
               :classlist="'modal_xl'"
               @close="showModal = false">
            <template v-slot:modal-header>
                @{{ textModalTitle }}
            </template>

            <template v-slot:modal-body>
                <iframe class="orderTracking-iframe"
                        :src="iframeUrl" frameborder="0"></iframe>
            </template>

            <template v-slot:modal-footer>
                <a href="/checkout"
                   class="orderTracking-btn">@{{ textModalButton }}</a>
            </template>
        </modal>
    </div>
@endsection
