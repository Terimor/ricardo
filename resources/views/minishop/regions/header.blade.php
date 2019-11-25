<nav class="navbar navbar-expand-lg navbar-light">

  <div class="container">

    <!-- Logo -->
    @include('minishop.regions.header.logo')

    <!-- Toggler -->
    @include('minishop.regions.header.toggler')

    <div
      class="collapse navbar-collapse"
      ref="navbar_collapsible">

      <!-- Menu -->
      @include('minishop.regions.header.menu')

      <ul class="navbar-nav ml-auto mt-2 mt-lg-0">

        <!-- Login -->
        @include('minishop.regions.header.login')

        <!-- Cart -->
        @include('minishop.regions.header.cart')

      </ul>

    </div>

  </div>

</nav>
