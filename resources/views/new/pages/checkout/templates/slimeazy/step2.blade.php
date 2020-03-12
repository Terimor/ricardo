<div v-if="step === 2" class="step2" ref="step2">
  <div class="container">
    <div class="left-column">
      <div class="column-title">Choose Package {{ $product->product_name }}</div>
      @include('new.pages.checkout.templates.slimeazy.step2.deals')
      <div class="column-title">Choose Variant {{ $product->product_name }}</div>
      @include('new.pages.checkout.form.variant')
      @include('new.pages.checkout.form.warranty')
      @include('new.pages.checkout.templates.slimeazy.step2.total')
    </div>
    <div class="right-column">
      <div class="header">
        <div class="subtitle">Payment Information</div>
        <div class="title">Final Step!</div>
      </div>
      @include('new.pages.checkout.templates.slimeazy.step2.right')
    </div>
  </div>
</div>
