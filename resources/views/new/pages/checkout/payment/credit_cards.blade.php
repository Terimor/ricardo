<div
  class="credit-cards"
  @click="credit_cards_click">

  <div
    class="credit-cards-arrow fa"
    :class="{ ['fa-chevron-' + (!is_rtl ? 'right' : 'left')]: true }"></div>

  @include('new.components.radio', [
    'active' => 'form.payment_provider === \'credit-card\'',
    'class' => 'credit-cards-radio',
  ])

  <div class="inside">
    
    <div class="credit-cards-label">{{ t('checkout.credit_cards') }}</div>

    <div class="credit-cards-list">
      @foreach ($setting['payment_methods'] as $name => $payment_method)
        @if ($name !== 'instant_transfer')
          <div class="credit-card-item credit-card-{{ $name }}">
            <img
              src="{{ $payment_method['logo'] }}"
              title="{{ $payment_method['name'] }}" />
          </div>
        @endif
      @endforeach
    </div>

  </div>

</div>
