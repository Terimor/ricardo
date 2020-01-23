<div class="slider mt-4">

  <i
    class="fas fa-arrow-left"
    :class="{ active: slider_left_active }"
    @click="slider_left"></i>

  <div class="wrapper">
    <div
      class="inside d-flex"
      :style="slider_style">

      @php $index = 0; @endphp
      @foreach ($product->image as $image)
        <div
          class="item d-flex align-items-center justify-content-center"
          :class="{ active: image_index === {{ $index }} }"
          @click="slider_select({{ $index }})">
          <img
            alt=""
            src="{{ $image }}" />
        </div>
        @php $index++; @endphp
      @endforeach

      @if (!empty($product->vimeo_id))
        <div
          class="item"
          :class="{ active: image_index === {{ $index }} }"
          @click="slider_select({{ $index }})">
          <div class="iframe-cover"></div>
          <iframe
            src="https://player.vimeo.com/video/{{ $product->vimeo_id }}"
            allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture"
            allowfullscreen=""
            frameborder="0">
          </iframe>
        </div>
        @php $index++; @endphp
      @endif

    </div>
  </div>

  <i
    class="fas fa-arrow-right"
    :class="{ active: slider_right_active }"
    @click="slider_right"></i>

</div>
