@include('new.components.input', [
  'name' => 'first_name',
  'model' => 'form.first_name',
  'validation' => '$v.form.first_name',
  'label' => t('checkout.payment_form.first_name'),
  'placeholder' => t('checkout.payment_form.first_name'),
  'validation_labels' => [
    'required' => t('checkout.payment_form.first_name.required'),
  ],
  'blur' => 'first_name_blur',
])
