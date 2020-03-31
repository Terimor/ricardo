<div class="users-online">
  <img class="magnifier lazy" data-src="{{ $cdn_url }}/assets/images/checkout/amc81/magnifier.png" />
  <div class="text">
    <transition name="fade">
      <div class="value" :key="users_online">@{{ users_online }}</div>
    </transition>
    <div>&nbsp;{{ t('checkout.users_online') }}</div>
  </div>
</div>
