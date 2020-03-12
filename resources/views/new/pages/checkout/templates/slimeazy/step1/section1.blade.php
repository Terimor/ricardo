<div class="section1 section-back">
  <div class="container">
    <div class="left">
      <div class="subrow">
        <div class="subleft">
          <img class="image lazy" data-src="{{ $cdn_url }}/assets/images/checkout/slimeazy/heading-img.png" />
        </div>
        <div class="subright">
          <div class="title">Lose Weight</div>
          <div class="subtitle">Without Exercies, Exercies or Diet!</div>
          <div class="options">
            <div class="option">
              <img class="lazy" data-src="{{ $cdn_url }}/assets/images/checkout/slimeazy/aero.png" />
              <div>Help Prevent Fat Buld Up</div>
            </div>
            <div class="option">
              <img class="lazy" data-src="{{ $cdn_url }}/assets/images/checkout/slimeazy/aero.png" />
              <div>Control your Cravings</div>
            </div>
            <div class="option">
              <img class="lazy" data-src="{{ $cdn_url }}/assets/images/checkout/slimeazy/aero.png" />
              <div>Decrease Your Belly Fat</div>
            </div>
          </div>
          <div class="line1">Testosterone Support, Natural Ingredients</div>
          <div class="line2">Long Lasting Female Diet!</div>
        </div>
      </div>
      <div class="shipping">
        <img class="image lazy" data-src="{{ $cdn_url }}/assets/images/checkout/slimeazy/mobil-heding-img3.png" />
        <div class="label">Free Shipping Today!</div>
      </div>
    </div>
    <div class="right">
      <div class="title">Limited Time Offer ACT NOW!</div>
      <div class="card">
        <div class="head">
          <div class="title">Where Do We Send</div>
          <div class="subtitle">Your Order?</div>
        </div>
        <div class="subhead">Enter Your Shipping Address Below</div>
        <div class="form">
          @include('new.pages.checkout.form.first_name')
          @include('new.pages.checkout.form.last_name')
          @include('new.pages.checkout.form.email')
          @include('new.pages.checkout.form.phone')
          @include('new.pages.checkout.form.zipcode', ['br' => true])
          @include('new.pages.checkout.form.street')
          @include('new.pages.checkout.form.building')
          @include('new.pages.checkout.form.complement')
          @include('new.pages.checkout.form.district')
          @include('new.pages.checkout.form.city')
          @include('new.pages.checkout.form.state')
          @include('new.pages.checkout.form.zipcode')
          @include('new.pages.checkout.form.country')
          <div class="button" @click="step1_submit">Rush My Order</div>
        </div>
      </div>
    </div>
  </div>
  <div class="mobile">
    <div class="line1">Testosterone Support, Natural Ingredients</div>
    <div class="line2">Long Lasting Female Diet!</div>
    <div class="shipping">
      <img class="image lazy" data-src="{{ $cdn_url }}/assets/images/checkout/slimeazy/mobil-heding-img3.png" />
      <div class="label">Free Shipping Today!</div>
    </div>
  </div>
</div>
