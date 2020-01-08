<template v-if="extra_fields.complement">

  @include('new.components.input', [
    'name' => 'complement',
    'model' => 'form.complement',
    'validation' => '$v.form.complement',
    'label' => t('checkout.payment_form.complement'),
    'validation_labels' => [
      'valid' => t('checkout.payment_form.complement.required'),
    ],
    'loading' => 'is_loading.address',
    'placeholder' => true,
  ])

</template>
