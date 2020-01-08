@include('new.components.input', [
  'name' => 'card_date',
  'model' => 'form.card_date',
  'validation' => '$v.form.card_date',
  'label' => t('checkout.payment_form.card_date.label'),
  'validation_labels' => [
    'required' => t('checkout.payment_form.card_date.required'),
    'valid' => t('checkout.payment_form.card_date.required'),
    'not_expired' => t('checkout.payment_form.card_date.expired'),
  ],
  'input' => 'card_date_input',
  'mask' => 'card_date_mask',
  'monospace' => true,
])
