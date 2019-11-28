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
    @if ($is_aff_id_empty)
      <div class="row">
        <nav class="mx-auto">
          <div class="company-name py-2 px-4 text-center">
            MDE Commerce Ltd. - 29, Triq il-Kbira - Hal-Balzan - BZN 1259 - Malta
          </div>
        </nav>
      </div>
    @endif

  </div>

</footer>
