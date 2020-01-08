@yield('footer_before')


<footer class="footer-region">

  <ul class="footer__row">
    @if (isset($hasHome) && $hasHome == true)
      <li class="footer__row-item"><a href="/" class="footer__link">{!! t('footer.home') !!}</a></li>
    @endif

    <li class="footer__row-item"><a href="/contact-us" class="footer__link">{!! t('footer.contact') !!}</a></li>
    <li class="footer__row-item"><a href="/terms" class="footer__link">{!! t('footer.terms') !!}</a></li>
    <li class="footer__row-item"><a href="/privacy" class="footer__link">{!! t('footer.privacy') !!}</a></li>

    @if (empty($aff['is_signup_hidden']))
      <li class="footer__row-item"><a href="https://www.h8m8.com" target="_blank" class="footer__link">{!! t('footer.affiliate') !!}</a></li>
    @endif

    <li class="footer__row-item"><a href="/delivery" class="footer__link">{!! t('footer.delivery') !!}</a></li>
  </ul>

  @if ($is_aff_id_empty)
    <div class="footer-company-address">{!! $company_address !!}</div>
  @endif

</footer>


@yield('footer_after')
