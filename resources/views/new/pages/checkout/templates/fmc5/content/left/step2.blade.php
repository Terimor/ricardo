<transition name="fade">
  <div
    v-if="step === 2"
    class="step2">

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
    </div>

  </div>
</transition>
