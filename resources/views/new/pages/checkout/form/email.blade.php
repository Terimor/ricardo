@include('new.components.input', [
  'name' => 'email',
  'model' => 'form.email',
  'validation' => '$v.form.email',
  'label' => t('checkout.payment_form.email'),
  'placeholder' => t('checkout.payment_form.email'),
  'validation_labels' => [
    'required' => t('checkout.payment_form.email.required'),
    'email' => t('checkout.payment_form.email.invalid'),
    'valid' => t('checkout.payment_form.email.invalid'),
  ],
  'extra_validation_labels' => [
    'suggest' => t('checkout.payment_form.email.suggestion', ['email' => '<a href="#" class="suggestion"></a>']),
    'warning' => t('checkout.payment_form.email.warning'),
    'disposable' => t('checkout.payment_form.email.disposable'),
  ],
  'blur' => 'email_blur',
  'input' => 'email_input',
])
