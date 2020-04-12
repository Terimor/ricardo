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
  <div class="total-block">
    <div class="image">
      <img class="lazy" data-src="{{ $cdn_url }}/assets/images/checkout/amc81/today-you-saved.png" />
    </div>
    <div class="prices">
      <div class="old">{{ t('checkout.reg') }}: @{{ xprice_perdeal_old_text[form.deal] }}</div>
      <div class="total">{{ t('checkout.summary.total') }}: @{{ xprice_total_text }}</div>
    </div>
  </div>
</div>
