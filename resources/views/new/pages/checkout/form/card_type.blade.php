<template v-if="extra_fields.card_type">

  @php
    $card_type_options = $setting['payment_methods']['mastercard']['extra_fields']['card_type']['items'] ?? null;

    if ($card_type_options) {
      $card_type_options = array_map(
        function($option) {
          return [
            'value' => $option['value'],
            'label' => t($option['phrase']),
          ];
        },
        $card_type_options,
      );
    }
  @endphp

  @include('new.components.select', [
    'name' => 'card_type',
    'model' => 'form.card_type',
    'validation' => '$v.form.card_type',
    'label' => t('checkout.payment_form.card_type.title'),
    'placeholder' => t('checkout.payment_form.card_type.title'),
    'validation_labels' => [
      'required' => t('checkout.payment_form.card_type.required'),
    ],
    'items' => $card_type_options,
    'items_code' => 'card_type_items',
  ])

</template>
