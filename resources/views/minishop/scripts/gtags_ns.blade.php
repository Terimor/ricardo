@if (!empty($htmlToApp['gtags']))
  @foreach($htmlToApp['gtags'] as $gtag)

    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ !empty($gtag['code']) ? $gtag['code'] : '' }}" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>

  @endforeach
@endif
