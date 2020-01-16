<template v-if="extra_fields.document_number">

  @include('new.components.input', [
    'name' => 'document_number',
    'model' => 'form.document_number',
    'validation' => '$v.form.document_number',
    'label' => t('checkout.payment_form.document_number.title'),
    'validation_labels' => [
      'required' => t('checkout.payment_form.document_number.required'),
      'valid' => t('checkout.payment_form.document_number.required'),
    ],
    'input' => 'document_number_input',
    'mask' => 'document_number_mask',
    'monospace' => true,
  ])

</template>
