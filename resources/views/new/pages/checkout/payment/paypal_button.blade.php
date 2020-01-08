<div
  ref="paypal_button"
  class="paypal-button"
  v-if="!is_paypal_hidden && (paypal_button_init() || true)"
  v-show="form.installments === 1">

  <div class="paypal-button-original"></div>

  <div
    class="paypal-button-shim"
    :class="{ 'active': !is_submitted }">

    <div
      class="paypal-button-arrow fa"
      :class="{ ['fa-chevron-' + (!is_rtl ? 'right' : 'left')]: true }"></div>

    <div
      v-if="is_submitted"
      class="paypal-button-disabled"></div>

    <div class="paypal-button-label">{{ t('checkout.paypal.risk_free') }}</div>

    <img
      class="paypal-button-image"
      src="{{ $cdn_url }}/assets/images/paypal-highq.png" />

  </div>

</div>
