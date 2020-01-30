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
