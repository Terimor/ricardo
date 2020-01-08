<div
  v-if="timer_enabled"
  class="timer-desktop">

  <div class="inside">
    <div class="timer-label">{!! t('checkout.timer.valid_for') !!}</div>

    <div class="timer-time" dir="ltr">
      <div class="timer-minutes">
        <div><div class="timer-line"></div>@{{ timer_time[0] }}</div>
        <div><div class="timer-line"></div>@{{ timer_time[1] }}</div>
      </div>
      <div class="timer-dots">
        <div></div>
        <div></div>
      </div>
      <div class="timer-seconds">
        <div><div class="timer-line"></div>@{{ timer_time[3] }}</div>
        <div><div class="timer-line"></div>@{{ timer_time[4] }}</div>
      </div>
    </div>
  </div>

</div>
