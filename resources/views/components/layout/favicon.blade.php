@if (isset($product) && isset($product->favicon_image))

  <link
    rel="shortcut icon"
    href="{{ $product->favicon_image }}" />

@endif
