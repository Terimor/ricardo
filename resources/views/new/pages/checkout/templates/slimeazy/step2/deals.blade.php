<div class="deals">
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

      <div class="header">
        <div class="left">
          <div>Buy {{ $deals_main_quantities[$deal['quantity']] }}</div>
          @if ($deals_free_quantities[$deal['quantity']])
            <div>&nbsp;Get {{ $deals_free_quantities[$deal['quantity']] }} {{ $product->product_name }} Free</div>
          @else
            <div>&nbsp;{{ $product->product_name }}</div>
          @endif
        </div>
        <div class="right">Save {{ $deal['discount_percent'] }}%</div>
      </div>

      <div class="content">
        <div>
          <img v-if="form.deal === {{ $deal['quantity'] }}" class="lazy" data-src="{{ $cdn_url }}/assets/images/checkout/slimeazy/checked.png" />
          <img v-else class="lazy" data-src="{{ $cdn_url }}/assets/images/checkout/slimeazy/unchecked.png" />
        </div>
        <img class="image lazy" data-src="{{ $product->image[0] }}" />
        <img class="guarantee lazy" data-src="{{ $cdn_url }}/assets/images/checkout/slimeazy/deal-guarantee.png" />
        <div class="prices">
          <div class="old-price">
            &nbsp;
            <div v-if="form.installments === 6">6x {{ $product->prices[$deal['quantity']]['installments6_old_value_text'] }}</div>
            <div v-else-if="form.installments === 3">3x {{ $product->prices[$deal['quantity']]['installments3_old_value_text'] }}</div>
            <div v-else>{{ $product->prices[$deal['quantity']]['old_value_text'] }}</div>
            &nbsp;
          </div>
          <div class="new-price">
            <div v-if="form.installments === 6">6x {{ $product->prices[$deal['quantity']]['installments6_value_text'] }}</div>
            <div v-else-if="form.installments === 3">3x {{ $product->prices[$deal['quantity']]['installments3_value_text'] }}</div>
            <div v-else>{{ $product->prices[$deal['quantity']]['value_text'] }}</div>
          </div>
          @include('new.pages.checkout.payment.credit_cards_list', ['paypal' => 'true'])
          <div class="button">Select Package</div>
        </div>
      </div>

    </div>
  @endforeach
</div>
