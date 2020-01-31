@php
  $countries_options = array_map(
    function($code) {
      return [
        'value' => $code,
        'label' => t('country.' . $code),
      ];
    },
    $countries,
  );
  usort(
    $countries_options,
    function($a, $b) {
      return strcasecmp($a['label'], $b['label']);
    },
  );
@endphp


@include('new.components.select', [
  'name' => 'country',
  'model' => 'form.country',
  'validation' => '$v.form.country',
  'label' => t('checkout.payment_form.сountry'),
  'validation_labels' => [
    'required' => t('checkout.payment_form.сountry.required'),
  ],
  'items' => $countries_options,
  'placeholder' => true,
])
