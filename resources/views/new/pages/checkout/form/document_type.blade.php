<template v-if="extra_fields.document_type">

  @php
    $document_type_options = $setting['payment_methods']['mastercard']['extra_fields']['document_type']['items'] ?? null;

    if ($document_type_options) {
      $document_type_options = array_map(
        function($option) {
          return [
            'value' => $option['value'],
            'label' => t($option['phrase']),
          ];
        },
        $document_type_options,
      );
    }
  @endphp

  @include('new.components.select', [
    'name' => 'document_type',
    'model' => 'form.document_type',
    'validation' => '$v.form.document_type',
    'label' => t('checkout.payment_form.document_type.title'),
    'validation_labels' => [
      'required' => t('checkout.payment_form.document_type.required'),
    ],
    'items' => $document_type_options,
    'items_code' => 'document_type_items',
    'placeholder' => true,
  ])

</template>
