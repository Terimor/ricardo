<div class="left-column">
  
  <div class="title">
    <i class="fa fa-user"></i>
    {!! t('vc2.customer.title') !!}
  </div>

  <div class="form">
    @include('new.pages.checkout.form.first_name')
    @include('new.pages.checkout.form.last_name')
    @include('new.pages.checkout.form.email')
    @include('new.pages.checkout.form.phone')
  </div>

  <div class="title billing">
    <i class="fa fa-tags"></i>
    {!! t('vc2.billing.title') !!}
  </div>

  @include('new.pages.checkout.templates.vc2.payment_provider')

  <div class="form">
    <template v-if="form.payment_provider === 'credit-card'">
      @include('new.pages.checkout.form.card_holder')
      @include('new.pages.checkout.form.card_type')
      @include('new.pages.checkout.payment.credit_cards_list')
      @include('new.pages.checkout.form.card_number')
      @include('new.pages.checkout.form.card_date')
      @include('new.pages.checkout.form.card_cvv')
      @include('new.pages.checkout.form.document_type')
      @include('new.pages.checkout.form.document_number')
    </template>
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
