@if (isset($product) && isset($product->is_discount) && $product->is_discount)
  <div
    v-if="covid_fixed_visible"
    class="covid-fixed">
    <div
      class="close-button"
      @click="covid_fixed_close">
      <i class="fa fa-times"></i>
    </div>
    {!! t('fixed.covid.content') !!}
  </div>
@endif
