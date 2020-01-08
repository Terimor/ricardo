@if ((!Request::get('aff_id') || Request::get('aff_id') === '0') && (!Request::get('affid') || Request::get('affid') === '0'))

  <div
    ref="terms_field"
    class="terms-field scroll-when-error"
    :class="{ invalid: $v.form.terms.$dirty && $v.form.terms.$invalid }"
    @click="terms_change(!form.terms)"
    v-if="terms_init() || true">

    <input
      type="checkbox"
      class="terms-field-input"
      v-model="form.terms" />
    
    @include('new.components.check', [
      'active' => 'form.terms',
      'class' => 'terms-field-check',
    ])

    <div class="terms-field-label">
      {!! t('checkout.payment_form.terms', ['address' => '/terms', 'domain' => '/privacy']) !!}
    </div>

  </div>

@endif
