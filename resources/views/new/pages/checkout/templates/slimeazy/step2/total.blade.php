<transition name="fade">
  <div v-if="form.deal" class="total">
    <div class="left">
      <img class="guarantee lazy" data-src="{{ $cdn_url }}/assets/images/checkout/slimeazy/total-guarantee.png" />
    </div>
    <div class="right">
      <div class="row">
        <div class="column column1">Sub-Total</div>
        <div class="column column2">@{{ xprice_text }}</div>
      </div>
      <div v-if="form.warranty" class="row">
        <div class="column column1">3 Years Additional Warranty</div>
        <div class="column column2">@{{ xprice_warranty_text }}</div>
      </div>
      <div class="line"></div>
      <div class="row last">
        <div class="column column1">Total</div>
        <div class="column column2">@{{ xprice_total_text }}</div>
      </div>
      <div class="line"></div>
    </div>
  </div>
</transition>
