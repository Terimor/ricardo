@include('new.components.select', [
  'name' => 'country',
  'model' => 'form.country',
  'validation' => '$v.form.country',
  'label' => t('checkout.payment_form.сountry'),
  'validation_labels' => [
    'required' => t('checkout.payment_form.сountry.required'),
  ],
  'items' => 'country_items',
  'placeholder' => true,
])
