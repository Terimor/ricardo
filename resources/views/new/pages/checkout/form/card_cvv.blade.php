@include('new.components.input', [
  'name' => 'card_cvv',
  'model' => 'form.card_cvv',
  'validation' => '$v.form.card_cvv',
  'label' => t('checkout.payment_form.card_cvv'),
  'placeholder' => t('checkout.payment_form.card_cvv'),
  'validation_labels' => [
    'required' => t('checkout.payment_form.card_cvv.required'),
    'numeric' => t('checkout.payment_form.card_cvv.required'),
    'min_length' => t('checkout.payment_form.card_cvv.required'),
    'max_length' => t('checkout.payment_form.card_cvv.required'),
  ],
  'suffix' => '<i class="fa fa-question-circle"></i>',
  'suffix_click' => 'card_cvv_suffix_open',
  'input' => 'card_cvv_input',
])

<div
  v-if="card_cvv_dialog_visible"
  class="dialog card_cvv-dialog"
  @click="card_cvv_dialog_close">

  <div
    class="inside"
    @click.stop>

    <div class="dialog-title">

      <i
        class="dialog-close fa fa-close"
        @click="card_cvv_dialog_close"></i>

      <div>{!! t('checkout.payment_form.cvv_popup.title') !!}</div>

    </div>

    <div class="dialog-content">
      <div>{!! t('checkout.payment_form.cvv_popup.line_1') !!}</div>
      <img
        class="lazy"
        data-src="{{ $cdn_url }}/assets/images/cvv_popup.jpg">
      <div>{!! t('checkout.payment_form.cvv_popup.line_2') !!}</div>
    </div>

  </div>

</div>
