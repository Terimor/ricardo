<div
  ref="products"
  class="products">

  <!-- Title -->
  <div class="title row">
    <div class="col-12 col-sm-12 col-md-12 col-lg-12">
      <h2 class="mb-5 mb-25-m font-weight-bold text-center">
        {{ t('minishop.home.products.title') }}
      </h2>
    </div>
  </div>

  <!-- Search -->
  @if ($is_catch_all)
    <div class="search row">
      <div class="col-12 col-md-8 col-lg-6 col-xl-4">
        <form class="mb-5 d-flex">
          <input
            v-model="search"
            class="form-control"
            placeholder="{{ t('minishop.home.products.search.input') }}" />
          <button
            type="submit"
            class="btn btn-primary text-nowrap ml-2 px-4"
            @click.prevent="search_click">{{ t('minishop.home.products.search.button') }}</button>
        </form>
      </div>
    </div>
  @endif

  <!-- Grid -->
  <div class="grid row">
    @foreach ($products as $product)
      <div class="col-12 col-sm-12 col-md-6 col-lg-4 col-xl-3">
        <a
          class="product-box d-block text-decoration-none text-reset"
          href="/product?product={{ $product->skus[0]['code'] ?? '' }}">

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

          <div class="price d-flex flex-column text-center font-weight-bold">
            <div class="old-price">{{ $product->prices[1]['old_value_text'] ?? '' }}</div>
            <div class="new-price">{{ $product->prices[1]['value_text'] ?? '' }}</div>
          </div>

        </a>
      </div>
    @endforeach
  </div>

  <!-- No Results -->
  @if (count($products) === 0)
    <div class="no-results">
      <p>{{ t('minishop.home.products.no_results') }}</p>
    </div>
  @endif

  <!-- Pager -->
  @if ($pagination && $pagination['total_pages'] > 1)
    <div class="pager row">
      <div class="col-12">
        <nav>
          <ul class="pagination">

            <li class="page-item{{ $pagination['page'] <= 1 ? ' disabled' : '' }}">
              <a class="page-link" href="/?p={{ $pagination['page'] - 1 }}">&laquo;</a>
            </li>

            @for ($index = 1; $index <= $pagination['total_pages']; $index++)
              <li class="page-item{{ $index == $pagination['page'] ? ' active' : '' }}">
                <a class="page-link" href="/?p={{ $index }}">{{ $index }}</a>
              </li>
            @endfor

            <li class="page-item{{ $pagination['page'] >= $pagination['total_pages'] ? ' disabled' : '' }}">
              <a class="page-link" href="/?p={{ $pagination['page'] + 1 }}">&raquo;</a>
            </li>

          </ul>
        </nav>
      </div>
    </div>
  @endif

</div>
