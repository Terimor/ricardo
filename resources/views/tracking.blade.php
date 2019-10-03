@extends('layouts.app', ['product' => $product])

@section('title', $product->page_title . ' - ' . $loadedPhrases['tracking.title'])

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
                <img src="{{ $product->logo_image }}" alt="{{ $product->product_name }}">
            </div>
        </div>
        <div class="tracking-body">
            <div class="container">
                <div class="tracking-form-wrap">
                    <form class="tracking-form" @submit.prevent="trackOrder">
                        <h2 class="tracking-form-title">@{{ textTrackingTitle }}</h2>
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
                                class="tracking-btn tracking-btn_sm">@{{ textFormButton }}</button>
                        </div>
                    </form>
                    <div class="tracking-action">
                        <a href="/checkout"
                           class="btn">@{{ textToMenuButton }}</a>
                    </div>
                </div>

            </div>
        </div>
        <div class="tracking-footer">
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
                <iframe class="tracking-iframe"
                        :src="iframeUrl" frameborder="0"></iframe>
            </template>

            <template v-slot:modal-footer>
                <a href="/checkout"
                   class="tracking-btn">@{{ textModalButton }}</a>
            </template>
        </modal>
    </div>
@endsection
