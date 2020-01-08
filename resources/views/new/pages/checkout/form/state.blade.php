<template v-if="extra_fields.state && extra_fields.state.type === 'text'">

  @include('new.components.input', [
    'name' => 'state',
    'model' => 'form.state',
    'validation' => '$v.form.state',
    'label_code' => 'state_label',
    'validation_labels' => [
      'required' => t('checkout.payment_form.state.required'),
    ],
    'placeholder' => true,
  ])

</template>


<template v-if="extra_fields.state && extra_fields.state.type === 'dropdown'">

  @include('new.components.select', [
    'name' => 'state',
    'model' => 'form.state',
    'validation' => '$v.form.state',
    'label_code' => 'state_label',
    'validation_labels' => [
      'required' => t('checkout.payment_form.state.required'),
    ],
    'items' => 'state_items',
    'loading' => 'is_loading.address',
    'placeholder' => true,
  ])

</template>
