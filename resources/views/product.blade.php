<div>Product name: {{ $product->product_name }}</div>
<div>Product description: {{ $product->description }}</div>
<div>Product long_name: {{ $product->long_name }}</div>
<div>Product logo_image: {{ isset($product->logoImage->urls) ? $product->logoImage->urls : '' }}</div>
<div>Product upsell_hero_image: {{ isset($product->upsellHeroImage->urls) ? $product->upsellHeroImage->urls : '' }}</div>
<div>Product category: {{ $product->category->name }}</div>
<br>
<br>
<br>

{{--@json($product)--}}
