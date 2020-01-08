<div class="details">

  <div class="inside">

    <div class="product-name">{{ $product->product_name }}</div>

    <div class="product-long-name">{{ $product->long_name }}</div>

    <div class="product-bestseller">
      <div class="product-bestseller-triangle"></div>
      <div>#1 {{ t('fmc5.n1_bestseller') }}</div>
    </div>

    <img
      src="{{ $cdn_url }}/assets/images/fmc5-5star.svg"
      class="product-5star" />

  </div>

  <img
    src="{{ $product->image[0] }}"
    class="product-image" />

</div>
