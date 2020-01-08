<div
  v-if="timer_enabled"
  class="timer-mobile">

  <div class="inside">
    <div v-if="timer_time === '00:00'">
      {!! t('checkout.timer.time_over') !!}
    </div>
    <template v-else>
      <div class="timer-mobile-label">{!! t('checkout.timer.valid_for') !!}</div> <div>@{{ timer_time }}</div>
    </template>
  </div>

</div>
