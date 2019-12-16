@if (!empty($html_to_app['gtags']))
  @foreach($html_to_app['gtags'] as $gtag)

    <noscript>
      <iframe
        src="https://www.googletagmanager.com/ns.html?id={{ !empty($gtag['code']) ? $gtag['code'] : '' }}"
        style="display:none;visibility:hidden"
        height="0"
        width="0"></iframe>
    </noscript>

  @endforeach
@endif
