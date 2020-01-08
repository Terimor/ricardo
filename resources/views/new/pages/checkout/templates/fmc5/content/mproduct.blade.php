<div class="mproduct">

  <div class="mproduct-title">{{ t('fmc5.mproduct.title') }}</div>

  <div class="mproduct-details">

    <img
      src="{{ $product->image[0] }}"
      class="mproduct-image" />

    <div class="mproduct-details-text">
      <div class="mproduct-long-name">{{ $product->long_name }}</div>
      <div class="mdescription">
        {!!
          preg_replace(
            '/<li([^>]*)>(((?!(<\/li>)).)*)<\/li>/',
            '<li$1><img src="' . $cdn_url . '/assets/images/fmc5-mlist-check.png" /><div>$2</div></li>',
            $product->description
          )
        !!}
      </div>
    </div>

  </div>

</div>
