@if (!empty($product->free_files) && is_array($product->free_files))

  <div class="upsells">

    <img
      alt=""
      class="img-check lazy"
      data-src="{{ $cdn_url }}/assets/images/checkout/vc1/check.png">

    <div class="title">{!! t('vc1.upsells.title') !!}</div>

    @foreach ($product->free_files as $file)
      <div class="upsell">
        
        <img
          alt=""
          class="image lazy"
          data-src="{{ $file['image'] ?? $file['image'] }}" />

        <div class="text">
          <div class="name">{{ $file['title'] }}</div>
        </div>

      </div>
    @endforeach

  </div>

@endif
