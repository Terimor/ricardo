<div v-if="form.deal" class="summary">
  <div class="line header-line">
    <div class="left">{{ t('checkout.summary.item') }}</div>
    <div class="right">{{ t('checkout.summary.amount') }}</div>
  </div>
  <div class="line deals-line">
    <div class="left">@{{ form.deal }}x {{ $product->long_name }}:</div>
    <div class="right">@{{ xprice_text }}</div>
  </div>
  <div v-if="form.warranty" class="line warranty-line">
    <div class="left">{{ t('checkout.summary.warranty') }}:</div>
    <div class="right">@{{ xprice_warranty_text }}</div>
  </div>
  <div class="line total-line">
    <div class="left">{{ t('checkout.summary.total') }}:</div>
    <div class="right">@{{ xprice_total_text }}</div>
  </div>
</div>
