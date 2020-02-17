@if ((!Request::get('aff_id') || Request::get('aff_id') === '0') && (!Request::get('affid') || Request::get('affid') === '0'))

  @include('new.components.input', [
    'name' => 'card_holder',
    'model' => 'form.card_holder',
    'validation' => '$v.form.card_holder',
    'label' => t('checkout.payment_form.card_holder'),
    'placeholder' => t('checkout.payment_form.card_holder'),
    'validation_labels' => [
      'required' => t('checkout.payment_form.card_holder.required'),
    ],
  ])

@endif
