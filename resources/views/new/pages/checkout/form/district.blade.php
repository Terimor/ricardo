<template v-if="extra_fields.district">

  @include('new.components.input', [
    'name' => 'district',
    'model' => 'form.district',
    'validation' => '$v.form.district',
    'label' => t('checkout.payment_form.complemento'),
    'validation_labels' => [
      'required' => t('checkout.payment_form.complemento.required'),
      'valid' => t('checkout.payment_form.complemento.required'),
    ],
    'loading' => 'is_loading.address',
    'placeholder' => true,
  ])

</template>
