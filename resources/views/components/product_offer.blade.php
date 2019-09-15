<div class="container offer">
    <p><span class="bold">{{ t('checkout.header_banner.prefix') }}:</span> {{$product->long_name}}</p>
    <p>{{ t('checkout.header_banner.price') }}:&nbsp;<span id="old-price" class="price-object productprice-old-object strike"> {{$product->prices['1'] ? $product->prices['1']['old_value_text'] : ''}}</span>
        <span class="price-span">
          <b><span id="new-price" class="price-object productprice-object"> {{$product->prices['1'] ? $product->prices['1']['value_text'] : ''}}</span></b>
        </span>&nbsp;({{$product->prices['1'] ? $product->prices['1']['discount_percent'] : ''}}% {{ t('checkout.header_banner.discount') }})
    </p>
</div>
