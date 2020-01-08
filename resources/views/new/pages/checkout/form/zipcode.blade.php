<template v-if="form.country {{ !empty($br) ? '===' : '!==' }} 'br'">

  @include('new.components.input', [
    'name' => 'zipcode',
    'model' => 'form.zipcode',
    'validation' => '$v.form.zipcode',
    'label_code' => 'zipcode_label',
    'validation_labels' => [
      'required' => t('checkout.payment_form.zipcode.required'),
      'min_length' => t('checkout.payment_form.zipcode.required'),
    ],
    'blur' => 'zipcode_blur',
    'input' => 'zipcode_input',
    'placeholder' => true,
  ])

</template>

