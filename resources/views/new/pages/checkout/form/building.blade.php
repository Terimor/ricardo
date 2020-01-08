<template v-if="extra_fields.building">

  @include('new.components.input', [
    'name' => 'building',
    'model' => 'form.building',
    'validation' => '$v.form.building',
    'label' => t('checkout.payment_form.building'),
    'validation_labels' => [
      'valid' => t('checkout.payment_form.building.required'),
    ],
    'placeholder' => true,
  ])

</template>
