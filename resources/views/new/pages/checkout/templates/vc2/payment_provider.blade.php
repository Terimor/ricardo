<div class="payment-provider">

  <div class="label">{!! t('vc2.payment_method.label') !!}</div>

  <div class="tabs">

    <div
      class="tab"
      :class="{ active: form.payment_provider === 'credit-card' }"
      @click="payment_provider_change('credit-card')">
      <i class="fa fa-credit-card-alt"></i>
      {!! t('vc2.payment_method.credit_card') !!}
    </div>

    <div
      class="tab"
      :class="{ active: form.payment_provider === 'paypal' }"
      @click="payment_provider_change('paypal')">
      <i class="fa fa-paypal"></i>
      {!! t('vc2.payment_method.paypal') !!}
    </div>
  </div>

</div>
