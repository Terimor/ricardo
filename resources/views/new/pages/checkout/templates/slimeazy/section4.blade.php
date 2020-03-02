<div class="section4 section-cover" style="background: url({{ $cdn_url }}/assets/images/checkout/slimeazy/about-bg.jpg) top center no-repeat">
  <div class="container">
    <div class="title">
      <div class="text">What Are People Saying About {{ $product->product_name }}</div>
      <div class="underline"></div>
    </div>
    <div class="slider">
      <img
        class="left lazy"
        data-src="{{ $cdn_url }}/assets/images/checkout/slimeazy/left-arrow.png"
        @click="section4_slider_left" />
      <img
        class="right lazy"
        data-src="{{ $cdn_url }}/assets/images/checkout/slimeazy/right-arrow.png"
        @click="section4_slider_right" />
      <div class="wrapper">
        <div class="inside" :style="section4_slider_style">
          @for ($i = 0; $i < 6; $i++)
          <div class="slide" :style="section4_slider_slide_style">
            <div class="image"><img class="lazy" data-src="{{ $cdn_url }}/assets/images/checkout/slimeazy/slider-img1.png" /></div>
            <div class="text">I thank Libido Daily Plus for letting me know what sex is. I lost couple of girls in my life as I could not satisfy them in my life. The thing has changes right now. My girlfriend visits me every single day so that we can have sex.</div>
            <div class="name">Roger</div>
          </div>
          @endfor
        </div>
      </div>
    </div>
  </div>
</div>
