<div class="price-cart row align-items-center mt-5 mt-25">
  <div class="col-12 col-sm-12 col-md-6 col-lg-6 mb-15">
    <div class="d-flex">
      <p class="value mb-0 mx-auto">@{{ total_price }}</p>
    </div>
  </div>
  <div class="col-12 col-sm-12 col-md-6 col-lg-6">
    <div class="d-flex">
      <a
        class="button btn btn-default"
        @click.prevent="goto_checkout"
        href="#">
        <i class="fas fa-cart-plus"></i>{!! t('minishop.product.add_to_cart') !!}
      </a>
    </div>
  </div>
</div>
