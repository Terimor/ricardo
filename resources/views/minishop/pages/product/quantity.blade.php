<div class="quantity row align-items-center">
  <div class="col-6 col-md-6 col-lg-6">
    <div class="d-flex">
      <p class="mb-0 mx-auto font-weight-normal">{!! t('minishop.product.qty') !!}</p>
    </div>
  </div>
  <div class="col-6 col-md-6 col-lg-6">
    <div class="d-flex justify-content-center">
      <button
        class="plus-minus"
        @click="quantity_minus">-</button>
      <div class="value font-weight-normal">@{{ quantity }}</div>
      <button
        class="plus-minus"
        @click="quantity_plus">+</button>
    </div>
  </div>
</div>
