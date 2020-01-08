<div class="description">
  {!!
    preg_replace(
      '/<li([^>]*)>(((?!(<\/li>)).)*)<\/li>/',
      '<li$1><img src="' . $cdn_url . '/assets/images/fmc5-list-check.png" /><div>$2</div></li>',
      $product->description
    )
  !!}
</div>
