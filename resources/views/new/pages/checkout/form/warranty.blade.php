<div
  ref="warranty_field"
  v-if="form.deal"
  class="warranty-field scroll-when-error{{ !empty($small) ? ' small' : '' }}"
  :class="{ invalid: $v.form.warranty.$dirty && $v.form.warranty.$invalid }"
  @click="warranty_change(!form.warranty)">

  <i class="warranty-arrow-left fa fa-arrow-left"></i>
  <i class="warranty-arrow-right fa fa-arrow-right"></i>

  <img
    class="warranty-field-image"
    src="{{ $cdn_url }}/assets/images/best-saller.png">

  @include('new.components.check', [
    'active' => 'form.warranty',
    'class' => 'warranty-field-check',
  ])

  <div class="warranty-field-label">
    {!! t('checkout.warranty') !!}: @{{ xprice_warranty_text }}
  </div>
  
</div>
