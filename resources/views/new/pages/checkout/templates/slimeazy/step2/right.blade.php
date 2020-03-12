<div class="right">
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

  <div class="payment-info">Payment Information:</div>

  <div class="form">
    @include('new.pages.checkout.form.installments')
    @include('new.pages.checkout.form.card_holder')
    @include('new.pages.checkout.form.card_type')
    @include('new.pages.checkout.form.card_number')
    @include('new.pages.checkout.form.card_date')
    @include('new.pages.checkout.form.card_cvv')
    @include('new.pages.checkout.form.document_type')
    @include('new.pages.checkout.form.document_number')
    @include('new.pages.checkout.form.terms')

    @include('new.components.error', [
      'ref' => 'payment_error',
      'active' => 'payment_error && form.payment_provider === \'credit-card\'',
      'class' => 'payment-error',
      'label_code' => 'payment_error',
    ])
  </div>

  @include('new.pages.checkout.payment.pay_card_button', ['label' => 'Place Order'])
</div>
