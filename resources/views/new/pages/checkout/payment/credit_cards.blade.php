<div
  class="credit-cards"
  @click="credit_cards_click">

  <div
    class="credit-cards-arrow fa"
    :class="credit_cards_class_list">
  </div>

  @include('new.components.radio', [
    'active' => 'form.payment_provider === \'credit-card\'',
    'class' => 'credit-cards-radio',
  ])

  <div class="inside">
    <div class="credit-cards-label">{{ t('checkout.credit_cards') }}</div>
    @include('new.pages.checkout.payment.credit_cards_list')
  </div>

</div>
