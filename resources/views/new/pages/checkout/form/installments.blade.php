<template v-if="installments_visible">

  @include('new.components.select', [
    'name' => 'installments',
    'model' => 'form.installments',
    'validation' => '$v.form.installments',
    'label' => t('checkout.payment_form.installments.title'),
    'items' => 'installments_items',
  ])

</template>
