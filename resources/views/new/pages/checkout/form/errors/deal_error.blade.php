@include('new.components.error', [
  'ref' => 'deal_error',
  'active' => '$v.form.deal.$dirty && $v.form.deal.$invalid',
  'class' => 'deal-error scroll-when-error invalid',
  'label' => t('checkout.main_deal.error'),
])
