<div class="reviews container">
  <div class="inside">
    <div class="back-image">
      <img class="lazy" data-src="{{ $product->bg_image ?? $cdn_url . '/assets/images/checkout/amc8/reviews-back.jpg' }}" />
    </div>
    @if (empty($product->bg_image))
      <div class="product-image">
        <img class="lazy" data-src="{{ $product->image[0] }}" />
      </div>
    @endif
    <div class="items">
      @foreach (array_slice($product->reviews, 0, 3) as $review)
        <div class="review">
          <img class="image lazy" data-src="{{ $review['image'] }}" />
          <div class="content">
            <div class="name">{{ $review['name'] }}<span class="city"> · {{ $review['city'] }}</span></div>
            <div class="text">{{ $review['text'] }}</div>
            <div class="bottom">
              <div class="like">{{ t('checkout.review.like') }}</div>
              <div class="reply">· {{ t('checkout.review.reply') }}</div>
              <div class="likes">· <i class="fa fa-thumbs-o-up"></i>{{ rand(16,32) }}</div>
              <div class="date">· {{ $review['date'] }}</div>
            </div>
          </div>
        </div>
      @endforeach
    </div>
  </div>
</div>
