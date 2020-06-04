<div class="providers">

  <div class="badge details">
    <div class="title">{!! t('vc1.providers.checkout') !!}</div>
    <div class="triangle"><div></div></div>
  </div>

  <div class="buttons">
    @include('new.pages.checkout.payment.credit_cards')

    @include('new.pages.checkout.payment.paypal_button')

    @include('new.components.error', [
      'ref' => 'paypal_payment_error',
      'active' => 'payment_error && form.payment_provider === \'paypal\'',
      'class' => 'paypal-payment-error',
      'label_code' => 'payment_error',
    ])

    <div class="or" v-if="is_apm_visible">{!! t('vc1.providers.or') !!}</div>

    @include('new.pages.checkout.payment.apm_buttons')
  </div>

  <div 
    class="badge checkout" 
    v-if="form.payment_provider" 
    v-show="form.payment_provider !== 'paypal'"
  >
    <div class="title">{!! t('vc1.providers.details') !!}</div>
    <div class="triangle"><div></div></div>
  </div>

</div>
