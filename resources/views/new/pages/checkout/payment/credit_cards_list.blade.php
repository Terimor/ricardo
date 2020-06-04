<div class="credit-cards-list">
  <div
    v-for="(payment_method, name) in payment_methods"
    v-if="true{!! (empty($with_paypal) ? " && name !== 'instant_transfer'" : '') !!}{!! (empty($with_apm) ? " && !payment_method.is_apm" : '') !!}"
    :class="{ ['credit-card-' + name]: true }"
    class="credit-card-item">

    <img
      :src="payment_method.logo"
      :title="payment_method.name" />

  </div>

  @if (!empty($paypal))
    <div
      v-if="!is_paypal_hidden && paypal_payment_method"
      class="credit-card-item credit-card-paypal">

      <img
        :src="paypal_payment_method.logo"
        :title="paypal_payment_method.name" />

    </div>
  @endif
</div>
