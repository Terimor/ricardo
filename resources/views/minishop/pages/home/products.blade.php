<div
  ref="home_products"
  class="home-products">

  <!-- Title -->
  <div class="title row">
    <div class="col-12 col-sm-12 col-md-12 col-lg-12">
      <h2 class="mb-5 mb-25-m font-weight-bold text-center">
        {{ t('minishop.home.products.title') }}
      </h2>
    </div>
  </div>

  <!-- Grid -->
  <div class="grid row">
    @foreach ($products as $product)
      <div class="col-12 col-sm-12 col-md-6 col-lg-4 col-xl-3">
        <a
          class="product-box d-block text-decoration-none text-reset"
          href="/splash?product={{ $product->skus[0]['code'] ?? '' }}">

          <div class="images-holder d-flex position-relative align-items-center justify-content-center px-4">
            <img
              class="d-block img-1 mw-100 mh-100"
              src="{{ $product->image[0] ?? '' }}"
              alt="" />
          </div>

          <h3 class="text-center font-weight-bold text-truncate my-3">{{ $product->product_name ?? '' }}</h3>

          <div
            class="add-to-cart">
            <i class="fas fa-cart-plus"></i>{{ t('minishop.home.products.add_to_cart') }}
          </div>

          <p class="text-center font-weight-bold">
            <span class="old-price">{{ $product->prices[1]['old_value_text'] ?? '' }}</span>{{ $product->prices[1]['value_text'] ?? '' }}
          </p>

        </a>
      </div>
    @endforeach
  </div>

  <!-- Pager -->
  @if ($pagination && $pagination['total_pages'] > 1)
    <div class="pager row">
      <div class="col-12">
        <nav>
          <ul class="pagination">

            <li class="page-item{{ $pagination['page'] <= 1 ? ' disabled' : '' }}">
              <a class="page-link" href="/?page={{ $pagination['page'] - 1 }}">&laquo;</a>
            </li>

            @for ($index = 1; $index <= $pagination['total_pages']; $index++)
              <li class="page-item{{ $index === $pagination['page'] ? ' active' : '' }}">
                <a class="page-link" href="/?page={{ $index }}">{{ $index }}</a>
              </li>
            @endfor

            <li class="page-item{{ $pagination['page'] >= $pagination['total_pages'] ? ' disabled' : '' }}">
              <a class="page-link" href="/?page={{ $pagination['page'] + 1 }}">&raquo;</a>
            </li>

          </ul>
        </nav>
      </div>
    </div>
  @endif

</div>
