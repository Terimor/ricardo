@include('new.components.input', [
  'name' => 'city',
  'model' => 'form.city',
  'validation' => '$v.form.city',
  'label' => t('checkout.payment_form.city'),
  'placeholder' => t('checkout.payment_form.city'),
  'validation_labels' => [
    'required' => t('checkout.payment_form.city.required'),
  ],
  'loading' => 'is_loading.address',
])
