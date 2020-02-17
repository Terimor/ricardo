<template v-if="extra_fields.state && extra_fields.state.type === 'text'">

  @include('new.components.input', [
    'name' => 'state',
    'model' => 'form.state',
    'validation' => '$v.form.state',
    'label_code' => 'state_label',
    'placeholder_code' => 'state_label',
    'validation_labels' => [
      'required' => t('checkout.payment_form.state.required'),
    ],
  ])

</template>


<template v-if="extra_fields.state && extra_fields.state.type === 'dropdown'">

  @php
    $states_options = $setting['payment_methods']['mastercard']['extra_fields']['state']['items'] ?? null;
  @endphp

  @include('new.components.select', [
    'name' => 'state',
    'model' => 'form.state',
    'validation' => '$v.form.state',
    'label_code' => 'state_label',
    'placeholder' => t('checkout.payment_form.state.selected'),
    'validation_labels' => [
      'required' => t('checkout.payment_form.state.required'),
    ],
    'items' => $states_options,
    'items_code' => 'state_items',
    'loading' => 'is_loading.address',
  ])

</template>
