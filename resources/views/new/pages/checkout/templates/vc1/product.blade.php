<div class="product">

  <div class="left">
    <div class="price-title">{!! t('vc1.product.title') !!}</div>
    <div class="price-old-value">
      <div class="strike"><div></div><div></div></div>
      {{ $product->prices[1]['old_value_text'] }}
    </div>
    <div class="price-new-value">{{ $product->prices[1]['value_text'] }}</div>
    <div class="sh">{!! t('vc1.product.sh') !!}</div>
  </div>

  <div class="triangles">
    <div></div><div></div><div></div>
  </div>

  <div class="right">
    <img
      alt=""
      class="image lazy"
      data-src="{{ $product->image[0] }}"
    />
  </div>

</div>
