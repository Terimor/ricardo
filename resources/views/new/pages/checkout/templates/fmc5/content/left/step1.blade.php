<transition name="fade">
  <div
    v-if="step === 1"
    class="step1">

    @include('new.pages.checkout.form.installments')

    <div class="deals">

      @include('new.pages.checkout.form.errors.deal_error')

      @foreach ($deals as $deal)
        <div
          class="deal"
          :class="{
            selected: form.deal === {{ $deal['quantity'] }},
            sellout: {{ $deal['sellout'] ? 'true' : 'false' }},
          }"
          @click="deal_change({{ $deal['quantity'] }})">

          <div class="deal-separator"></div>

          @if ($deal['sellout'])
            <div
              class="deal-sellout"
              @click.stop></div>
          @endif

          @if ($deal['is_bestseller'] || $deal['is_popular'] || (isset($product->labels) && isset($product->labels[$deal['quantity']])))
            <div class="deal-special">

              <div class="deal-left">

                @if (isset($product->labels) && isset($product->labels[$deal['quantity']]))
                  <div class="deal-popular">
                    <div class="deal-special-triangle"></div>
                    <div>{{ $product->labels[$deal['quantity']] }}</div>
                  </div>
                @elseif ($deal['is_bestseller'])
                  <div class="deal-bestseller">
                    <div class="deal-special-triangle"></div>
                    <div>{{ t('fmc5.bestseller') }}</div>
                  </div>
                @elseif ($deal['is_popular'])
                  <div class="deal-popular">
                    <div class="deal-special-triangle"></div>
                    <div>{{ t('fmc5.bestdeal') }}</div>
                  </div>
                @endif

              </div>

              <div class="deal-right">
                <div class="deal-sameas">{{ t('fmc5.sameas') }}</div>
              </div>

            </div>
          @endif

          <div class="deal-content">

            <div class="deal-left">

              @include('new.components.radio', [
                'active' => 'form.deal === ' . $deal['quantity'],
                'class' => 'deal-radio',
              ])

              <div class="deal-label">
                <div class="deal-count">{{ $deals_main_quantities[$deal['quantity']] }}x</div>
                <div class="deal-name">&nbsp;{{ $product->product_name }}&nbsp;</div>
                @if ($deals_free_quantities[$deal['quantity']])
                  <div class="deal-free">+ {{ $deals_free_quantities[$deal['quantity']] }} {{ t('fmc5.free') }}</div>
                @endif
                @if ($product->unit_qty > 1)
                  <span class="deal-unit-qty">&nbsp;{!! t('product.unit_qty.total', ['count' => $deal['quantity'] * $product->unit_qty]) !!}</span>
                @endif
              </div>
              <div class="deal-discount">
                <div class="deal-discount-o">o</div>
                <div class="deal-discount-value">{{ $deal['discount_percent'] }}%</div>
                <div class="deal-discount-off">&nbsp;{{ t('fmc5.off') }}</div>
              </div>

            </div>

            <div class="deal-right">

              <div class="deal-price-one">
                <div v-if="form.installments === 6">6x {{ $deal['installments6_unit_value_text'] }}</div>
                <div v-if="form.installments === 3">3x {{ $deal['installments3_unit_value_text'] }}</div>
                <div v-if="form.installments === 1">{{ $deal['unit_value_text'] }}</div>
                <div v-if="{{ $deal['quantity'] }} > 1">&nbsp;{{ t('fmc5.each') }}</div>
              </div>

              <div class="deal-price-total">
                <div v-if="form.installments === 6">(6x {{ $deal['installments6_value_text'] }})</div>
                <div v-if="form.installments === 3">(3x {{ $deal['installments3_value_text'] }})</div>
                <div v-if="form.installments === 1">({{ $deal['value_text'] }})</div>
              </div>

              <div class="deal-free-shipping">{{ t('fmc5.free_shipping') }}</div>

            </div>

          </div>

        </div>
      @endforeach

    </div>

    @include('new.pages.checkout.form.variant')

  </div>
</transition>
