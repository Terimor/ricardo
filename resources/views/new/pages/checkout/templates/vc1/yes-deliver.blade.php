<div class="yes-deliver">

  <img
    alt=""
    class="img-check lazy"
    data-src="{{ $cdn_url }}/assets/images/checkout/vc1/check.png">

  <div class="yes">{!! t('vc1.yes') !!}</div>

  <div class="deliver">
    {!! t('vc1.deliver', ['product' => $product->product_name]) !!}
  </div>

</div>
