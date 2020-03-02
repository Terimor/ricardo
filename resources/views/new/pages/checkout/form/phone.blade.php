@include('new.components.input', [
  'name' => 'phone',
  'model' => 'form.phone',
  'validation' => '$v.form.phone',
  'label' => t('checkout.payment_form.phone'),
  'placeholder' => t('checkout.payment_form.phone'),
  'validation_labels' => [
    'required' => t('checkout.payment_form.phone.required'),
    'valid' => t('checkout.payment_form.phone.required'),
  ],
  'init' => 'phone_init',
  'input' => 'phone_input',
  'blur' => 'phone_blur',
])
