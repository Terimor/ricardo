<div
  v-if="eps_method"
  class="eps-button"
  @click="eps_button_click">

  <div
    class="eps-button-arrow fa"
    :class="eps_button_class_list">
  </div>

  @include('new.components.radio', [
    'active' => 'form.payment_provider === \'eps\'',
    'class' => 'eps-button-radio',
  ])

  <div class="eps-button-label">EPS</div>

  <img
    class="eps-button-image lazy"
    data-src="{{ $cdn_url }}/assets/images/cc-icons/eps.png" />

</div>
