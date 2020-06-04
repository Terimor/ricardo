<div class="right-column">

  <div class="title">
    <i class="fa fa-shopping-cart"></i>
    {!! t('vc2.summary.title') !!}
  </div>

  @include('new.pages.checkout.form.installments')

  <div class="product">
    <div class="name">{!! $product->product_name !!}</div>
    <div class="text">
      <i class="fa fa-download"></i>
      {!! t('vc2.product.text') !!}
    </div>
    <div class="total"><span v-cloak>@{{ xprice_total_text }}</span></div>
  </div>

  <div class="access">
    {!! t('vc2.product.access') !!}
  </div>
  
  <div
    v-if="form.payment_provider && form.payment_provider !== 'paypal'" 
  >
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
