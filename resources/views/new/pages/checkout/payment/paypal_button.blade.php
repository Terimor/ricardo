<div
  ref="paypal_button"
  class="paypal-button"
  v-if="paypal_button_init() || true"
  v-show="!is_paypal_hidden">

  <div class="paypal-button-original"></div>

  <div
    class="paypal-button-shim"
    :class="{ 'active': !is_submitted }">

    <div
      class="paypal-button-arrow fa"
      :class="paypal_button_class_list">
    </div>

    <div
      v-if="is_submitted"
      class="paypal-button-disabled"></div>

    <div class="paypal-button-label">{{ t('checkout.paypal.risk_free') }}</div>

    <img
      class="lazy paypal-button-image"
      data-src="{{ $cdn_url }}/assets/images/paypal-highq.png" />

  </div>

</div>
