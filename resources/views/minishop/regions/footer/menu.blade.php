<ul class="flex-wrap justify-content-center">

  @if ($product->type != 'virtual')
    <li><a class="nav-link" href="/contact-us">{{ t('minishop.menu.contact_us') }}</a></li>
  @endif

  <li><a class="nav-link" href="/terms">{{ t('minishop.menu.terms') }}</a></li>

  <li><a class="nav-link" href="/privacy">{{ t('minishop.menu.privacy') }}</a></li>


  @if ($product->type != 'virtual')
    <li><a class="nav-link" href="/delivery">{{ t('minishop.menu.delivery') }}</a></li>
  @endif

  <li><a class="nav-link" href="/report-abuse">{{ t('abuse.title') }}</a></li>

</ul>
