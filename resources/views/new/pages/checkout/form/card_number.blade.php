@include('new.components.input', [
  'name' => 'card_number',
  'model' => 'form.card_number',
  'validation' => '$v.form.card_number',
  'label' => t('checkout.payment_form.card_number'),
  'validation_labels' => [
    'required' => t('checkout.payment_form.card_number.required'),
    'valid' => t('checkout.payment_form.card_number.required'),
  ],
  'prefix' => '<img :src="card_number_prefix_url || \'' . $cdn_url . '/assets/images/cc-icons/iconcc.png\'" />',
  'suffix' => '<i class="fa fa-lock"></i>',
  'input' => 'card_number_input',
  'placeholder' => true,
])
