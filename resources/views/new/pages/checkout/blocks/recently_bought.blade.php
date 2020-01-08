<transition name="recently_bought_transition" appear>

  <div
    v-if="recently_bought_active"
    class="recently-bought">

    <div
      v-if="recently_bought_active === 'just_bought'"
      class="recently-notice">
      <div class="recently-notice__left">
        <img src="{{ $product->image[0] }}" alt="">
      </div>
      <div class="recently-notice__right">
        <p v-html="recently_bought_just_bought"></p>
      </div>
    </div>

    <div
      v-if="recently_bought_active === 'user_active'"
      class="recently-notice recently-notice_user-active">
      <div class="recently-notice__left">
        <i class="fa fa-user"></i>
      </div>
      <div class="recently-notice__right">
        <p v-html="recently_bought_user_active"></p>
      </div>
    </div>

    <div
      v-if="recently_bought_active === 'paypal'"
      class="recently-notice recently-notice_paypal"
      @click="recently_bought_paypal_click">
      <div class="recently-notice__left">
        <img src="{{ $cdn_url }}/assets/images/paypal232.png" alt="PayPal">
      </div>
      <div class="recently-notice__right">
        <p>{{ t('checkout.paypal.risk_free') }}</p>
        <img src="{{ $cdn_url }}/assets/images/paypal-highq.png" alt="PayPal">
      </div>
    </div>

    <div
      v-if="recently_bought_active === 'bestseller'"
      class="recently-notice recently-notice_high-demand">
      <div class="recently-notice__left">
        <i class="fa fa-user"></i>
      </div>
      <div class="recently-notice__right">
        <p>{!! t('checkout.notification.bestseller') !!}</p>
      </div>
    </div>

  </div>

</transition>
