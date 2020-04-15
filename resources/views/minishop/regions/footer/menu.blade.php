<ul class="flex-wrap justify-content-center">

  <li><a class="nav-link" href="/contact-us">{{ t('minishop.menu.contact_us') }}</a></li>

  <li><a class="nav-link" href="/terms">{{ t('minishop.menu.terms') }}</a></li>

  <li><a class="nav-link" href="/privacy">{{ t('minishop.menu.privacy') }}</a></li>

  @if (!$is_signup_hidden)
    <li><a class="nav-link" href="https://www.h8m8.com" target="_blank">{{ t('minishop.menu.affiliate') }}</a></li>
  @endif

  <li><a class="nav-link" href="/delivery">{{ t('minishop.menu.delivery') }}</a></li>
  <li><a class="nav-link" href="/report-abuse">{{ t('abuse.title') }}</a></li>

</ul>
