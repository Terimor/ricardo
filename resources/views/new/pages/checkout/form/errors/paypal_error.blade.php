@include('new.components.error', [
  'ref' => 'paypal_payment_error',
  'active' => 'payment_error && form.payment_provider === \'paypal\'',
  'class' => 'paypal-payment-error',
  'label_code' => 'payment_error',
])
