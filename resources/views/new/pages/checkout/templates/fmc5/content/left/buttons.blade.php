<div class="buttons">

  <img
    v-if="step > 1"
    src="{{ $cdn_url }}/assets/images/fmc5-back.png"
    class="button-back"
    @click="back_click" />

  <div
    v-if="step === 1 || step === 2"
    class="button-next"
    @click="next_click">

    <div>{{ t('fmc5.next') }}</div>

  </div>

  <div
    v-if="step === 3 && form.payment_provider === 'credit-card'"
    :class="{ submitted: is_submitted }"
    class="button-next multi"
    @click="next_click">

    <div
      v-if="is_submitted"
      class="button-next-disabled"
      @click.stop>
      @include('new.components.spinner')
    </div>

    <div :class="{ hidden: is_submitted }">
      <div>{{ t('fmc5.complete') }}</div>
    </div>

  </div>

</div>
