<div>Product name: {{ $product->product_name }}</div>
<div>Product description: {{ $product->description['en'] }}</div>
<div>Product long_name: {{ $product->long_name['en'] }}</div>
<div>Product logo_image: {{ $product->logoImage->urls['en'] }}</div>
<div>Product upsell_hero_image: {{ $product->upsellHeroImage->urls['en'] }}</div>
<div>Product category: {{ $product->category->name }}</div>
<br>
<br>
<br>

{{--@json($product)--}}
