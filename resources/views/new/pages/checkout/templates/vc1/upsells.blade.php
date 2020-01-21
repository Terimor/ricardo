@if (count($product->upsells) > 0)

  <div class="upsells">

    <img
      alt=""
      class="img-check"
      src="{{ $cdn_url }}/assets/images/checkout/vc1/check.png">

    <div class="title">{!! t('vc1.upsells.title') !!}</div>

    @foreach ($upsells as $upsell)
      <div class="upsell">
        
        <img
          alt=""
          class="image"
          src="{{ $upsell['image'] }}" />

        <div class="text">
          <div class="name">{{ $upsell['long_name'] }}</div>
          <div class="extra">
            {!! t('vc1.upsells.extra', ['count' => $upsell['upsellPrices'][2]['price_text'], 'amount' => $upsell['upsellPrices'][1]['price_text']]) !!}
          </div>
        </div>

        <div class="prices">
          <div class="old-price">{{ $upsell['upsellPrices'][2]['price_text'] }}</div>
          <div class="new-price">{{ $upsell['upsellPrices'][1]['price_text'] }}</div>
        </div>

      </div>
    @endforeach

  </div>

@endif
