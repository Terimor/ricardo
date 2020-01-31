<template v-if="installments_visible">

  @php
    $installments_options = $setting['payment_methods']['mastercard']['extra_fields']['installments']['items'] ?? null;

    if ($installments_options) {
      $installments_options = array_map(
        function($option) {
          return [
            'value' => $option['value'],
            'label' => t($option['phrase']),
          ];
        },
        $installments_options,
      );
    }
  @endphp

  @include('new.components.select', [
    'name' => 'installments',
    'model' => 'form.installments',
    'validation' => '$v.form.installments',
    'label' => t('checkout.payment_form.installments.title'),
    'items' => $installments_options,
    'items_code' => 'installments_items',
  ])

</template>
