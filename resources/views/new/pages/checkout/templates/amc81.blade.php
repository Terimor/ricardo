@extends('new.pages.checkout')


@section('fonts_checkout')
  @include('new.fonts.roboto')
@endsection


@section('styles_checkout')
  <link
    href="{{ mix_cdn('assets/css/new/pages/checkout/templates/amc81.css') }}"
    onload="js_deps.ready.call(this, 'page-styles')"
    rel="stylesheet"
    media="none" />
@endsection


@section('scripts_checkout')
  <script
    src="{{ mix_cdn('assets/js/new/pages/checkout/templates/amc81.js') }}"
    onload="js_deps.ready('page-scripts')"
    async></script>
@endsection


@section('header_before')
  @include('new.pages.checkout.templates.amc81.header_before')
@endsection


@section('header_after')
  @include('new.pages.checkout.templates.amc81.header_after')
@endsection


@section('content_checkout')
  <div class="amc81">
    <div class="main-content">
      <div class="left-column">
        @include('new.pages.checkout.templates.amc81.left-column.users-online')
        @include('new.pages.checkout.templates.amc81.left-column.step1-title')
        @include('new.pages.checkout.templates.amc81.left-column.deals')
        @include('new.pages.checkout.templates.amc81.left-column.step2-title')
        @include('new.pages.checkout.form.variant')
        @include('new.pages.checkout.templates.amc81.left-column.summary')
      </div>
      <div class="right-column">
        @include('new.pages.checkout.templates.amc81.right-column.step3-title')
        @include('new.pages.checkout.form.warranty')
        @include('new.pages.checkout.payment.credit_cards')
        @include('new.pages.checkout.payment.paypal_button')
        @include('new.pages.checkout.form.errors.paypal_error')
        @include('new.pages.checkout.payment.apm_buttons')
        <div v-show="form.payment_provider && form.payment_provider !== 'paypal'" class="form" ref="form">
          @include('new.pages.checkout.templates.amc81.right-column.step4-title')
          @include('new.pages.checkout.form.first_name')
          @include('new.pages.checkout.form.last_name')
          @include('new.pages.checkout.form.email')
          @include('new.pages.checkout.form.phone')
          @include('new.pages.checkout.templates.amc81.right-column.step5-title')
          @include('new.pages.checkout.form.zipcode', ['br' => true])
          @include('new.pages.checkout.form.street')
          @include('new.pages.checkout.form.building')
          @include('new.pages.checkout.form.complement')
          @include('new.pages.checkout.form.district')
          @include('new.pages.checkout.form.city')
          @include('new.pages.checkout.form.state')
          @include('new.pages.checkout.form.zipcode')
          @include('new.pages.checkout.form.country')
          <template v-show="form.payment_provider === 'credit-card'">
            @include('new.pages.checkout.templates.amc81.right-column.step6-title')
            @include('new.pages.checkout.form.card_holder')
            @include('new.pages.checkout.form.card_type')
            @include('new.pages.checkout.form.card_number')
            @include('new.pages.checkout.form.card_date')
            @include('new.pages.checkout.form.card_cvv')
            @include('new.pages.checkout.form.document_type')
            @include('new.pages.checkout.form.document_number')
          </template>
          @include('new.pages.checkout.form.terms')
          @include('new.pages.checkout.form.errors.payment_error')
          @include('new.pages.checkout.payment.pay_card_button', ['label' => t('checkout.payment_form.submit_button')])
        </div>
        @include('new.pages.checkout.templates.amc81.right-column.guarantee')
      </div>
    </div>
    @include('new.pages.checkout.templates.amc81.reviews')
    @include('new.pages.checkout.templates.amc81.question')
  </div>
@endsection
