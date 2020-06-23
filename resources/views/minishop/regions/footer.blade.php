<footer
  ref="footer"
  class="footer">

  <div class="container">

    <!-- Menu -->
    <div class="row">
      <nav class="mx-auto">
        @include('minishop.regions.footer.menu')
      </nav>
    </div>

    <!-- Company -->
    @if ($is_aff_id_empty && !empty($company_address))
      <div class="row">
        <nav class="mx-auto">
          <div class="company-name py-2 px-4 text-center">{{ $company_address }}</div>
        </nav>
      </div>
    @endif

  </div>

</footer>
