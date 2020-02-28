<div class="credit-cards-list">
  <div
    v-for="(payment_method, name) in payment_methods"
    v-if="name !== 'instant_transfer'"
    :class="{ ['credit-card-' + name]: true }"
    class="credit-card-item">

    <img
      class="lazy"
      :data-src="payment_method.logo"
      :title="payment_method.name" />

  </div>
</div>
