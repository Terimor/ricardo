<div class="credit-cards-list">
  <div
    v-for="(payment_method, name) in payment_methods"
    v-if="true{!! (empty($with_paypal) ? " && name !== 'instant_transfer'" : '') !!}{!! (empty($with_apm) ? " && !payment_method.is_apm" : '') !!}"
    :class="{ ['credit-card-' + name]: true }"
    class="credit-card-item">

    <img
      class="lazy"
      :data-src="payment_method.logo"
      :title="payment_method.name" />

  </div>
</div>
