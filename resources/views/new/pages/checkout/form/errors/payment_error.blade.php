@include('new.components.error', [
  'ref' => 'payment_error',
  'active' => 'payment_error && form.payment_provider !== \'paypal\'',
  'class' => 'payment-error',
  'label_code' => 'payment_error',
])
