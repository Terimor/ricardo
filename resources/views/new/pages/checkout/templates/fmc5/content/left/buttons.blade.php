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
    class="button-next multi"
    @click.capture.stop="next_click">
    @include('new.pages.checkout.payment.pay_card_button', ['label' => t('fmc5.complete')])
  </div>

</div>
