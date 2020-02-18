@include('new.components.input', [
  'name' => 'last_name',
  'model' => 'form.last_name',
  'validation' => '$v.form.last_name',
  'label' => t('checkout.payment_form.last_name'),
  'placeholder' => t('checkout.payment_form.last_name'),
  'validation_labels' => [
    'required' => t('checkout.payment_form.last_name.required'),
  ],
])
