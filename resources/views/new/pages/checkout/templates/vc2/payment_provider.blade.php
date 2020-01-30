<div class="payment-provider">

  <div class="label">Payment Method</div>

  <div class="tabs">

    <div
      class="tab"
      :class="{ active: form.payment_provider === 'credit-card' }"
      @click="payment_provider_change('credit-card')">
      <i class="fa fa-credit-card-alt"></i>
      Credit Card
    </div>

    <div
      class="tab"
      :class="{ active: form.payment_provider === 'paypal' }"
      @click="payment_provider_change('paypal')">
      <i class="fa fa-paypal"></i>
      PayPal
    </div>
  </div>

</div>
