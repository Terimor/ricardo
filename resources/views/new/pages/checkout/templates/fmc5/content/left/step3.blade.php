<transition name="fade">
  <div
    v-if="step === 3"
    class="step3">

    @include('new.pages.checkout.payment.credit_cards')

    <div class="paypal-container">
      @include('new.pages.checkout.payment.paypal_button')

      @include('new.components.error', [
        'ref' => 'paypal_payment_error',
        'active' => 'payment_error && form.payment_provider === \'paypal\'',
        'class' => 'paypal-payment-error',
        'label_code' => 'payment_error',
      ])
    </div>

    @include('new.pages.checkout.payment.apm_buttons')

    <div class="space-20"></div>

    <transition name="fade">
      <div
        ref="form"
        v-if="form.payment_provider && form.payment_provider !== 'paypal'"
        class="form">

        <template v-if="form.payment_provider === 'credit-card'">
          @include('new.pages.checkout.form.card_holder')
          @include('new.pages.checkout.form.card_type')
          @include('new.pages.checkout.form.card_number')
          @include('new.pages.checkout.form.card_date')
          @include('new.pages.checkout.form.card_cvv')
          @include('new.pages.checkout.form.document_type')
          @include('new.pages.checkout.form.document_number')
        </template>

        @include('new.pages.checkout.form.terms')

        @include('new.components.error', [
          'ref' => 'payment_error',
          'active' => 'payment_error && form.payment_provider !== \'paypal\'',
          'class' => 'payment-error',
          'label_code' => 'payment_error',
        ])

      </div>
    </transition>

    @include('new.pages.checkout.form.warranty')

  </div>
</transition>
