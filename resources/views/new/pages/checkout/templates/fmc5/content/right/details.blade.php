<div class="details">

  <div class="inside">

    <div class="product-name">{{ $product->product_name }}</div>

    <div class="product-long-name">{{ $product->long_name }}</div>

    <div class="product-bestseller">
      <div class="product-bestseller-triangle"></div>
      <div>#1 {{ t('fmc5.n1_bestseller') }}</div>
    </div>

    <img
      class="lazy product-5star"
      data-src="{{ $cdn_url }}/assets/images/fmc5-5star.svg" />

  </div>

  <img
    class="lazy product-image"
    data-src="{{ $product->image[0] }}" />

</div>
