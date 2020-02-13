@if (count($product->skus) > 0 && (!Request::get('variant') || Request::get('variant') === '0'))

  <div
    class="variant-field scroll-when-error"
    :class="{ opened: variant_opened, up: variant_up, invalid: $v.form.variant.$dirty && $v.form.variant.$invalid }">

    <div class="variant-field-label">{{ t('checkout.select_variant') }}</div>

    <div class="inside">

      <div
        ref="variant_field_input"
        class="variant-field-input"
        @click="variant_toggle">

        <img v-if="form.variant" :src="variant_image" class="variant-field-input-image" alt="" />
        <div class="variant-field-input-label">@{{ form.variant ? variant_name : '' }}</div>
        <i class="fa fa-angle-down"></i>

      </div>

      <div
        v-if="variant_opened"
        class="variant-field-backdrop"
        @click="variant_toggle"></div>

      <transition name="slide-down">
        <div
          v-if="variant_opened"
          class="variant-field-dropdown">

          @foreach ($product->skus as $sku)
            <div
              class="variant-field-item"
              :class="{ active: form.variant === '{{ $sku['code'] }}' }"
              @click="variant_change('{{ $sku['code'] }}')">

              <img src="{{ $sku['quantity_image'][1] }}" alt="" />
              <div>{{ $sku['name'] }}</div>

            </div>
          @endforeach

        </div>
      </transition>

    </div>

  </div>

@endif
