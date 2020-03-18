<div
  v-if="is_apm_visible"
  class="apm-buttons">

  <div
    v-if="payment_method.is_apm"
    v-for="(payment_method, name) in payment_methods"
    :class="{ ['apm-button-' + name]: true }"
    class="apm-button"
    @click="apm_button_click(name)">

    <div
      class="apm-button-arrow fa"
      :class="apm_button_class_list">
    </div>

    @include('new.components.radio', [
      'active' => 'form.payment_provider === name',
      'class' => 'apm-button-radio',
    ])

    <div class="apm-button-label">
      @{{ payment_method.name }}
    </div>

    <img
      class="apm-button-image lazy"
      :data-src="payment_method.logo.replace('-curved', '')" />

  </div>

</div>
