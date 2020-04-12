@if (count($product->skus) > 1 && (!Request::get('variant') || Request::get('variant') === '0'))
  <div class="step2-title">
    <span class="step">{{ t('checkout.step') }}</span>
    &nbsp;@{{ step_numbers[2] }}:&nbsp;
    <span class="title">{{ t('checkout.select_variant') }}</span>
  </div>
@endif
