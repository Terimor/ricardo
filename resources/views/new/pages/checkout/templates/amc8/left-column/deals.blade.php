<div class="deals">

  @include('new.pages.checkout.form.installments')
  @include('new.pages.checkout.form.errors.deal_error')

  @foreach ($deals as $deal)
    <div
      class="deal"
      :class="{
        selected: form.deal === {{ $deal['quantity'] }},
        sellout: {{ $deal['sellout'] ? 'true' : 'false' }},
      }"
      @click="deal_change({{ $deal['quantity'] }})">

      @if ($deal['sellout'])
        <div class="deal-sellout" @click.stop></div>
      @endif

      @include('new.components.radio', [
        'active' => 'form.deal === ' . $deal['quantity'],
        'class' => 'deal-radio',
      ])

      <div class="deal-content">
        <div class="deal-label">
          <span class="deal-label-1">
            <span class="deal-count">{{ $deals_main_quantities[$deal['quantity']] }}x</span>
            <span class="deal-name">{{ $product->product_name }}</span>
            @if ($deals_free_quantities[$deal['quantity']])
              <span class="deal-free">+ {{ $deals_free_quantities[$deal['quantity']] }} {{ t('checkout.free') }}</span>
            @endif
            @if ($product->unit_qty > 1)
              <span class="deal-unit-qty">{!! t('product.unit_qty.total', ['count' => $deal['quantity'] * $product->unit_qty]) !!}</span>
            @endif
          </span>
          <span>&nbsp;-&nbsp;</span>
          <span class="deal-label-2">
            {!! !empty($product->labels[$deal['quantity']]) ? $product->labels[$deal['quantity']] : t('checkout.common_labels.q' . $deal['quantity']) !!}
          </span>
        </div>
      </div>

      <div class="deal-prices">
        <div class="deal-discount-name">
          @if ($deal['is_bestseller'])
            <img class="deal-discount-star lazy" data-src="{{ $cdn_url }}/assets/images/checkout/amc8/star.png" />
            <div class="deal-bestseller">{{ t('checkout.bestseller') }}</div>
          @endif
          @if ($deal['is_popular'])
            <img class="deal-discount-star lazy" data-src="{{ $cdn_url }}/assets/images/checkout/amc8/star.png" />
            <div class="deal-popular">{{ t('checkout.best_deal') }}</div>
          @endif
        </div>

        <div class="deal-old-price">
          <span class="deal-old-price-reg">{{ t('checkout.reg') }}&nbsp;</span>
          <span class="deal-old-price-value" v-html="xprice_perdeal_old_text[{{ $deal['quantity'] }}]"></span>
        </div>

        <div class="deal-new-price" v-html="xprice_perdeal_text[{{ $deal['quantity'] }}]"></div>
      </div>

    </div>
  @endforeach

</div>
