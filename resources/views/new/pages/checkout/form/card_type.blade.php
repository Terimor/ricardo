<template v-if="extra_fields.card_type">

  @include('new.components.select', [
    'name' => 'card_type',
    'model' => 'form.card_type',
    'validation' => '$v.form.card_type',
    'label' => t('checkout.payment_form.card_type.title'),
    'validation_labels' => [
      'required' => t('checkout.payment_form.card_type.required'),
    ],
    'items' => 'card_type_items',
    'placeholder' => true,
  ])

</template>
