<div class="guarantee">

  <div class="border">
    <img class="top lazy" data-src="{{ $cdn_url }}/assets/images/checkout/vc1/guarantee-top.png" />
    <div class="middle" style="background-image: url({{ $cdn_url }}/assets/images/checkout/vc1/guarantee-middle.png)"></div>
    <img class="bottom lazy" data-src="{{ $cdn_url }}/assets/images/checkout/vc1/guarantee-bottom.png" />
  </div>

  <div class="inside">
    <div class="headline">
      <img
        class="image lazy"
        data-src="{{ $cdn_url }}/assets/images/checkout/vc1/guarantee.png" />
      <div class="title">{!! t('vc1.guarantee.title') !!}</div>
    </div>
    <div class="text">{!! t('vc1.guarantee.text', ['product' => $product->long_name]) !!}</div>
  </div>

</div>
