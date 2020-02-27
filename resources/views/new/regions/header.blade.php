@yield('header_before')


<header class="header-region">
  <div class="container">
      
    <a
      href="/checkout"
      class="header-logo-link">
        <img
          data-src="{{ $main_logo ?? $product->logo_image }}"
          class="header-logo lazy"
          alt=""
        />
    </a>

    @yield('header')

  </div>
</header>


@yield('header_after')
