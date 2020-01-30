<div class="right-column">

  <div class="title">
    <i class="fa fa-shopping-cart"></i>
    Order Summary
  </div>

  @include('new.pages.checkout.form.installments')

  <div class="product">
    <div class="name">{!! $product->product_name !!}</div>
    <div class="text">
      <i class="fa fa-download"></i>
      Digital Product
    </div>
    <div class="total">@{{ xprice_total_text }}</div>
  </div>

  <div class="access">
    Immediate access to this product or service is available once payment is approved.
  </div>

  @include('new.pages.checkout.form.warranty')
  @include('new.pages.checkout.form.terms')

  <div
    v-show="form.payment_provider === 'paypal'"
    class="paypal-payment-block">

    @include('new.components.error', [
      'ref' => 'paypal_payment_error',
      'active' => 'payment_error && form.payment_provider === \'paypal\'',
      'class' => 'paypal-payment-error',
      'label_code' => 'payment_error',
    ])

    @include('new.pages.checkout.payment.paypal_button')
  </div>

  <div
    v-show="form.payment_provider === 'credit-card'"
    class="payment-block">

    @include('new.components.error', [
      'ref' => 'payment_error',
      'active' => 'payment_error',
      'class' => 'payment-error',
      'label_code' => 'payment_error',
    ])

    @include('new.pages.checkout.payment.pay_card_button', ['label' => 'Pay Now'])
  </div>

  @include('new.pages.checkout.blocks.safe_payment')

</div>
