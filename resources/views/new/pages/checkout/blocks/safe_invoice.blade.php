<div class="safe-invoice">
  <div class="ssl">
    <i class="fa fa-lock"></i>
    <div>{!! t('checkout.safe_sll_encryption') !!}</div>
  </div>
  <div class="invoiced">{!! t('checkout.credit_card_invoiced') !!}</div>
  <div class="descriptor">"<div v-if="is_affid_empty">{!! $company_descriptor_prefix !!}</div>{!! $product->billing_descriptor !!}"</div>
</div>
