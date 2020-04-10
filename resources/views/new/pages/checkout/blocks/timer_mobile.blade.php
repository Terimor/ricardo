<div
  v-if="timer_enabled"
  class="timer-mobile">

  <div class="inside">
    <template v-if="timer_time === '00:00'">
      {!! t('checkout.timer.time_over') !!}
    </template>
    <span v-else>
      <span class="timer-mobile-label">{!! t('checkout.timer.valid_for') !!}</span> <template>@{{ timer_time }}</template>
    </span>
  </div>

</div>
