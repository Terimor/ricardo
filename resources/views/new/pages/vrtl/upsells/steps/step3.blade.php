<div
  v-if="order_upgraded_visible"
  class="order-upgraded">
  {!! t('vc_upsells.order_upgraded') !!}
</div>
<div class="page-title">A complete guide on how to build a geothermal heat pump.</div>
<div class="upsell-long-name">{{ $product->long_name }}</div>
<div class="upsell-last-call-text">
  This will help you cool your house durring summer or heat it durring winter with less than 30% of the energy that you already consume. You will learn how to convert a regular AC unit into a geothermal heat pump. A regular heat pump cost more than<br><strong>$15 000. This is an aswesome way to start making money as well if you are going to sell it to other people.</strong>
</div>
<div class="last-call-card">
  <div class="last-call-card-title">{!! t('vc_upsells.last_call_card.title', ['product' => $product->product_name]) !!}</div>
  <div class="last-call-card-inside">
    <img class="last-call-card-image lazy" data-src="{{ $product->image[0] ?? '' }}" />
    <div class="last-call-card-download">{!! t('vc_upsells.last_call_card.download') !!}</div>
    <div class="last-call-card-label-1">{!! t('vc_upsells.last_call_card.label_1') !!}</div>
    <div class="last-call-card-price">$17!</div>
    <div
      class="last-call-card-submit"
      @click="add_upsell">
      {!! t('vc_upsells.last_call_card.submit') !!}
    </div>
    <div
      class="last-call-card-label-2"
      @click="add_upsell">
      {!! t('vc_upsells.last_call_card.label_2') !!}
    </div>
    <div
      class="last-call-card-label-3"
      @click="cancel">
      {!! t('vc_upsells.last_call_card.label_3') !!}
    </div>
  </div>
</div>
