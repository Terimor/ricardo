<div
  ref="home_products"
  class="home-products">

  <div class="title row">
    <div class="col-12 col-sm-12 col-md-12 col-lg-12">
      <h2 class="mb-5 mb-25-m font-weight-bold text-center">
        {{ t('minishop.home.products.title') }}
      </h2>
    </div>
  </div>

  <div class="grid row">
    @foreach ($products as $product)
      <div class="col-12 col-sm-12 col-md-6 col-lg-4 col-xl-3">
        <div
          class="product-box"
          @click="home_products_goto_checkout('{{ $product->skus[0]['code'] ?? '' }}')">

          <div class="images-holder d-flex position-relative align-items-center justify-content-center px-4">
            <img
              class="d-block img-1 mw-100 mh-100"
              src="{{ $product->image[0] ?? '' }}"
              alt="" />
          </div>

          <h3 class="text-center font-weight-bold my-3">{{ $product->product_name ?? '' }}</h3>

          <div
            class="add-to-cart">
            <i class="fas fa-cart-plus"></i>{{ t('minishop.home.products.add_to_cart') }}
          </div>

          <p class="text-center font-weight-bold">
            <span class="old-price">{{ $product->prices[1]['old_value_text'] ?? '' }}</span>{{ $product->prices[1]['value_text'] ?? '' }}
          </p>

        </div>
      </div>
    @endforeach
  </div>

</div>
