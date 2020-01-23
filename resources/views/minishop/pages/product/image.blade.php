<div class="image">
  <div
    class="wrapper d-flex"
    :style="image_style">

    @foreach ($product->image as $image)
      <div class="item d-flex align-items-center justify-content-center">
        <img
          alt=""
          src="{{ $image }}" />
      </div>
    @endforeach

    @if (!empty($product->vimeo_id))
      <div class="item">
        <iframe
          src="https://player.vimeo.com/video/{{ $product->vimeo_id }}"
          allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture"
          allowfullscreen=""
          frameborder="0">
        </iframe>
      </div>
    @endif

  </div>
</div>
