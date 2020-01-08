<template v-if="extra_fields.document_type">

  @include('new.components.select', [
    'name' => 'document_type',
    'model' => 'form.document_type',
    'validation' => '$v.form.document_type',
    'label' => t('checkout.payment_form.document_type.title'),
    'validation_labels' => [
      'required' => t('checkout.payment_form.document_type.required'),
    ],
    'items' => 'document_type_items',
    'placeholder' => true,
  ])

</template>
