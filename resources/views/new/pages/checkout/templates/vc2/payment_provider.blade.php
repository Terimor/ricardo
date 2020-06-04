<div class="payment-provider">

  <div class="label">{!! t('vc2.payment_method.label') !!}</div>

  <div class="tabs">

    <div
      class="tab"
      :class="{ active: form.payment_provider === 'credit-card' }"
      @click="payment_provider_change('credit-card')">
      <i class="fa fa-credit-card-alt"></i>
      {!! t('vc2.payment_method.credit_card') !!}
    </div>

    <div
      v-if="!is_paypal_hidden"
      class="tab"
      :class="{ active: form.payment_provider === 'paypal' }"
      @click="payment_provider_change('paypal')">
      <i class="fa fa-paypal"></i>
      {!! t('vc2.payment_method.paypal') !!}
    </div>

    <template v-if="is_apm_visible">
      <div
        v-if="payment_method.is_apm"
        v-for="(payment_method, name) in payment_methods"
        class="tab"
        :class="{ active: form.payment_provider === name }"
        @click="apm_button_click(name)">

        <img
          class="lazy"
          :data-src="payment_method.logo.replace('-curved', '')"
          style="height: 20px; margin-right: 5px;"
        />

        @{{ payment_method.name }}
      </div>
    </template>
  </div>

</div>
