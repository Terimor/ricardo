<div class="reviews">

  <div class="reviews-title">
    <div class="reviews-title-left"></div>
    <div class="reviews-title-right"></div>
    <div>{{ t('fmc5.reviews.title') }}</div>
  </div>

  <div class="reviews-block">

    <div class="rate-box">
      <div class="rate-box-value">4.8</div>
      <div class="rate-box-outof">{{ t('fmc5.rate_box.outof') }} 5.0</div>
    </div> 

    <img
      class="lazy stars-lines"
      data-src="{{ $cdn_url }}/assets/images/fmc5-stars-lines.png" />

    <div class="reviews-overall">
      <div>{{ t('fmc5.reviews.overall') }}</div>
      <img
        class="lazy"
        data-src="{{ $cdn_url }}/assets/images/fmc5-5star.svg" />
    </div>

    <div class="reviews-percent">
      <div class="reviews-percent-value">91%</div>
      <div class="reviews-percent-text">{{ t('fmc5.reviews.percent') }}</div>
    </div>

  </div>

  <div class="reviews-items">
    @foreach ($product->reviews as $review)
      <div class="review">

        <div
          class="review-5star"
          :style="{ width: {{ $review['rate'] }} * 20 + 'px' }">
          <img
            class="lazy"
            data-src="{{ $cdn_url }}/assets/images/fmc5-5star.svg" />
        </div>

        <div class="review-text">{!! $review['text'] !!}</div>
        <div class="review-name">{{ $review['name'] }} – {{ $review['date'] }}</div>

        <div class="review-verified">
          <img
            class="lazy"
            data-src="{{ $cdn_url }}/assets/images/fmc5-verified-check.svg" />
          <div>{{ t('fmc5.reviews.verified') }}</div>
        </div>

      </div>
    @endforeach
  </div>

</div>
