@if (count($product->skus) > 1 && (!Request::get('variant') || Request::get('variant') === '0'))
  <div class="step2-title">
    {{ t('checkout.step') }} @{{ step_numbers[2] }}: {{ t('checkout.select_variant') }}
  </div>
@endif
