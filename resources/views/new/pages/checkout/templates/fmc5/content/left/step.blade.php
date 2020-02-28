<div
  ref="step"
  class="step">

  <img
    class="lazy"
    :data-src="'{{ $cdn_url }}/assets/images/fmc5-step' + step + '.png'" />

  <strong>{{ t('fmc5.step') }} #@{{ step }}:&nbsp;</strong>

  <span v-if="step === 1">{{ t('fmc5.steps.quantity') }}</span>

  <span v-if="step === 2">{{ t('fmc5.steps.shipping') }}</span>

  <span v-if="step === 3">{{ t('fmc5.steps.payment') }}</span>

</div>
