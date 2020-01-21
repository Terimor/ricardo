<div class="summary">

  <div class="title">{!! t('vc1.summary.title') !!}</div>

  @include('new.pages.checkout.form.installments')

  <div class="table">
    <div class="inside">
      <div class="row-header">
        <div class="col col-item">{!! t('vc1.summary.table.item') !!}</div>
        <div class="col col-quantity">{!! t('vc1.summary.table.quantity') !!}</div>
        <div class="col col-price">{!! t('vc1.summary.table.price') !!}</div>
      </div>
      <div class="row row-product">
        <div class="col col-item">{{ $product->long_name }}</div>
        <div class="col col-quantity">@{{ form.deal }}</div>
        <div class="col col-price">@{{ summary_price_text }}</div>
      </div>
      <div
        v-if="form.warranty"
        class="row row-warranty">
        <div class="col col-item">{!! t('vc1.summary.table.warranty') !!}</div>
        <div class="col col-quantity">@{{ form.deal }}</div>
        <div class="col col-price">@{{ warranty_price_text }}</div>
      </div>
      <div class="total">
        <div class="label">{!! t('vc1.summary.table.total') !!}</div>
        <div class="value">@{{ summary_total }}</div>
      </div>
    </div>
  </div>

  @include('new.pages.checkout.form.warranty')
  @include('new.pages.checkout.form.terms')

  @include('new.components.error', [
    'ref' => 'payment_error',
    'active' => 'payment_error && form.payment_provider === \'credit-card\'',
    'class' => 'payment-error',
    'label_code' => 'payment_error',
  ])

  <div
    class="submit"
    :class="{ submitted: is_submitted }"
    @click="credit_card_create_order">

    <div
      v-if="is_submitted"
      class="disabled"
      @click.stop>
      @include('new.components.spinner')
    </div>

    <img
      alt=""
      src="{{ $cdn_url }}/assets/images/checkout/vc1/buy.gif" />

  </div>

  <img
    alt=""
    class="secure"
    src="{{ $cdn_url }}/assets/images/checkout/vc1/secure2.png" />

</div>
