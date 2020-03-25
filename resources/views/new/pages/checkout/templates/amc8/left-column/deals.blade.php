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
        <div class="deal-discount-name">
          @if ($deal['is_bestseller'])
            <div class="deal-bestseller">{{ t('checkout.bestseller') }}</div>
          @endif
          @if ($deal['is_popular'])
            <div class="deal-popular">{{ t('checkout.best_deal') }}</div>
          @endif
        </div>

        <div class="deal-label">
          <div class="deal-count">{{ $deals_main_quantities[$deal['quantity']] }}x</div>
          <div class="deal-name">&nbsp;{{ $product->product_name }}&nbsp;</div>
          @if ($deals_free_quantities[$deal['quantity']])
            <div class="deal-free">+ {{ $deals_free_quantities[$deal['quantity']] }} {{ t('checkout.free') }}</div>
          @endif
        </div>

        <div class="deal-discount">
          <div>(</div>
          <div>{{ $deal['discount_percent'] }}% {{ t('checkout.discount') }}</div>
          @if ($deal['quantity'] > 1)
            <div>, <span v-html="xprice_perdeal_unit_text[{{ $deal['quantity'] }}]"></span>/{{ t('checkout.unit') }}</div>
          @endif
          <div>)</div>
        </div>
      </div>

      <div class="deal-prices">
        <div class="deal-new-price" v-html="xprice_perdeal_text[{{ $deal['quantity'] }}]"></div>
        <div class="deal-old-price" v-html="xprice_perdeal_old_text[{{ $deal['quantity'] }}]"></div>
      </div>

    </div>
  @endforeach

</div>
