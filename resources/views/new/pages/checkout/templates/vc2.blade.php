@extends('new.pages.checkout')


@section('styles_checkout')
  <link
    href="{{ mix_cdn('assets/css/new/pages/checkout/templates/vc2.css') }}"
    onload="js_deps.ready.call(this, 'page-styles')"
    rel="stylesheet"
    media="none" />
@endsection


@section('scripts_checkout')
  <script
    src="{{ mix_cdn('assets/js/new/pages/checkout/templates/vc2.js') }}"
    onload="js_deps.ready('page-scripts')"
    async></script>
@endsection


@section('content_checkout')
  <div class="vc2" v-cloak>
    @if ($product->bg_image)
      <div class="page-header-image" style="background-image: url('{{ $product->bg_image ?? ''  }}')"></div>
    @endif

    @include('new.pages.checkout.templates.vc2.htitle')
    
    <form @submit.stop.prevent="credit_card_create_order">
      <div class="content">
        @include('new.pages.checkout.templates.vc2.left_column')
        @include('new.pages.checkout.templates.vc2.right_column')
      </div>
      
      <div class="payment-controls" v-if="!isPurchasAlreadyExists">
        <div v-if="form.payment_provider && form.payment_provider !== 'paypal'">
          @include('new.pages.checkout.form.terms')
        </div>

        <div
          v-if="form.payment_provider" 
          v-show="form.payment_provider === 'paypal'"
          class="paypal-payment-block"
        >

          @include('new.components.error', [
            'ref' => 'paypal_payment_error',
            'active' => 'payment_error && form.payment_provider === \'paypal\'',
            'class' => 'paypal-payment-error',
            'label_code' => 'payment_error',
          ])

          @include('new.pages.checkout.payment.paypal_button')
        </div>

        <div
          v-if="form.payment_provider" 
          v-show="form.payment_provider !== 'paypal'"
          class="payment-block"
        >

          @include('new.components.error', [
            'ref' => 'payment_error',
            'active' => 'payment_error',
            'class' => 'payment-error',
            'label_code' => 'payment_error',
          ])

          @include('new.pages.checkout.payment.pay_card_button', ['label' => t('vc2.pay_button')])
        </div>

        @include('new.pages.checkout.blocks.safe_payment')
      </div>
    </form>

    @include('new.pages.checkout.templates.vc2.guarantee')
    @include('new.pages.checkout.blocks.safe_invoice')
  </div>
@endsection
