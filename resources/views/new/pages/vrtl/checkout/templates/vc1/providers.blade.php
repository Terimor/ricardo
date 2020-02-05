<div class="providers">

  <div class="badge details">
    <div class="title">{!! t('vc1.providers.checkout') !!}</div>
    <div class="triangle"><div></div></div>
  </div>

  <div class="buttons">
    @include('new.pages.checkout.payment.paypal_button')

    @include('new.components.error', [
      'ref' => 'paypal_payment_error',
      'active' => 'payment_error && form.payment_provider === \'paypal\'',
      'class' => 'paypal-payment-error',
      'label_code' => 'payment_error',
    ])

    <div class="or">{!! t('vc1.providers.or') !!}</div>
  </div>

  <div class="badge checkout">
    <div class="title">{!! t('vc1.providers.details') !!}</div>
    <div class="triangle"><div></div></div>
  </div>

</div>
